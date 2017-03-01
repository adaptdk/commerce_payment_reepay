<?php

namespace Drupal\commerce_payment_reepay\Form;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ReepayController.
 *
 * @package Drupal\commerce_payment_reepay\Controller
 */
class ReepayForm extends FormBase {

  protected $order;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reepay.payment.form';
  }

  /**
   * Paymentform.
   *
   * @return string
   *   Return Hello string.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $commerce_order = NULL) {
    $this->order = $commerce_order;
    $payment_gateway_plugin = PaymentGateway::load('reepay_js')->getPlugin();
    $configuration = $payment_gateway_plugin->getConfiguration();
    $form['#attached']['library'][] = 'commerce_payment_reepay/reepay';
    $form['#attached']['drupalSettings'] = [
      'reepay' => [
        'reepayApi' => $configuration['api_key'],
      ],
    ];
    $form['#title'] = $this->t('Reepay');
    $form['#attached']['library'][] = 'commerce_payment_reepay/handling';
    $form['number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CreditCard number'),
      '#attributes' => [
        'data-reepay' => 'number'
      ]
    ];
    $form['month'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Month'),
      '#attributes' => [
        'data-reepay' => 'month'
      ]
    ];
    $form['year'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Year'),
      '#attributes' => [
        'data-reepay' => 'year'
      ]
    ];
    $form['cvv'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CVV'),
      '#attributes' => [
        'data-reepay' => 'cvv'
      ]
    ];
    $form['reepay-token'] = [
      '#type' => 'hidden',
      '#value' => '',
    ];
    $form['submit'] = [
      '#type' => 'button',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate.
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // submit.
    $token = $form_state->getValue('reepay_token');
    dpm($token);
  }

}
