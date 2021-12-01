<?php

namespace Drupal\article\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
// use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
// use Symfony\Component\DependencyInjection\ContainerInterface;
// use Drupal\Core\Session\AccountProxyInterface;
// use Drupal\media\Entity\Media;
// use Drupal\file\Entity\File;
// use Drupal\node\Entity\Node;

/**
 * Provides a 'article' block.
 *
 * @Block(
 *   id = "article_block",
 *   admin_label = @Translation("Article block"),
 *   category = @Translation("Custom article block example")
 * )
 */
class ArticleBlock extends BlockBase {

	
  /**
   * {@inheritdoc}
   */
  public function build() {


$connection = \Drupal::database();

$qry = $connection->select('node_field_data' , 'nfd');
$qry->join('node__field_images', 'nfi', 'nfd.nid = nfi.entity_id');
$qry->join('file_managed', 'fm','nfd.nid = fm.fid');
$qry->join('node__field_description', 'nfde','nfd.nid = nfde.entity_id');
$qry->fields('nfi',['field_images_alt', 'entity_id', 'field_images_target_id'])
->fields('nfd', ['nid', 'title', 'type'])
->fields('fm', ['fid', 'uri'])
->fields('nfde', ['entity_id', 'field_description_value'])
->condition('nfd.type', 'image_styles')
->range(0,3);
                 
          $result = $qry->execute()->fetchAll();
          

//                        // $qry = $connection->select('node' , 'n');
//                        // $result = $qry->execute()->fetchAll();
//                        // echo"<pre>;
//                        // print_r($result);
//                        // die();



                 
                    $list_of_images = [];
                 foreach ($result as $key => $value) {
                    $list_of_images[$key]['title'] = $value->title;
                    $list_of_images[$key]['uri'] = $value->uri;
                    $list_of_images[$key]['description'] = $value->field_description_value;
                    $list_of_images[$key]['type'] = $value->type;
                    }
               
                // echo "<pre>";
                // print_r($list_of_images);
                // die();
                return [
                '#theme' => 'article_tpl',
                '#result_node' => $list_of_images,
                '#cache' => ['max-age' => 0,
                '#attached' => array(
                  'library' => array(
                  'article/cuddly-slider',
                     ),
                  ),
                ],
  ];          
  }
  }
