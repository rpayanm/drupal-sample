<?php

namespace Drupal\interim_solution\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\catalog\CatalogManager;

/**
 * QueueService service.
 */
class QueueService {

  /**
   * The first time queue name.
   *
   * @var string
   */
  const FIRST_TIME_QUEUE = 'interim_solution_cron_entity_first_time_requester';

  /**
   * The update time queue name.
   *
   * @var string
   */
  const UPDATE_QUEUE = 'interim_solution_cron_entity_update_requester';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entity_type_manager;

  /**
   * The interim solution utils service.
   *
   * @var \Drupal\interim_solution\Service\InterimSolutionUtils
   */
  private InterimSolutionUtils $interim_solution_utils;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  private QueueFactory $queue_factory;

  /**
   * The catalog api service.
   *
   * @var \Drupal\catalog\CatalogManager
   */
  private CatalogManager $catalog_api;

  /**
   * Constructs an QueueService object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    InterimSolutionUtils       $interim_solution_utils,
    QueueFactory               $queue_factory,
    CatalogManager             $catalog_api,
  ) {
    $this->entity_type_manager = $entity_type_manager;
    $this->interim_solution_utils = $interim_solution_utils;
    $this->queue_factory = $queue_factory;
    $this->catalog_api = $catalog_api;
  }

  /**
   * Process an item from the queue.
   *
   * @param $data
   *  The data.
   * @param $queue_name
   *   The queue name.
   *
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function processItem($data, $queue_name) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entity_type_manager->getStorage('node')->load($data['nid']);

    $result = $this->catalog_api->hasDigitalObjectsByFoia($node->get(InterimSolutionUtils::CATALOG_ID_FIELD)->value);

    if ($result === TRUE) {
      $this->interim_solution_utils->saveAvailableCatalogContent($node, TRUE);
    }
    elseif ($result === FALSE) {
      if ($queue_name == $this::UPDATE_QUEUE) {
        $this->interim_solution_utils->saveAvailableCatalogContent($node, FALSE);
        $this->addSimpleItemQueue($data, $this::FIRST_TIME_QUEUE);
      }
    }
  }

  /**
   * Add a media item to the queue.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The media id.
   * @param string $queue_name
   *   The queue name.
   *
   * @return void
   */
  public function addEntityItemQueue(EntityInterface $entity, string $queue_name): void {
    $item = [
      'nid' => $entity->id(),
    ];

    $this->addSimpleItemQueue($item, $queue_name);
  }

  /**
   * Adds an item to the queue.
   *
   * @param $data
   * @param $queue_name
   *
   * @return void
   */
  public function addSimpleItemQueue($data, $queue_name): void {
    $queue = $this->queue_factory->get($queue_name);
    $queue->createItem($data);
  }

  /**
   * Create a first time queue.
   */
  public function createFirstTimeQueue() {
    $first_time_queue = $this->queue_factory->get($this::FIRST_TIME_QUEUE);
    $nodes = $this->interim_solution_utils->getNotDigitalizedFindingAids();

    foreach ($nodes as $node) {
      $item = [
        'nid' => $node->id(),
      ];
      $first_time_queue->createItem($item);
    }
  }

  /**
   * Create an update queue.
   */
  public function createUpdateQueue() {
    $update_queue = $this->queue_factory->get($this::UPDATE_QUEUE);
    $nodes = $this->interim_solution_utils->getDigitalizedFindingAids();

    foreach ($nodes as $node) {
      $item = [
        'nid' => $node->id(),
      ];
      $update_queue->createItem($item);
    }
  }

}
