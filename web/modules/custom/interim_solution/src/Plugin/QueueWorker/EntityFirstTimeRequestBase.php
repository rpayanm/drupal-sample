<?php

namespace Drupal\interim_solution\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\interim_solution\Service\QueueService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides base functionality for the EntityRequestBase Queue Workers.
 */
abstract class EntityFirstTimeRequestBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\interim_solution\Service\QueueService
   */
  private QueueService $queue_service;

  /**
   * Creates a new EntityRequestBase object.
   */
  public function __construct(
    array        $configuration, $plugin_id, $plugin_definition,
    QueueService $queue_service,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->queue_service = $queue_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('interim_solution.queue'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->queue_service->processItem($data, QueueService::FIRST_TIME_QUEUE);
  }

}