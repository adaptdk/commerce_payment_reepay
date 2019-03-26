<?php

namespace Drupal\commerce_payment_reepay\Model;

/**
 * Class ReepaySubscription
 *
 * @package Drupal\commerce_payment_reepay\Model
 */
class ReepaySubscription {

  /**
   * @var string
   */
  public $customer = '';

  /**
   * @var string
   */
  public $plan = '';

  /**
   * @var string
   */
  public $handle = '';

  /**
   * @var string
   */
  public $signup_method = 'card_token';

  /**
   * @var string
   */
  public $card_token = '';

  /**
   * @var bool
   */
  public $no_trial = TRUE;

  /**
   * @var bool
   */
  public $test = FALSE;

  /**
   * @var bool
   */
  public $generate_handle = FALSE;

  /**
   * @var array
   */
  public $add_ons = [];

  /**
   * @var \stdClass
   */
  public $hosted_page_links;

  /**
   * ReepaySubscription constructor.
   */
  public function __construct() {

  }

  /**
   * @return string
   */
  public function getCustomer(): string {
    return $this->customer;
  }

  /**
   * @param string $customer
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setCustomer(string $customer): self {
    $this->customer = $customer;

    return $this;
  }

  /**
   * @return string
   */
  public function getPlan(): string {
    return $this->plan;
  }

  /**
   * @param string $plan
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setPlan(string $plan): self {
    $this->plan = $plan;

    return $this;
  }

  /**
   * @return string
   */
  public function getSignupMethod(): string {
    return $this->signup_method;
  }

  /**
   * @param string $signup_method
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setSignupMethod(string $signup_method): self {
    $this->signup_method = $signup_method;

    return $this;
  }

  /**
   * @return string
   */
  public function getCardToken(): string {
    return $this->card_token;
  }

  /**
   * @param string $card_token
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setCardToken(string $card_token = ''): self {
    $this->card_token = $card_token;

    return $this;
  }

  /**
   * @return bool
   */
  public function isNoTrial(): bool {
    return $this->no_trial;
  }

  /**
   * @param bool $no_trial
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setNoTrial(bool $no_trial): self {
    $this->no_trial = $no_trial;

    return $this;
  }

  /**
   * @return bool
   */
  public function isTest(): bool {
    return $this->test;
  }

  /**
   * @param bool $test
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setTest(bool $test): self {
    $this->test = $test;

    return $this;
  }

  /**
   * @return bool
   */
  public function isGenerateHandle(): bool {
    return $this->generate_handle;
  }

  /**
   * @param bool $generate_handle
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setGenerateHandle(bool $generate_handle): self {
    $this->generate_handle = $generate_handle;

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
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setHandle(string $handle): self {
    $this->handle = $handle;

    return $this;
  }

  /**
   * @return array
   */
  public function getAddOns(): array {
    return $this->add_ons;
  }

  /**
   * @param array $add_ons
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setAddOns(array $add_ons): self {
    $this->add_ons = $add_ons;

    return $this;
  }

  /**
   * @return \stdClass
   */
  public function getHosted_page_links(): \stdClass {
    return $this->hosted_page_links;
  }

  /**
   * @param \stdClass $hosted_page_links
   *
   * @return \Drupal\commerce_payment_reepay\Model\ReepaySubscription
   */
  public function setHosted_page_links(\stdClass $hosted_page_links): self {
    $this->hosted_page_links = $hosted_page_links;

    return $this;
  }

}
