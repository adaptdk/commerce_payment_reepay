<?php

namespace Drupal\commerce_payment_reepay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\commerce_payment_reepay\ReepayApi;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the Off-site Redirect payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "reepay_offsite",
 *   label = "Reepay",
 *   display_label = "Reepay Offsite",
 *    forms = {
 *     "offsite-payment" = "Drupal\commerce_payment_reepay\PluginForm\OffsiteRedirect\ReepayOffsiteForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "amex", "dinersclub", "discover", "jcb", "maestro", "mastercard", "visa",
 *   },
 * )
 */
class ReepayOffsite extends OffsitePaymentGatewayBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'redirect_method' => 'post',
      'public_key' => '',
      'private_key' => '',
      'payment_plan' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['public_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Public key'),
      '#default_value' => isset($config['public_key']) ? $config['public_key'] : '',
    ];
    $form['private_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Private key'),
      '#default_value' => isset($config['private_key']) ? $config['private_key'] : '',
      '#ajax' => [
        'trigger' => 'change',
        'callback' => 'updatePlan',
      ],
    ];
    $values = $form_state->getValues();
    if (isset($config['private_key']) || isset($values['private_key'])) {
      dpm($form_state->getValues());
      $key = isset($values['private_key']) ? $values['private_key'] : $config['private_key'];
      $client = new ReepayApi($config['private_key']);
      $plans = $client->getListOfPlans();
      dpm($plans);
      $plan_options = [];
      if ($plans) {
        foreach ($plans as $plan) {
          $plan_options[$plan->handle] = $plan->name;
        }
        $form['payment_plan'] = [
          '#type' => 'select',
          '#title' => $this->t('Plan'),
          '#options' => $plan_options,
          '#default_value' => isset($config['payment_plan']) ? $config['payment_plan'] : '',
        ];
      }
    }
    return $form;
  }

  public function updatePlan(array $form, FormStateInterface $form_state) {
    return $form['payment_plan'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['public_key'] = $values['public_key'];
      $this->configuration['private_key'] = $values['private_key'];
      if (isset($values['payment_plan'])) {
        $this->configuration['payment_plan'] = $values['payment_plan'];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    // @todo Add examples of request validation.
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $payment = $payment_storage->create([
      'state' => 'authorization',
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'test' => $this->getMode() == 'test',
      'remote_id' => $request->query->get('txn_id'),
      'remote_state' => $request->query->get('payment_status'),
      'authorized' => REQUEST_TIME,
    ]);
    $payment->save();
    drupal_set_message('Payment was processed');
  }

  /**
   * {@inheritdoc}
   */
  public function handlePayment(OrderInterface $order, $transaction) {
    // @todo Add examples of request validation.
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $payment = $payment_storage->create([
      'state' => 'authorization',
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'test' => $this->getMode() == 'test',
      'remote_id' => $transaction->handle,
      'remote_state' => $transaction->state,
      'authorized' => REQUEST_TIME,
    ]);
    $payment->save();
    drupal_set_message('Payment was processed');
  }

}