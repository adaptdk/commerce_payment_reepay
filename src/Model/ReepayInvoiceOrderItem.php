<?php

namespace Drupal\commerce_payment_reepay\Model;

/**
 * Class ReepayInvoiceOrderItem
 *
 * @package Drupal\commerce_payment_reepay\Model
 */
class ReepayInvoiceOrderItem {

  /**
   * @var string
   */
  public $ordertext = '';

  /**
   * @var int
   */
  public $amount = 0;

  /**
   * @var int
   */
  public $vat = 0;

  /**
   * @var int
   */
  public $quantity = 1;

  /**
   * @var bool
   */
  public $amount_incl_vat = TRUE;

  /**
   * ReepayInvoiceOrderItem constructor.
   */
  public function __construct() {

  }

  /**
   * @return string
   */
  public function getOrderText(): string {
    return $this->ordertext;
  }

  /**
   * @param string $orderText
   *
   * @return \Drupal\commerce_payment_reepay\Model\InvoiceOrderItem
   */
  public function setOrderText(string $orderText): self {
    $this->ordertext = $orderText;

    return $this;
  }

  /**
   * @return int
   */
  public function getAmount(): int {
    return $this->amount;
  }

  /**
   * @param int $amount
   *
   * @return \Drupal\commerce_payment_reepay\Model\InvoiceOrderItem
   */
  public function setAmount(int $amount): self {
    $this->amount = $amount;

    return $this;
  }

  /**
   * @return int
   */
  public function getVat(): int {
    return $this->vat;
  }

  /**
   * @param int $vat
   *
   * @return \Drupal\commerce_payment_reepay\Model\InvoiceOrderItem
   */
  public function setVat(int $vat): self {
    $this->vat = $vat;

    return $this;
  }

  /**
   * @return int
   */
  public function getQuantity(): int {
    return $this->quantity;
  }

  /**
   * @param int $quantity
   *
   * @return \Drupal\commerce_payment_reepay\Model\InvoiceOrderItem
   */
  public function setQuantity(int $quantity): self {
    $this->quantity = $quantity;

    return $this;
  }

  /**
   * @return bool
   */
  public function isAmountInclVat(): bool {
    return $this->amount_incl_vat;
  }

  /**
   * @param bool $amount_incl_vat
   *
   * @return \Drupal\commerce_payment_reepay\Model\InvoiceOrderItem
   */
  public function setAmountInclVat(bool $amount_incl_vat): self {
    $this->amount_incl_vat = $amount_incl_vat;

    return $this;
  }

}