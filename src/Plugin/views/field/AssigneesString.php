<?php

namespace Drupal\civicrm_entity_vf\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\civicrm_entity\CiviCrmApiInterface;
use Drupal\views\ResultRow;

/**
 * @ingroup views_field_handlers
 *
 * @ViewsField("civicrm_entity_vf_activity_assignees_string")
 */
class AssigneesString extends FieldPluginBase {

  /**
   * The CiviCRM API.
   *
   * @var \Drupal\civicrm_entity\CiviCrmApiInterface
   */
  protected $civicrmApi;

  /**
   * Constructs a CiviCrmEntityVfAssignees object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\civicrm_entity\CiviCrmApiInterface $civicrmApi
   *   The CiviCRM Api.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CiviCrmApiInterface $civicrmApi) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->civicrmApi = $civicrmApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('civicrm_entity.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $activity_id = $this->getValue($values);

    $contacts = $this->getActivityContacts($activity_id);

    // Build query string, starting with cid3={contact_id}
    $params = [];
    $cid_index = 3;
    foreach (array_column($contacts, 'contact_id') as $id) {
      $params[] = 'cid' . $cid_index . '=' . $id;
      $cid_index++;
    }
    $string = implode('&', $params);

    return $string;
  }

  /**
   * civicrmApi query.
   */
  function getActivityContacts($id, $type = "Activity Assignees") {

    $result = $this->civicrmApi->get('ActivityContact', [
      'sequential' => 1,
      'return' => ['contact_id'],
      'activity_id' => $id,
      'record_type_id' => $type,
    ]);

    return $result;
  }

}
