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

  // Add a field to provide activity assignee contact ids in query string format.
  $data['civicrm_activity']['activity_assignees_string'] = [
    'real field' => 'id',
    'title' => 'Activity Assignees Query String',
    'field' => [
      'title' => 'Activity Assignees Query String',
      'help' => 'Activity assignee contact IDs in a query string.',
      'real field' => 'id',
      'id' => 'civicrm_entity_vf_activity_assignees_string',
    ],
  ];

}
