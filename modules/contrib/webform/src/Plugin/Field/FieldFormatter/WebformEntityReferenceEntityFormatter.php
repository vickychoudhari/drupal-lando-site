<?php

namespace Drupal\webform\Plugin\Field\FieldFormatter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\webform\Plugin\WebformSourceEntityManager;
use Drupal\webform\WebformMessageManagerInterface;
use Drupal\webform\WebformRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Webform rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "webform_entity_reference_entity_view",
 *   label = @Translation("Webform"),
 *   description = @Translation("Display the referenced webform with default submission data."),
 *   field_types = {
 *     "webform"
 *   }
 * )
 */
class WebformEntityReferenceEntityFormatter extends WebformEntityReferenceFormatterBase {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The webform message manager.
   *
   * @var \Drupal\webform\WebformMessageManagerInterface
   */
  protected $messageManager;

  /**
   * Webform request handler.
   *
   * @var \Drupal\webform\WebformRequestInterface
   */
  protected $requestHandler;

  /**
   * WebformEntityReferenceEntityFormatter constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\webform\WebformMessageManagerInterface $message_manager
   *   The webform message manager.
   * @param \Drupal\webform\WebformRequestInterface $request_handler
   *   The webform request handler.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, RendererInterface $renderer, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, WebformMessageManagerInterface $message_manager, WebformRequestInterface $request_handler = NULL) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $renderer, $config_factory);

    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->messageManager = $message_manager;
    $this->requestHandler = $request_handler ?: \Drupal::service('webform.request');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('renderer'),
      $container->get('config.factory'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('webform.message_manager'),
      $container->get('webform.request')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'source_entity' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Set submission source entity: @source_entity', ['@source_entity' => $this->getSetting('source_entity') ? $this->t('Yes') : $this->t('No')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    if ($this->fieldDefinition->getTargetEntityTypeId() === 'paragraph') {
      $title = $this->t("Use this paragraph field's main entity as the webform submission's source entity.");
      $description = $this->t("If unchecked, the current page's entity will be used as the webform submission's source entity.");
    }
    else {
      $entity_type_definition = $this->entityTypeManager->getDefinition($this->fieldDefinition->getTargetEntityTypeId());
      $title = $this->t("Use this field's %entity_type entity as the webform submission's source entity.", ['%entity_type' => $entity_type_definition->getLabel()]);
      $description = $this->t("If unchecked, the current page's entity will be used as the webform submission's source entity. For example, if this webform was displayed on a node's page, the current node would be used as the webform submission's source entity.", ['%entity_type' => $entity_type_definition->getLabel()]);
    }

    $form = parent::settingsForm($form, $form_state);
    $form['source_entity'] = [
      '#title' => $title,
      '#description' => $description,
      '#type' => 'checkbox',
      '#return_type' => TRUE,
      '#default_value' => $this->getSetting('source_entity'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Get items entity, which is the entity that the webform
    // is directly attached to. For Paragraphs this would be the field's
    // paragraph entity.
    $items_entity = $items->getEntity();

    // Get the items main entity. For Paragraphs this would be the parent entity
    // of the the paragraph field.
    $items_main_entity = WebformSourceEntityManager::getMainSourceEntity($items_entity);

    $request_source_entity = $this->requestHandler->getCurrentSourceEntity();

    // Determine if webform is previewed within a Paragraph on
    // entity edit forms (via *.edit_form or .content_translation_add routes).
    $route_name = $this->routeMatch->getRouteName();
    $is_entity_edit_form = (preg_match('/\.edit_form$/', $route_name)
      || preg_match('/\.content_translation_add$/', $route_name)
      || in_array($route_name, ['entity.block_content.canonical']));

    // Same goes for entity add forms.
    $is_entity_add_form = preg_match('/\.add$/', $route_name);

    $is_paragraph = ($items_entity && $items_entity->getEntityTypeId() === 'paragraph');

    $is_paragraph_current_source_entity = ($items_main_entity && $request_source_entity)
      && ($items_main_entity->getEntityTypeId() === $request_source_entity->getEntityTypeId())
      && ($items_main_entity->id() === $request_source_entity->id());

    $is_paragraph_entity_edit_form = ($is_entity_edit_form && $is_paragraph && $is_paragraph_current_source_entity);

    $is_paragraph_entity_add_form = ($is_entity_add_form && $is_paragraph);

    $elements = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      if ($is_paragraph_entity_edit_form || $is_paragraph_entity_add_form) {
        // Webform can not be nested within node edit form because the nested
        // <form> tags will cause unexpected validation issues.
        $elements[$delta] = [
          '#type' => 'webform_message',
          '#message_message' => $this->t('%label webform can not be previewed when editing content.', ['%label' => $entity->label()]),
          '#message_type' => 'info',
        ];
      }
      else {
        $elements[$delta] = [
          '#type' => 'webform',
          '#webform' => $entity,
          '#default_data' => (!empty($items[$delta]->default_data)) ? Yaml::decode($items[$delta]->default_data) : [],
          '#entity' => ($this->getSetting('source_entity')) ? $items_entity : NULL,
        ];
      }
      $this->setCacheContext($elements[$delta], $entity, $items[$delta]);
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity) {
    // Always allow access so that the Webform element can determine if the
    // Webform is accessible or an access denied message should be displayed.
    // @see \Drupal\webform\Element\Webform
    return AccessResult::allowed();
  }

}
