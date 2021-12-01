<?php
namespace Drupal\dn_students\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Routing;
use Drupal\Core\Link;

/**
 * Provides the list of Students.
 */
class StudentTableForm extends FormBase {
	
	 /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dn_student_table_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$pageNo = NULL) {
    
   
   //$pageNo = 2;
    $header = [
      'id' => $this->t('Id'),
      'fname' => $this->t('First Name'),
	  'sname' => $this->t('Second Name'),
	  'age'=> $this->t('age'),
	  'Marks'=> $this->t('Marks'),	  
	  'opt' =>$this->t('Operations'),
    'edit' =>$this->t('Update')
    ];

    
	
   if($pageNo != ''){
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $this->get_students($pageNo),
      '#empty' => $this->t('No users found'),
    ];
   }else{
	    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $this->get_students("All"),
      '#empty' => $this->t('No records found'),
    ];
   }
   //$form['form2'] = $this->get_students("All");
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
	//$form['#attached']['library'][] = 'core/drupal.ajax';
	$form['#attached']['library'][] = 'dn_students/global_styles';
	
     $form['#theme'] = 'student_form';
	  $form['#prefix'] = '<div class="result_message">';
	   $form['#suffix'] = '</div>';
	  // $form_state['#no_cache'] = TRUE;
	   $form['#cache'] = [
      'max-age' => 0
    ];
    return $form;

  }

  
  

  public function validateForm(array &$form, FormStateInterface $form_state) {
     
	 //$field = $form_state->getValues();
	
	 
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array & $form, FormStateInterface $form_state) {	  
	   
  }
  
  function get_students($opt) {
	$res = array();
	//$opt = 2;
 if($opt == "All"){

  $results = \Drupal::database()->select('students', 'st');
 
  $results->fields('st');
  $results->range(0, 15);
  $results->orderBy('st.id','DESC');
  $res = $results->execute()->fetchAll();
  $ret = [];
 }else{
	 $query = \Drupal::database()->select('students', 'st');
  
  $query->fields('st');
  $query->range($opt*15, 15);
  $query->orderBy('st.id','DESC');
  $res = $query->execute()->fetchAll();
  $ret = [];
 }
    foreach ($res as $row) {

      
	  $edit = Url::fromUserInput('/ajax/dn_students/students/edit/' . $row->id);
	  //array('attributes' => array('onclick' => "return confirm('Are you Sure')"))
	  $delete = Url::fromUserInput('/del/dn_students/students/delete/' . $row->id,array('attributes' => array('onclick' => "return confirm('Are you Sure')")));
      
	  $edit_link = Link::fromTextAndUrl(t('Edit'), $edit);
	  $delete_link = Link::fromTextAndUrl(t('Delete'), $delete);
	  $edit_link = $edit_link->toRenderable();
      $delete_link  = $delete_link->toRenderable();
	  $edit_link['#attributes'] = ['class'=>'use-ajax'];
	  $delete_link['#attributes'] = ['class'=>'use-ajax'];
	 
       
      $mainLink = t('@linkApprove  @linkReject', array('@linkApprove' => $edit_link, '@linkReject' => $delete_link));
      
	  
      $ret[] = [
        'id' => $row->id,
        'fname' => $row->fname,
		'sname' => $row->sname,
		'age' => $row->age,
		'marks' => $row->marks,
        'opt' => render($delete_link),
		'opt1' => render($edit_link),
      ];
    }
    return $ret;
}
	
}