<?php

namespace Drupal\mymodule\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\file\Entity\File;

/**
 * Class MydataController
 * @package Drupal\mymodule\Controller
 */
class MydataController extends ControllerBase
{

  /**
   * @return array
   */
  public function show($id)
  {

    $conn = Database::getConnection();

    $query = $conn->select('mytable', 'm')
      ->condition('id', $id)
      ->fields('m');
    $data = $query->execute()->fetchAssoc();
    $full_name = $data['first_name'] . ' ' . $data['last_name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $message = $data['message'];

    $file =File::load($data['fid']);
  
    $picture = $file->url();
    // echo "<pre>";
    // print_r($picture);
    // die();


    return [
      '#type' => 'markup',
      '#markup' => "<h1>$full_name</h1><br>
                    <img src='$picture' width='100' height='100' /> <br>
                    <p>$email</p>
                    <p>$phone</p>
                    <p>$message</p>"
    ];
  }

}