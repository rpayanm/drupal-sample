<?php

namespace Drupal\ob_timeline_migration\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DomCrawler\Crawler;

/**
 * This plugin processes the body field of the Timeline Details page.
 *
 * @MigrateProcessPlugin(
 *   id = "timeline_details_page"
 * )
 */
class TimeLineDetailsPageProcess extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $looking_for = $this->configuration['looking_for'] ?? NULL;
    if (!$value || !$looking_for) {
      throw new MigrateException('The value and the looking_for variables are empty.');
    }

    $crawler = new Crawler($value);
    $return = NULL;
    switch ($looking_for) {
      case 'gallery':
        $medias = $crawler
          ->filter('.nwidget .nwidget')->each(function (Crawler $node, $i) {
            $caption_title = $node->attr('data-caption-title');

            // Video.
            $transcript = $node->attr('data-transcript');
            $video = $node->attr('data-video');

            // Image.
            $image = $node->attr('data-image');
            $reference_number = $node->attr('data-reference-number');
            $alt = $node->attr('data-alt-tag');

            if ($video) {
              $return = [
                'type' => 'remote_video',
                'video' => $video,
                'transcript' => $transcript,
                'caption_title' => $caption_title,
              ];
            }
            else {
              $return = [
                'type' => 'image_catalog',
                'image' => $image,
                'caption_title' => $caption_title,
                'reference_number' => $reference_number,
                'alt' => $alt,
              ];
            }

            return $return;
          });

        $return = [];
        foreach ($medias as $media) {
          $entity_media = Media::create([
            'bundle' => $media['type'],
          ]);

          if ($media['type'] == 'remote_video') {
            if ($media['transcript']) {
              $caption = $media['caption_title'] . '<a href="' . $media['transcript'] . '" target="_blank">View Transcript</a>';
            }
            else {
              $caption = $media['caption_title'];
            }
            $entity_media->set('field_caption', $caption);
            $entity_media->set('field_media_oembed_video', $media['video']);
          }
          else {
            // Image.
            $fid = $this->getFidByUri($media['image']);

            $entity_media->set('field_caption', $media['caption_title']);
            $entity_media->set('field_artifact_id', $media['reference_number']);
            $entity_media->set('field_media_image', [
              'target_id' => $fid,
              'alt' => $media['alt'],
            ]);
          }

          $entity_media->save();
          $return[] = [
            'target_id' => $entity_media->id(),
            'target_revision_id' => $entity_media->getRevisionId(),
          ];
        }

        break;

      case 'description':
        $description = $crawler
          ->filterXPath('//body/*')
          ->reduce(function (Crawler $node, $i) {
            if ($node->text() == 'Media Gallery' || $node->text() == '&nbsp;' || $node->text() == ' ' || $node->text() == ' ') {
              return FALSE;
            }

            if (str_contains($node->attr('class'), 'nwidget')) {
              return FALSE;
            }

            return TRUE;
          });

        $return = $description->count() > 0 ? $description->html() : '';
        break;

      case 'detail_page_paragraph':
        $detail_page_id = $row->getSource()['field_timeline_detail'];
        if ($detail_page_id) {
          /** @var \Drupal\migrate\Plugin\MigrationPluginManager $plugin_manager_migration */
          $plugin_manager_migration = \Drupal::service('plugin.manager.migration');

          /** @var \Drupal\migrate\Plugin\MigrationInterface $events_migration */
          $events_migration = $plugin_manager_migration->createInstance('details_page_paragraphs');
          $target_id = $detail_page_id[0]['target_id'];
          $detail_paragraph = $events_migration->getIdMap()
            ->lookupDestinationIds(['nid' => $target_id]);
          $return = $detail_paragraph ? [
            'target_id' => $detail_paragraph[0][0],
            'target_revision_id' => $detail_paragraph[0][1],
          ] : NULL;
        }
        break;
    }

    return $return;
  }

  /**
   * Get the file id by uri.
   *
   * @param string $uri
   *   The uri of the file.
   *
   * @return false|int
   *   The file id or FALSE.
   */
  private function getFidByUri(string $uri): bool|int {
    $uri = urldecode($uri);
    $connection = Database::getConnection('default', 'migrate');
    $query = $connection->select('file_managed', 'fm')
      ->condition('fm.uri', str_replace('/sites/default/files/', 'public://', $uri))
      ->fields('fm');
    $file_managed_row = $query->execute()->fetchAssoc();
    Database::setActiveConnection();

    if (isset($file_managed_row['fid'])) {
      return $file_managed_row['fid'];
    }

    return FALSE;
  }

}
