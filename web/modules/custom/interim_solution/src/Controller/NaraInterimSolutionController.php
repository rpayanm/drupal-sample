<?php

namespace Drupal\interim_solution\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Url;
use Drupal\interim_solution\Service\InterimSolutionUtils;
use Drupal\interim_solution\Service\QueueService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InterimSolutionController extends ControllerBase {

  /**
   * The queue.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  private QueueFactory $queue;

  /**
   * The interim_solution_utils service.
   *
   * @var \Drupal\interim_solution\Service\InterimSolutionUtils
   */
  private InterimSolutionUtils $interim_solution_utils;

  /**
   * Constructs an InterimSolutionController object.
   */
  public function __construct(
    QueueFactory         $queue,
    InterimSolutionUtils $interim_solution_utils,
  ) {
    $this->queue = $queue;
    $this->interim_solution_utils = $interim_solution_utils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('queue'),
      $container->get('interim_solution.utils')
    );
  }

  /**
   * Generates a report.
   *
   * @return array
   *   The render array.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function report() {
    $automatic_switch_setting_page = $this->t('You can change this setting in the <a target="@target" href=":url">configuration page</a>.', [
      '@target' => "_blank",
      ':url' => Url::fromRoute('interim_solution.admin_settings_form')
        ->toString(),
    ]);
    $automatic_switch = $this->config('interim_solution.settings')
      ->get('automatic_switch');
    $items_approved = [];
    if (!$automatic_switch) {
      $build['not_digitalized_finding_aids']['automatic_switch'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Automatic switch is not enabled.') . " " . $automatic_switch_setting_page,
      ];
      $nodes = $this->interim_solution_utils->getDigitalizedFindingAids();
      $items_to_be_approved = [];
      foreach ($nodes as $node) {
        $item = [
          'data' => [
            '#type' => 'link',
            '#title' => $node->getTitle(),
            '#url' => $node->toUrl(),
            '#attributes' => [
              'target' => '_blank',
            ],
          ],
        ];
        if ($node->get(InterimSolutionUtils::FIELD_SHOW_CATALOG_CONTENT)->value != '1') {
          $items_to_be_approved[] = $item;
        }
        else {
          $items_approved[] = $item;
        }
      }
      if (count($items_to_be_approved) > 0) {
        $build['not_digitalized_finding_aids']['header'] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('There are @num finding aids pending to be approved.', [
            '@num' => count($items_to_be_approved),
          ]),
        ];
        $build['not_digitalized_finding_aids']['items'] = [
          '#theme' => 'item_list',
          '#items' => $items_to_be_approved,
        ];
      }
      else {
        $build['not_digitalized_finding_aids']['header'] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('There are no finding aids pending to be approved.'),
        ];
      }
    }
    else {
      $build['not_digitalized_finding_aids']['automatic_switch'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Automatic switch is enabled. All Finding aids will display its assets from Catalog without approval.') . ' ' . $automatic_switch_setting_page,
      ];
    }

    $nodes = $this->interim_solution_utils->getDigitalizedFindingAids();

    $build['digitalized_finding_aids'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('There are @num digitalized finding aids.', [
        '@num' => count($nodes),
      ]),
    ];

    if (!$automatic_switch) {
      $build['finding_aids_approved']['header'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('There are @num finding aids approved.', [
          '@num' => count($items_approved),
        ]),
      ];

      $build['finding_aids_approved']['items'] = [
        '#theme' => 'item_list',
        '#items' => $items_approved,
      ];
    }

    $first_time_queue_items = $this->queue->get(QueueService::FIRST_TIME_QUEUE)
      ->numberOfItems();
    $build['first_time_queue_info'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('There are @items items in the first time queue.', [
        '@items' => $first_time_queue_items,
      ]),
    ];

    $update_queue_items = $this->queue->get(QueueService::UPDATE_QUEUE)
      ->numberOfItems();
    $build['update_queue_info'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('There are @items items in the update queue.', [
        '@items' => $update_queue_items,
      ]),
    ];

    return $build;
  }

}