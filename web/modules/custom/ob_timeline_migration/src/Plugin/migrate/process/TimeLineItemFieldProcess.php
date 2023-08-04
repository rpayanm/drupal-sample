<?php

namespace Drupal\ob_timeline_migration\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * This plugin gets the timeline item fields.
 *
 * @MigrateProcessPlugin(
 *   id = "timeline_item_field"
 * )
 */
class TimeLineItemFieldProcess extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $looking_for = $this->configuration['looking_for'] ?? NULL;
    if (!$value || !$looking_for) {
      throw new MigrateException('The value and the looking_for variables are empty.');
    }

    $connection = Database::getConnection('default', 'migrate');
    $query = $connection->select('url_alias', 'ua')
      ->condition('ua.source', 'node/' . $value)
      ->fields('ua');
    $alias = $query->execute()->fetchAssoc();

    $return = NULL;
    switch ($looking_for) {
      case 'alias':
        if (isset($alias['alias'])) {
          $return = $alias['alias'];
        }
        break;
    }

    return $return;
  }

}
