<?php

/**
 * Implements hook_views_data_alter().
 */
function civicrm_entity_vf_views_data_alter(array &$data) {

  // Add select filter for contacts who are also users.
  $data['civicrm_contact']['uf_select'] = [
    'real field' => 'id',
    'title' => 'User Contact',
    'filter' => [
      'title' => 'User Contact',
      'id' => 'civicrm_entity_vf_uf_select',
      'help' => 'Select contacts who are also users.',
    ],
  ];

}
