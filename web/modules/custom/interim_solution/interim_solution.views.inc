<?php

/**
 * @file
 * Provide views data for interim_solution module.
 */

/**
 * Implements hook_views_data_alter().
 */
function interim_solution_views_data() {
  $data['views']['global_flag_interim_solution'] = [
    'title' => t('Show global flag for interim solution'),
    'help' => t('Provide markup for the global flag from interim solution.'),
    'field' => [
      'id' => 'custom_global_flag_interim_solution',
      'click sortable' => FALSE,
    ],
  ];
  return $data;
}

/**
 * Implements hook_views_data_alter().
 */
function interim_solution_views_data_alter(array &$data) {
  $data['node']['node_available_content_field'] = [
    'title' => t('Available catalog content field'),
    'group' => t('Content'),
    'field' => [
      'title' => t('Available catalog content field'),
      'help' => t('Information about available catalog content field.'),
      'id' => 'custom_field_interim_solution',
    ],
    'sort' => [
      'id' => 'custom_field_interim_solution',
    ],
    'filter' => [
      'id' => 'custom_field_interim_solution',
      'accept null' => TRUE,
    ],
  ];
  return $data;
}
