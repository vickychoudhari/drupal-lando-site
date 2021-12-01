<?php

namespace Drupal\dn_students\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Routing;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormState;
use Drupal\Core\Link;

/**
 * Provides the form for adding countries.
 */
class StudentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dn_student_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$record = NULL) {
   
    $form['fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#maxlength' => 20,
	  '#attributes' => array(
       'class' => ['txt-class'],
       ),
      '#default_value' =>'',
	  '#prefix' => '<div id="div-fname">',
      '#suffix' => '</div><div id="div-fname-message"></div>',
    ];
	 $form['sname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Second Name'),
      '#maxlength' => 20,
	  '#attributes' => array(
       'class' => ['txt-class'],
       ),
      '#default_value' => '',
    ];
	$form['age'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Age'),
      '#maxlength' => 20,
	  '#attributes' => array(
       'class' => ['txt-class'],
       ),
      '#default_value' =>  '',
    ];
	 $form['marks'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Marks'),
      '#maxlength' => 20,
	  '#attributes' => array(
       'class' => ['txt-class'],
       ),
      '#default_value' => '',
    ];
	
	
    $form['actions']['#type'] = 'actions';
    $form['actions']['Save'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
	  '#ajax' => ['callback' => '::saveDataAjaxCallback'] ,
      '#value' => $this->t('Save') ,
    ];
	 $form['actions']['clear'] = [
      '#type' => 'submit',
      '#ajax' => ['callback' => '::clearForm','wrapper' => 'form-div',] ,
      '#value' => 'Clear',
     ];
	 $render_array['#attached']['library'][] = 'dn_students/global_styles';
    return $form;

  }
  
   /**
   * {@inheritdoc}
   */
  public function validateForm(array & $form, FormStateInterface $form_state) {
        //print_r($form_state->getValues());exit;
		
  }

 
   public function clearForm(array &$form, FormStateInterface $form_state) {
	   
	   //try {
	  $response = new AjaxResponse();
    print_r($response);
     die();
	   $response->addCommand(new InvokeCommand('.txt-class', 'val', ['']));

	   //$response->addCommand(new InvokeCommand('#edit-fname', 'style', ['']));
	   $response->addCommand(new InvokeCommand('#edit-fname','removeAttr',['style']));
	   $response->addCommand(new HtmlCommand('#div-fname-message', ''));
	   $response->addCommand(new InvokeCommand('.txt-class', 'val', ['']));
	  
	   return $response;
	   
   }
   
  
   
   /**
    * Our custom Ajax responce.
    */
   public function saveDataAjaxCallback(array &$form, FormStateInterface $form_state) {
    
	 $conn = Database::getConnection();

    $field = $form_state->getValues();
	
    $re_url = Url::fromRoute('dn_students.student');
   
	$fields["fname"] = $field['fname'];
	$fields["sname"] = $field['sname'];
	$fields["age"] = $field['age'];
	$fields["marks"] = $field['marks'];
	$response = new AjaxResponse();
	//========Field value validation
	if($fields["fname"] == ''){
		$css = ['border' => '1px solid red'];
		$text_css = ['color' => 'red'];
        $message = ('First Name not valid.');
	
		//$response = new \Drupal\Core\Ajax\AjaxResponse();
		$response->addCommand(new \Drupal\Core\Ajax\CssCommand('#edit-fname', $css));
		$response->addCommand(new \Drupal\Core\Ajax\CssCommand('#div-fname-message', $text_css));
		$response->addCommand(new \Drupal\Core\Ajax\HtmlCommand('#div-fname-message', $message));
		return $response;
	}else{
    
      
      $conn->insert('students')
           ->fields($fields)->execute();
     
	$dialogText['#attached']['library'][] = 'core/drupal.dialog.ajax';
	
	$render_array = \Drupal::formBuilder()->getForm('Drupal\dn_students\Form\StudentTableForm','All');
	//$render_array['#attached']['library'][] = 'dn_students/global_styles';
	 $response->addCommand(new HtmlCommand('.result_message','' ));
	 $response->addCommand(new \Drupal\Core\Ajax\AppendCommand('.result_message', $render_array));
	 $response->addCommand(new HtmlCommand('.pagination','' ));
	 $response->addCommand(new \Drupal\Core\Ajax\AppendCommand('.pagination', getPager()));
	  $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.pagination-link', 'removeClass', array('active')));
	   $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('.pagination-link:first', 'addClass', array('active')));
	 $response->addCommand(new InvokeCommand('.txt-class', 'val', ['']));
	 
	 
     return $response;
   
  }
   
  }

  /**
   * {@inheritdoc}
   */
 public function submitForm(array & $form, FormStateInterface $form_state) {
	  
  }

}
  
