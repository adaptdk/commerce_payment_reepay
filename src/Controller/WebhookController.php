<?php

namespace Drupal\commerce_payment_reepay\Controller;

use Drupal\commerce_payment_reepay\Event\WebhookEvent;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class WebhookController.
 *
 * @package Drupal\commerce_payment_reepay\Controller
 */
class WebhookController extends ControllerBase {

  /**
   * Webhookcallback.
   *
   * @return string
   *   Return Hello string.
   */
  public function webhookCallback() {
    $webhook_event = \Drupal::request()->request->all();
    // @todo check signature.
    //signature = hexencode(hmac_sha_256(webhook_secret, timestamp + id));
    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new WebhookEvent($webhook_event);
    $dispatcher->dispatch(WebhookEvent::WEBHOOK_EVENT, $event);
    return TRUE;
  }

}
