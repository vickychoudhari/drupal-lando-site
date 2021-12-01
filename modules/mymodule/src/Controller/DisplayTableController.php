<?php

namespace Drupal\mymodule\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class DisplayTableController
 * @package Drupal\mymodule\Controller
 */
class DisplayTableController extends ControllerBase
{

  public function index()
  {
    //create table header
    $header_table = array(
      'id' => t('ID'),
      'first_name' => t('first name'),
      'last_name' => t('last name'),
      'email' => t('Email'),
      'phone' => t('phone'),
      'view' => t('View'),
      'delete' => t('Delete'),
      'edit' => t('Edit'),
    );


    // get data from database
    $query = \Drupal::database()->select('mytable', 'm');
    $query->fields('m', ['id', 'first_name', 'last_name', 'email', 'phone']);
    $results = $query->execute()->fetchAll();
    $rows = array();
    foreach ($results as $data) {
      $url_delete = Url::fromRoute('mymodule.delete_form', ['id' => $data->id], []);
      $url_edit = Url::fromRoute('mymodule.add_form', ['id' => $data->id], []);
      $url_view = Url::fromRoute('mymodule.show_data', ['id' => $data->id], []);
      $linkDelete = Link::fromTextAndUrl('Delete', $url_delete);
      $linkEdit = Link::fromTextAndUrl('Edit', $url_edit);
      $linkView = Link::fromTextAndUrl('View', $url_view);

      //get data
      $rows[] = array(
        'id' => $data->id,
        'first_name' => $data->first_name,
        'last_name' => $data->last_name,
        'email' => $data->email,
        'phone' => $data->phone,
        'view' => $linkView,
        'delete' => $linkDelete,
        'edit' =>  $linkEdit,
      );

    }
    // render table
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => t('No data found'),
    ];
    return $form;

  }

}