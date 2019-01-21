<?php

namespace Drupal\commerce_payment_reepay\Model;

/**
 * Class ReepayAddOn
 *
 * @package Drupal\commerce_payment_reepay\Model
 */
class ReepayAddOn {

  /**
   * An on_off type cannot be given a quantity when attached to subscription.
   */
  public const ADD_ON_TYPE_ON_OFF = 'on_off';

  /**
   * For quantity type it is possible to set a quantity.
   */
  public const ADD_ON_TYPE_QUANTITY = 'quantity';

  /**
   * Name of add-on. Will be used as order line text.
   *
   * @var string
   */
  protected $name = '';

  /**
   * Optional description of add-on.
   *
   * @var string
   */
  protected $description = '';

  /**
   * Add-on amount.
   *
   * @var int
   */
  protected $amount = 0;

  /**
   * Optional vat for add-on. Account default is used if none given.
   *
   * @var float
   */
  protected $vat = 0;

  /**
   * Per account unique handle for the add-on.
   *
   * @var string
   */
  protected $handle = '';

  /**
   * Add-on type on_off or quantity.
   *
   * @var string
   */
  protected $type = '';

  /**
   * Whether the amount is including VAT.
   *
   * @var bool
   */
  protected $amount_incl_vat = TRUE;

  /**
   * Whether all plans are eligible for this add-on.
   *
   * @var bool
   */
  protected $all_plans = FALSE;

  /**
   * If not all_plans are set to true, then the set of eligible plan handles must be defined.
   *
   * @var array
   */
  protected $eligible_plans = [];

  /**
   * ReepayAddOn constructor.
   */
  public function __construct() {

  }

  /**
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepayAddOn
   */
  public function setName(string $name): self {
    $this->name = $name;

    return $this;
  }

  /**
   * @return string
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * @param string $description
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepayAddOn
   */
  public function setDescription(string $description): self {
    $this->description = $description;

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
   * @return \Drupal\commerce_payment_reepay\Model\ReepayAddOn
   */
  public function setAmount(int $amount): self {
    $this->amount = $amount;

    return $this;
  }

  /**
   * @return float
   */
  public function getVat(): float {
    return $this->vat;
  }

  /**
   * @param float $vat
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepayAddOn
   */
  public function setVat(float $vat): self {
    $this->vat = $vat;

    return $this;
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
   * @return \Drupal\commerce_payment_reepay\Model\ReepayAddOn
   */
  public function setHandle(string $handle): self {
    $this->handle = $handle;

    return $this;
  }

  /**
   * @return string
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * @param string $type
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepayAddOn
   */
  public function setType(string $type): self {
    $this->type = $type;

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
   * @return \Drupal\commerce_payment_reepay\Model\ReepayAddOn
   */
  public function setAmountInclVat(bool $amount_incl_vat): self {
    $this->amount_incl_vat = $amount_incl_vat;

    return $this;
  }

  /**
   * @return bool
   */
  public function isAllPlans(): bool {
    return $this->all_plans;
  }

  /**
   * @param bool $all_plans
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepayAddOn
   */
  public function setAllPlans(bool $all_plans): self {
    $this->all_plans = $all_plans;

    return $this;
  }

  /**
   * @return array
   */
  public function getEligiblePlans(): array {
    return $this->eligible_plans;
  }

  /**
   * @param array $eligible_plans
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepayAddOn
   */
  public function setEligiblePlans(array $eligible_plans): self {
    $this->eligible_plans = $eligible_plans;

    return $this;
  }

}
