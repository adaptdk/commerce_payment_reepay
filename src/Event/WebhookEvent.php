<?php

namespace Drupal\commerce_payment_reepay\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the order event.
 *
 * @see \Drupal\commerce_order\Event\OrderEvents
 */
class WebhookEvent extends Event {

  const WEBHOOK_EVENT = 'commerce_payment_reepay.webhook.event';

  /**
   * The webhook event data.
   */
  protected $event;

  protected $state;

  protected $type;

  /**
   * Constructs a new WebhookEvent.
   */
  public function __construct($event, $state, $type) {
    $this->event = $event;
    $this->state = $state;
    $this->type = $type;
  }

  /**
   * Gets the data.
   */
  public function getEvent() {
    return $this->event;
  }

  public function getState() {
    return $this->state;
  }

  public function setState($state) {
    $this->state = $state;
  }

  public function getType() {
    $this->type;
  }

  public function setType($type) {
    $this->type = $type;
  }

}
