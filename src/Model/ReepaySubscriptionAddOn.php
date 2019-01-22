<?php

namespace Drupal\commerce_payment_reepay\Model;

/**
 * Class ReepaySubscriptionAddOn
 *
 * @package Drupal\commerce_payment_reepay\Model
 */
class ReepaySubscriptionAddOn {

  /**
   * Name of add-on. Will be used as order line text.
   *
   * @var string
   */
  public $handle = '';

  /**
   * Optional description of add-on.
   *
   * @var int
   */
  public $quantity = 0;

  /**
   * Add-on amount.
   *
   * @var float
   */
  public $amount = 0;

  /**
   * Optional vat for add-on. Account default is used if none given.
   *
   * @var float
   */
  public $add_on = '';

  /**
   * Per account unique handle for the add-on.
   *
   * @var string
   */
  public $fixed_amount = true;

  /**
   * Whether the amount is including VAT.
   *
   * @var bool
   */
  public $amount_incl_vat = TRUE;

  /**
   * ReepayAddOn constructor.
   */
  public function __construct() {

  }

  /**
   * @return string
   */
  public function getHandle(): string {
    return $this->handle;
  }

  /**
   * @param string $handle
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscriptionAddOn
   */
  public function setHandle(string $handle): self {
    $this->handle = $handle;

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
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscriptionAddOn
   */
  public function setQuantity(int $quantity): self {
    $this->quantity = $quantity;

    return $this;
  }

  /**
   * @return float
   */
  public function getAmount(): float {
    return $this->amount;
  }

  /**
   * @param float $amount
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscriptionAddOn
   */
  public function setAmount(float $amount): self {
    $this->amount = $amount;

    return $this;
  }

  /**
   * @return string
   */
  public function getAddOn(): string {
    return $this->add_on;
  }

  /**
   * @param string $add_on
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscriptionAddOn
   */
  public function setAddOn(string $add_on): self {
    $this->add_on = $add_on;

    return $this;
  }

  /**
   * @return bool
   */
  public function getFixedAmount(): bool {
    return $this->fixed_amount;
  }

  /**
   * @param bool $fixed_amount
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscriptionAddOn
   */
  public function setFixedAmount(bool $fixed_amount): self {
    $this->fixed_amount = $fixed_amount;

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
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscriptionAddOn
   */
  public function setAmountInclVat(bool $amount_incl_vat): self {
    $this->amount_incl_vat = $amount_incl_vat;

    return $this;
  }

}
