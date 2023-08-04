<?php

namespace Drupal\ob_timeline_migration\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Event\MigrateRollbackEvent;
use Drupal\migrate\Event\MigrateRowDeleteEvent;
use Drupal\path_alias\Entity\PathAlias;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Response subscriber to the migration events.
 */
class MigrationSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private EntityTypeManager $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      MigrateEvents::POST_IMPORT => [
        ['postImport'],
      ],
      MigrateEvents::POST_ROLLBACK => [
        ['postRollback'],
      ],
      MigrateEvents::PRE_ROW_DELETE => [
        ['preRowDelete'],
      ],
    ];
  }

  /**
   * Called whenever the MigrateEvents::POST_IMPORT event is dispatched.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function postImport(MigrateImportEvent $event) {
    $migration = $event->getMigration();
    $id = $migration->id();
    if ($id == 'event_paragraphs' || $id == 'era_paragraphs') {
      /** @var \Drupal\migrate\Plugin\MigrationPluginManager $plugin_manager_migration */
      $plugin_manager_migration = \Drupal::service('plugin.manager.migration');

      /** @var \Drupal\migrate\Plugin\MigrationInterface $events_migration */
      $events_migration = $plugin_manager_migration->createInstance('event_paragraphs');
      /** @var \Drupal\migrate\Plugin\MigrationInterface $eras_migration */
      $eras_migration = $plugin_manager_migration->createInstance('era_paragraphs');

      if ($events_migration->getSourcePlugin()
          ->count() - $events_migration->getIdMap()->processedCount() === 0
        && $eras_migration->getSourcePlugin()
          ->count() - $eras_migration->getIdMap()->processedCount() === 0) {
        // Check if there is a Timeline node.
        $timeline = $this->entityTypeManager->getStorage('node')->getQuery()
          ->condition('type', 'timeline')
          ->execute();

        if (empty($timeline)) {
          // Create the timeline node.
          $timeline = $this->entityTypeManager->getStorage('node')->create([
            'type' => 'timeline',
            'title' => 'Timeline',
            'status' => 1,
          ]);
        }
        else {
          // Load the timeline node.
          $timeline = $this->entityTypeManager->getStorage('node')
            ->load(reset($timeline));
        }

        // Timeline events.
        $events = $this->entityTypeManager->getStorage('paragraph')
          ->loadByProperties([
            'type' => 'timeline_event',
          ]);

        $timeline->field_timeline_events = [];
        foreach ($events as $event) {
          $timeline->field_timeline_events->appendItem($event);
        }

        // Timeline eras.
        $eras = $this->entityTypeManager->getStorage('paragraph')
          ->loadByProperties([
            'type' => 'timeline_era',
          ]);

        $timeline->field_timeline_eras = [];
        foreach ($eras as $era) {
          $timeline->field_timeline_eras->appendItem($era);
        }

        $result = $timeline->save();

        if ($result === SAVED_NEW) {
          $path_alias = PathAlias::create([
            'path' => '/node/' . $timeline->id(),
            'alias' => '/timeline',
          ]);

          $path_alias->save();
        }
      }
    }
  }

  /**
   * Called whenever the MigrateEvents::POST_ROLLBACK event is dispatched.
   */
  public function postRollback(MigrateRollbackEvent $event) {
    $migration = $event->getMigration();
    $id = $migration->id();
    if ($id == 'event_paragraphs' || $id == 'era_paragraphs') {
      /** @var \Drupal\migrate\Plugin\MigrationPluginManager $plugin_manager_migration */
      $plugin_manager_migration = \Drupal::service('plugin.manager.migration');

      /** @var \Drupal\migrate\Plugin\MigrationInterface $events_migration */
      $events_migration = $plugin_manager_migration->createInstance('event_paragraphs');
      /** @var \Drupal\migrate\Plugin\MigrationInterface $eras_migration */
      $eras_migration = $plugin_manager_migration->createInstance('era_paragraphs');

      if ($events_migration->getIdMap()
          ->processedCount() == 0 && $eras_migration->getIdMap()
          ->importedCount() == 0) {
        // Remove the timeline node.
        $timeline = $this->entityTypeManager->getStorage('node')
          ->loadByProperties([
            'type' => 'timeline',
          ]);

        if ($timeline) {
          foreach ($timeline as $node) {
            $node->delete();
          }
        }
      }
    }
  }

  /**
   * Called whenever the MigrateEvents::PRE_ROW_DELETE event is dispatched.
   */
  public function preRowDelete(MigrateRowDeleteEvent $event) {
    $migration = $event->getMigration();
    $id = $migration->id();

    // Deletes the media entities from the Details paragraph.
    if ($id == 'details_page_paragraphs') {
      $paragraph_id = $event->getDestinationIdValues()['id'];
      $paragraph = $this->entityTypeManager->getStorage('paragraph')
        ->load($paragraph_id);

      if ($paragraph) {
        foreach ($paragraph->get('field_ed_media_gallery') as $item) {
          $media = $this->entityTypeManager->getStorage('media')
            ->load($item->target_id);
          if ($media) {
            $media->delete();
          }
        }
      }
    }
  }

}
