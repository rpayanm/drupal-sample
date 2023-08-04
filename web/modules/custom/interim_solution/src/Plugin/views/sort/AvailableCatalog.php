<?php

namespace Drupal\interim_solution\Plugin\views\sort;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\sort\SortPluginBase;
use Drupal\views\Plugin\ViewsHandlerManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sort handler, sorts by avaiable catalog field from interim solution table.
 *
 * @ingroup views_sort_handlers
 *
 * @ViewsSort("custom_field_interim_solution")
 */
class AvailableCatalog extends SortPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Views Handler Plugin Manager.
   *
   * @var \Drupal\views\Plugin\ViewsHandlerManager
   */
  protected $joinHandler;

  /**
   * Constructs a new BulkForm object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\views\Plugin\ViewsHandlerManager $join_handler
   *   Views Handler Plugin Manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ViewsHandlerManager $join_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->joinHandler = $join_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.views.join'),
    );
  }

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
    $join = $this->joinHandler->createInstance('standard', $definition);

    $interim_solution_table = $this->query->ensureTable('interim_solution', $this->relationship, $join);
    $available_catalog_content_field = $this->query->addField($interim_solution_table, 'available_catalog_content');

    $this->query->addOrderBy(NULL, NULL, $this->options['order'], $available_catalog_content_field);
  }

}
