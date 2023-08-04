<?php

namespace Drupal\interim_solution\Plugin\QueueWorker;

/**
 * A Requester that checks if there is content in the Catalog on CRON run.
 * It will process as many items as it can within 10 seconds.
 *
 * @QueueWorker(
 *   id = "interim_solution_cron_entity_first_time_requester",
 *   title = @Translation("Cron Entity Requester"),
 *   cron = {"time" = 10}
 * )
 */
class CronEntityFirstTimeRequester extends EntityFirstTimeRequestBase {

}