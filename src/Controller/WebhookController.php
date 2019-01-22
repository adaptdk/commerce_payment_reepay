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
    /*
    {
      "id": "8ab4b56439944e62ababca7954355578",
      "event_id": "680bd8055b5c444bb467dcb731c65bf9",
      "event_type": "invoice_settled",
      "timestamp": "2015-06-25T12:10:00.64Z",
      "signature": "7a591eddc400af4c8a64ff551ff90b37d79471bd2c9a5a2fcd4ed6944f39cb09",
      "customer": "OVAFIJ",
      "subscription": "sub001",
      "invoice": "23688c2c758040f9bc6cdee0fad6cc77",
      "transaction" : "28b1af53d7ecd1c487292402a908e2b3",
      "credit_note" : "a26c1bf5f656f489f295d0c4748dd003",
      "credit" : "inv-12-credit-3241",
    }
     */
    // @todo check signature.
    //signature = hexencode(hmac_sha_256(webhook_secret, timestamp + id));
    $eventType = $webhookEvent->event_type;
    $wehHookId = $webhookEvent->id;
    $webHookData = $this->getWebHookData($wehHookId);
    if (isset($webHookData->code) && $webHookData->code !== 200) {
      return new HtmlResponse('Error');
    }
    if (is_string($webHookData)) {
      $webHookData = json_decode($webHookData);
    }
    $webHookState = $webHookData->state;
    $data = $webHookData->content;
    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new WebhookEvent($data, $webHookState, $eventType);
    $dispatcher->dispatch(WebhookEvent::WEBHOOK_EVENT, $event);
    return new HtmlResponse('Done');
  }

  protected function getWebHookData($id) {
    $payment_gateway_plugin = PaymentGateway::load('reepay_js')->getPlugin();
    $configuration = $payment_gateway_plugin->getConfiguration();

    $reepay = new ReepayApi($configuration['private_key']);
//    return '{"id":"dafba2016614418f969fa5697383e47c","event":"a7a7195c54f644369922d0dfe794dd0c","state":"pending","url":"https://api.ownserver.com/webhook","username":"username","password":"password","content":{"id":"66fae1bb45ddaa29a49d0db3f2cc4363","timestamp":"2017-03-28T12:20:34.273Z","signature":"682dd5a5d1fc253cf6469355c64827a7bfacaecd5a48cf071be07b1d8911ff87","invoice":"ac0a4f063ef5c5344e85be6dcec773ed","subscription":"sub-0023","customer":"cust-0015","transaction":"152f91646daea2f5614729cc2bd07bd5","event_type":"invoice_settled","event_id":"34862b053efb119edd170b8257917f6f"},"created":"2015-04-04T12:40:56.656+00:00","success":"2015-04-04T12:40:56.656+00:00","count":2,"last_fail":"2015-04-04T12:40:56.656+00:00","first_fail":"2015-04-04T12:40:56.656+00:00","alert_count":2,"alert_sent":"2015-04-04T12:40:56.656+00:00"}';
    return $reepay->getWebHook($id);
  }

}
