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
 *     "offsite-payment" =
 *   "Drupal\commerce_payment_reepay\PluginForm\OffsiteRedirect\ReepayOffsiteForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "amex", "dinersclub", "discover", "jcb", "maestro", "mastercard",
 *   "visa",
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
      '#required' => TRUE,
    ];
    $form['private_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Private key'),
      '#default_value' => isset($config['private_key']) ? $config['private_key'] : '',
      '#required' => TRUE,
    ];
    $form['webhook_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Webhook key'),
      '#default_value' => isset($config['webhook_key']) ? $config['webhook_key'] : '',
    ];
    return $form;
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
      if (isset($values['webhook_key'])) {
        $this->configuration['webhook_key'] = $values['webhook_key'];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    $token = $request->request->get('reepay-token');
    $order->set('card_token', $token);
    $order->save();
    $this->handlePayment($order, $token);
  }

  /**
   * {@inheritdoc}
   */
  public function handlePayment(OrderInterface $order, $token) {
    // @todo Add examples of request validation.
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $payment = $payment_storage->create([
      'state' => 'authorization',
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'test' => $this->getMode() == 'test',
      'remote_id' => $token,
      'remote_state' => 'initialize',
      'authorized' => REQUEST_TIME,
    ]);
    $payment->save();
    drupal_set_message('Payment was processed');
  }

}
