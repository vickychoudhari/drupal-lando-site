<?php  
/**  
 * @file  
 * Contains Drupal\rsvp\Form\RSVPForm.  
 */  
namespace Drupal\rsvp\Form;  
use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;  

class RSVPForm extends ConfigFormBase {  

   protected function getEditableConfigNames() {  
    return [  
      'rsvp.settings',  
    ];  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function getFormId() {  
    return 'rsvp_form';  
  }  
 
 /**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $types  = node_type_get_names();
    // echo "<pre>";
    // print_r($types);
    // die();
    $config = $this->config('rsvp.settings');  
   $form['rsvplist_types'] = array(
         '#type' => 'checkboxes',
         '#title' => $this->t('The Content Types that enable RSVP Collection for'),
         '#default_value' => $config->get('allowed_types'),
         '#options' => $types);
    $form['array_filter'] = array( '#type' => 'value', '#value' => TRUE);
    // echo "<pre>";
    // print_r($form);
    // die();

  

     return parent::buildForm($form, $form_state);  
  }
   /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
   $allowed_types = array_filter($form_state->getValue('rsvplist_types'));
    sort($allowed_types);

    $this->config('rsvp.settings')
        ->set('allowed_types', $allowed_types)
        ->save();
        return parent::submitForm($form, $form_state); 
  }
  }  
  