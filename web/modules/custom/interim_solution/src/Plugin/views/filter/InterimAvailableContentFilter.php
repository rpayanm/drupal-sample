<?php

namespace Drupal\interim_solution\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\BooleanOperator;

/**
 * Filter handler for interim_solution field.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("custom_field_interim_solution")
 */
class InterimAvailableContentFilter extends BooleanOperator {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();

    $definition = [
      'table' => 'interim_solution',
      'field' => 'nid',
      'left_table' => 'node_field_data',
      'left_field' => 'nid',
    ];
    $join = \Drupal::service('plugin.manager.views.join')
      ->createInstance('standard', $definition);
    $interim_solution_table = $this->query->ensureTable('interim_solution', $this->relationship, $join);
    $field = "$interim_solution_table.available_catalog_content";
    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

}
