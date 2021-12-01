<?php
/**
 * @file
 * Contains \Drupal\move\Form\MoveForm.
 */
namespace Drupal\move\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Link;

class MoveForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'move_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    }
}