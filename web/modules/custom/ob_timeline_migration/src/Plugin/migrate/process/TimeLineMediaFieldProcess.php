<?php

namespace Drupal\ob_timeline_migration\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * This plugin gets the timeline media field.
 *
 * @MigrateProcessPlugin(
 *   id = "timeline_media_field"
 * )
 */
class TimeLineMediaFieldProcess extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $looking_for = $this->configuration['looking_for'] ?? NULL;
    if (!$value || !$looking_for) {
      throw new MigrateException('The value and the looking_for variables are empty.');
    }

    $connection = Database::getConnection('default', 'migrate');
    $query = $connection->select('file_managed', 'fm')
      ->condition('fm.fid', $value)
      ->fields('fm');
    $file_managed_row = $query->execute()->fetchAssoc();

    $return = NULL;
    switch ($looking_for) {
      case 'bundle':
        if (str_starts_with($file_managed_row['uri'], 'public://')) {
          $return = 'image_catalog';
        }
        elseif (str_starts_with($file_managed_row['uri'], 'youtube://')) {
          $return = 'remote_video';
        }
        break;

      case 'image_alt':
        $query = $connection->select('field_data_field_file_image_alt_text', 'fdfiat')
          ->condition('fdfiat.entity_id', $value)
          ->fields('fdfiat');
        $file_alt_row = $query->execute()->fetchAssoc();
        if ($file_alt_row) {
          $return = $file_alt_row['field_file_image_alt_text_value'];
        }
        break;

      case 'video_url':
        if (str_starts_with($file_managed_row['uri'], 'youtube://')) {
          $return = str_replace('youtube://v/', 'https://www.youtube.com/watch?v=', $file_managed_row['uri']);
        }
        break;

      case 'caption':
        $media_caption = $row->get('field_media_caption');
        if (isset($media_caption[0]['value'])) {
          $return = str_replace('<i class="fa fa-external-link" title="Our website includes links to many other federal agencies and, in some cases, we link to private organizations. Reference or links to any specific commercial products, service, or company does not constitute endorsement or recommendation by the U.S. Government or the National Archives. When you leave our website, you are subject to that site\'s privacy policy. We are not responsible for the content, information quality, security or Section 508 compliance (accessibility) of other websites."> </i>', '', $media_caption[0]['value']);
        }
        break;

      case 'status':
        $return = $file_managed_row['status'];
        break;
    }

    return $return;
  }

}
