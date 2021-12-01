<?php

namespace Drupal\mymodule\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;


class MyModuleForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'mymodule_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $conn = Database::getConnection();
    $data = array();
    if (isset($_GET['id'])) {
      $query = $conn->select('mytable', 'm')
        ->condition('id', $_GET['id'])
        ->fields('m');
      $data = $query->execute()->fetchAssoc();
    }
    // echo "<pre>";
    // print_r($data);
    // die();

    $form['first_name'] = [
      '#id' => 'one',
      '#type' => 'textfield',
      '#title' => $this->t('first name'),
      '#required' => true,
      '#size' => 60,
      '#default_value' => (isset($data['first_name'])) ? $data['first_name'] : '',
      '#maxlength' => 128,
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    $form['last_name'] = [
      '#id' => 'two',
      '#type' => 'textfield',
      '#title' => $this->t('last name'),
      '#required' => true,
      '#size' => 60,
      '#default_value' => (isset($data['last_name'])) ? $data['last_name'] : '',
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('email'),
      '#required' => true,
      '#default_value' => (isset($data['email'])) ? $data['email'] : '',
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    $form['picture'] = array(
      '#title' => t('picture'),
      '#description' => $this->t('Chossir Image gif png jpg jpeg'),
      '#type' => 'managed_file',
      '#required' => true,
      '#default_value' => (isset($data['fid'])) ? [$data['fid']] : [],
      '#upload_location' => 'public://images/',
      '#upload_validators' => array(
        'file_validate_extensions' => array('gif png jpg jpeg')),
    );
    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('phone'),
      '#required' => true,
      '#default_value' => (isset($data['phone'])) ? $data['phone'] : '',
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    $form['select'] = [
      '#type' => 'select',
      '#title' => $this
        ->t('Select element'),
      '#options' => [
        '1' => $this
          ->t('One'),
        '2' => [
          '2.1' => $this
            ->t('Two point one'),
          '2.2' => $this
            ->t('Two point two'),
        ],
        '3' => $this
          ->t('Three'),
      ],
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12'],
      '#default_value' => (isset($data['select'])) ? $data['select'] : '',
    ];
    $form['message'] = [
      // '#type' => 'textarea',
      '#type' => 'color',
      '#title' => $this->t('message'),
      '#required' => true,
      '#default_value' => (isset($data['message'])) ? $data['message'] : '',
      '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12']
    ];
    // $form['captcha'] = [
    // '#type' => 'captcha',
    // '#captcha_type' => 'image_captcha/Image',
    // '#required' => true,
    // '#default_value' => (isset($data['captcha'])) ? $data['captcha'] : '',
    // '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12'],
    // ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('save'),
      '#buttom_type' => 'primary'
    ];
    
    $form['#attached']['library'][] = 'mymodule/lib';
    return $form;
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    if (is_numeric($form_state->getValue('first_name'))) {
      $form_state->setErrorByName('first_name', $this->t('Error, The First Name Must Be A String'));
    }
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $picture = $form_state->getValue('picture');
    $data = array(
      'first_name' => $form_state->getValue('first_name'),
      'last_name' => $form_state->getValue('last_name'),
      'email' => $form_state->getValue('email'),
      'phone' => $form_state->getValue('phone'),
      'select' => $form_state->getValue('select'),
      'message' => $form_state->getValue('message'),
      // 'capacha' => $form_state->getValue('captcha'),
      'fid' => $picture[0],
    );
    // echo "<pre>";
    // print_r($data);
    // die(); 

    // save file as Permanent
    $file = File::load($picture[0]);
    $file->setPermanent();
    $file->save();

    if (isset($_GET['id'])) {
      // update data in database
      \Drupal::database()->update('mytable')->fields($data)->condition('id', $_GET['id'])->execute();
    } else {
      // insert data to database
      \Drupal::database()->insert('mytable')->fields($data)->execute();
    }

    // show message and redirect to list page
    \Drupal::messenger()->addStatus('Succesfully saved');
    $url = new Url('mymodule.display_data');
    $response = new RedirectResponse($url->toString());
    $response->send();

  }
}
