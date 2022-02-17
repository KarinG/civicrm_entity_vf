<?php

namespace Drupal\civicrm_entity_vf\Plugin\views\filter;

use Drupal\views\Views;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\civicrm_entity\CiviCrmApiInterface;

/**
 * Views filter handler for user contacts.
 *
 * @ViewsFilter("civicrm_entity_vf_uf_select")
 */
class UfSelect extends InOperator implements ContainerFactoryPluginInterface {

  /**
   * User entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userEntityStorage;

  /**
   * User entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $userQuery;

  /**
   * The CiviCRM API.
   *
   * @var \Drupal\civicrm_entity\CiviCrmApiInterface
   */
  protected $civicrmApi;

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\Core\Entity\EntityStorageInterface $userEntityStorage
   *   User entity storage object.
   * @param Drupal\Core\Entity\Query\QueryInterface $userQuery
   *   User entity query object.
   * @param Drupal\civicrm_entity\CiviCrmApiInterface $civicrmApi
   *   The CiviCRM Api.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $userEntityStorage, QueryInterface $userQuery, CiviCrmApiInterface $civicrmApi) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userEntityStorage = $userEntityStorage;
    $this->userQuery = $userQuery;
    $this->civicrmApi = $civicrmApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('entity_type.manager')->getStorage('user')->getQuery(),
      $container->get('civicrm_entity.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {

    if (!isset($this->valueOptions)) {

      // Get active user uids.
      $uids = $this->userQuery
        ->condition('status', 1)
        ->execute();

      // Get user display names.
      $users = $this->userEntityStorage->loadMultiple($uids);
      $user_display_names = [];
      foreach ($users as $uid => $user) {
        $user_display_names[$uid] = $user->getDisplayName();
      }

      // Get matching list of CiviCRM contacts.
      $user_contacts = $this->civicrmApi->get('UFMatch', [
        'sequential' => 1,
        'uf_id' => ['IN' => $uids],
        'options' => ['limit' => count($uids)],
        'return' => ['uf_id', 'contact_id.id'],
      ]);

      // Build valueOptions.
      foreach ($user_contacts as $contact) {
        $this->valueOptions[$contact['contact_id.id']] = $user_display_names[$contact['uf_id']];
      }

      // Sort by username.
      natcasesort($this->valueOptions);
    }

    return $this->valueOptions;
  }
}
