<?php

namespace Drupal\interim_solution\Service;

use Drupal;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\catalog\CatalogManager;
use Drupal\node\NodeInterface;


/**
 * InterimSolutionUtils service.
 */
class InterimSolutionUtils {
  use StringTranslationTrait;

  /**
   * Catalog ID field.
   *
   * @var string
   */
  const CATALOG_ID_FIELD = 'field_foia_tracking_number';

  /**
   * Show catalog content field (The user wants to show the Catalog assets).
   *
   * @var string
   */
  const FIELD_SHOW_CATALOG_CONTENT = 'field_show_catalog_content';

  /**
   * Catalog medias field.
   *
   * @var string
   */
  const FIELD_CATALOG_MEDIAS = 'field_catalog_medias';

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $config_factory;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entity_type_manager;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private LoggerChannelFactoryInterface $logger_factory;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  private MailManagerInterface $mail_manager;

  /**
   * The catalog api.
   *
   * @var \Drupal\catalog\CatalogManager
   */
  private CatalogManager $catalog_api;

  /**
   * Constructs an InterimSolutionUtils object.
   */
  public function __construct(
    ConfigFactoryInterface        $config_factory,
    EntityTypeManagerInterface    $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    MailManagerInterface          $mail_manager,
    CatalogManager                $catalog_api,
  ) {
    $this->config_factory = $config_factory;
    $this->entity_type_manager = $entity_type_manager;
    $this->logger_factory = $logger_factory;
    $this->mail_manager = $mail_manager;
    $this->catalog_api = $catalog_api;
  }

  /**
   * Returns TRUE if the catalog data should be shown.
   *
   * @param \Drupal\node\NodeInterface $node
   *
   * @return bool
   */
  public function showCatalogData(NodeInterface $node) {
    $digitalized_content = $this->isInCatalog($node->id());
    $show_catalog_content = $node->get(InterimSolutionUtils::FIELD_SHOW_CATALOG_CONTENT)->value;

    $automatic_switch = $this->config_factory->get('interim_solution.settings')
      ->get('automatic_switch');

    if ($digitalized_content) {
      return $automatic_switch || $show_catalog_content;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Gets all Not Digitalized Finding Aids.
   */
  public function getNotDigitalizedFindingAids() {
    $database = Drupal::database();
    $query = $database->select('node_field_data', 'nfd');
    $query->leftJoin('node__field_foia_tracking_number', 'nftn', 'nfd.nid = nftn.entity_id');
    $query->leftJoin('interim_solution', 'nis', 'nfd.nid = nis.nid');
    $query
      ->fields('nfd', ['nid'])
      ->condition('nfd.type', 'finding_aid')
      ->condition('nfd.status', 1)
      ->condition('nftn.field_foia_tracking_number_value', NULL, 'IS NOT NULL');

    $or = $query->orConditionGroup();
    $or->condition('nis.available_catalog_content', 0);
    $or->condition('nis.available_catalog_content', NULL, 'IS NULL');
    $query->condition($or);
    $nids = $query->execute()->fetchCol();

    $nodes = $this->entity_type_manager->getStorage('node')
      ->loadMultiple($nids);
    return $nodes;
  }

  /**
   * Gets all Digitalized Finding Aids.
   */
  public function getDigitalizedFindingAids() {
    $database = Drupal::database();
    $query = $database->select('node_field_data', 'nfd');
    $query->join('interim_solution', 'nis', 'nfd.nid = nis.nid');
    $query
      ->fields('nfd', ['nid'])
      ->condition('nfd.type', 'finding_aid')
      ->condition('nfd.status', 1)
      ->condition('nis.available_catalog_content', 1);
    $nids = $query->execute()->fetchCol();

    $nodes = $this->entity_type_manager->getStorage('node')
      ->loadMultiple($nids);
    return $nodes;
  }

  /**
   * Saves or removes if the Finding aid already has its assets in the Catalog.
   */
  public function saveAvailableCatalogContent(NodeInterface $node, bool $available_catalog_content) {
    $database = Drupal::database();
    $query = $database->merge('interim_solution');
    $query->key('nid', $node->id());
    $query->fields([
      'nid' => $node->id(),
      'available_catalog_content' => (int) $available_catalog_content,
    ]);
    $query->execute();

    if ($available_catalog_content) {
      $this->logger_factory->get('interim_solution')
        ->info('Node with nid @nid and foia_tracking_number @foia_tracking_number has its assets in the Catalog.', [
          '@nid' => $node->id(),
          '@foia_tracking_number' => $node->get(InterimSolutionUtils::CATALOG_ID_FIELD)->value,
        ]);

      $config = $this->config_factory->get('interim_solution.settings');
      if ($config->get('automatic_switch') != 1 && $config->get('email') != '') {
        $this->sendEmail($node, $config->get('email'));
      }
    }
    else {
      $this->logger_factory->get('interim_solution')
        ->info('The assets of the node @nid are removed in the Catalog.', ['@nid' => $node->id()]);
    }
  }

  /**
   * Sends an email when it is an asset in the Catalog.
   */
  public function sendEmail(NodeInterface $node, $to) {
    $site_name = $this->config_factory->get('system.site')->get('name');
    $params['subject'] = '[' . $site_name . '] Finding Aid Digitalized';
    $params['body'] = $this->t("The finding aid <a href=':url'>@title</a> has been digitalized and their assets are in the Catalog. It needs your approval.", [
      ':url' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()])
        ->setAbsolute()
        ->toString(),
      '@title' => $node->getTitle(),
    ]);
    $params['from'] = $this->config_factory->get('system.site')->get('mail');
    $params['to'] = $to;
    $params['langcode'] = $node->language()->getId();
    $params['node'] = $node;
    $result = $this->mail_manager->mail('interim_solution', 'finding_aids_digitalized', $to, $node->language()
      ->getId(), $params);
    if ($result['result'] !== TRUE) {
      $this->logger_factory->get('interim_solution')
        ->error('There was a problem sending the email to %to. Findind Aid id: %nid', [
          '%to' => $to,
          '%nid' => $node->id(),
        ]);
    }
  }

  /**
   * Returns TRUE if the node is in the Catalog.
   *
   * @param $nid
   *
   * @return bool
   */
  public function isInCatalog($nid) {
    $database = Drupal::database();
    $query = $database->select('interim_solution', 'nis');
    $query
      ->fields('nis', ['nid'])
      ->condition('nis.nid', $nid);
    $nids = $query->execute()->fetchCol();
    return !empty($nids);
  }

  /**
   * Checks if the node has assets in the Catalog.
   *
   * @param \Drupal\node\NodeInterface $node
   *  The node to check.
   *
   * @return void
   */
  public function checkForDigitalObjects(NodeInterface $node) {
    if ($node->get(InterimSolutionUtils::CATALOG_ID_FIELD)->value) {
      $result = $this->catalog_api->hasDigitalObjectsByFoia($node->get(InterimSolutionUtils::CATALOG_ID_FIELD)->value);

      if ($result === TRUE) {
        $this->saveAvailableCatalogContent($node, TRUE);
      }
    }
  }

}
