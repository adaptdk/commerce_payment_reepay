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
   * The order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $event;

  /**
   * Constructs a new OrderEvent.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   */
  public function __construct($event) {
    $this->event = $event;
  }

  /**
   * Gets the order.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface
   *   Gets the order.
   */
  public function getEvent() {
    return $this->event;
  }

}
