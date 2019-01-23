<?php

namespace Drupal\commerce_payment_reepay\Controller;

use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\commerce_payment_reepay\Event\WebhookEvent;
use Drupal\commerce_payment_reepay\ReepayApi;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;

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
    $webhookEvent = \Drupal::request()->getContent();
    if (is_string($webhookEvent)) {
      $webhookEvent = json_decode($webhookEvent);
    }
    // @todo check signature.
    //signature = hexencode(hmac_sha_256(webhook_secret, timestamp + id));
    $eventType = $webhookEvent->event_type;
//    $wehHookId = $webhookEvent->id;
//    $webHookData = $this->getWebHookData($wehHookId);
//    if (isset($webHookData->code) && $webHookData->code !== 200) {
//      return new HtmlResponse('Error');
//    }
//    if (is_string($webHookData)) {
//      $webHookData = json_decode($webHookData);
//    }
//    $data = $webHookData->content;
    $webHookState = '';//$webHookData->state;
    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new WebhookEvent($webhookEvent, $webHookState, $eventType);
    $dispatcher->dispatch(WebhookEvent::WEBHOOK_EVENT, $event);
    return new HtmlResponse('Done');
  }

  protected function getWebHookData($id) {
    $payment_gateway_plugin = PaymentGateway::load('reepay_js')->getPlugin();
    $configuration = $payment_gateway_plugin->getConfiguration();

    $reepay = new ReepayApi($configuration['private_key']);
    return $reepay->getWebHook($id);
  }

}
