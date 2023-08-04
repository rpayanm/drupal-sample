<?php

namespace Drupal\interim_solution\Plugin\QueueWorker;

/**
 * A Requester that checks if there is content in the Catalog on CRON run.
 * It will process as many items as it can within 10 seconds.
 *
 * @QueueWorker(
 *   id = "interim_solution_cron_entity_update_requester",
 *   title = @Translation("Cron Finding aids Update Catalog Requester"),
 *   cron = {"time" = 5}
 * )
 */
class CronEntityUpdateRequester extends EntityUpdateRequestBase {

}