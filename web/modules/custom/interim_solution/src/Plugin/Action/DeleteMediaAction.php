<?php

namespace Drupal\interim_solution\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\interim_solution\Service\InterimSolutionUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Delete all the associated media.
 *
 * @Action(
 *   id = "action_delete_media_finding_aids",
 *   label = @Translation("Delete local catalog media from finding aids"),
 *   type = "node",
 *   confirm = TRUE,
 * )
 */
class DeleteMediaAction extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Interim Solution Utils class.
   */
  protected InterimSolutionUtils $interimSolutionUtils;

  /**
   * Constructs an DeleteMediaAction object.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\interim_solution\Service\InterimSolutionUtils $interim_solution_utils
   *   Interim solution utils class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, InterimSolutionUtils $interim_solution_utils) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->interimSolutionUtils = $interim_solution_utils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('interim_solution.utils')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if ($entity->bundle() === 'finding_aid') {
      if ($this->interimSolutionUtils->showCatalogData($entity)) {
        if (!$entity->{InterimSolutionUtils::FIELD_CATALOG_MEDIAS}->isEmpty()) {
          $medias = $entity->{InterimSolutionUtils::FIELD_CATALOG_MEDIAS}->referencedEntities();
          $nmedias = count($medias);
          foreach ($medias as $media) {
            $media->delete();
          }
          $entity->set(InterimSolutionUtils::FIELD_CATALOG_MEDIAS, NULL);
          $entity->save();
          $this->messenger()
            ->addMessage($this->t('@nmedias media(s) removed from %title.',
              ['@nmedias' => $nmedias, '%title' => $entity->label()]));
        }
        else {
          $this->messenger()
            ->addWarning($this->t("%title doesn't have local media to delete.", ['%title' => $entity->label()]));
        }
      }
      else {
        $this->messenger()
          ->addWarning($this->t("%title doesn't have available catalog media, or the media catalog flag is disabled.", ['%title' => $entity->label()]));
      }
    }
    else {
      $this->messenger()
        ->addWarning($this->t('No media was deleted, because %title is not a finding aid.', ['%title' => $entity->label()]));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\Core\Entity\EntityInterface $object */
    return $object->access('delete', $account, $return_as_object);
  }

}
