<?php

namespace Drupal\commerce_payment_reepay\PluginForm\OffsiteRedirect;

use CommerceGuys\Intl\Formatter\NumberFormatterInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\commerce_payment_reepay\ReepayApi;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;

class ReepayOffsiteForm extends BasePaymentOffsiteForm {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    // Get the configuration array.
    $configuration = $payment_gateway_plugin->getConfiguration();
    $order = $payment->getOrder();
//    $form_url = Url::fromRoute('commerce_payment_reepay.reepayform', ['commerce_order' => $order->id()], ['absolute' => TRUE])->toString();
//    $form['iframe_test'] = [
//      '#type' => 'inline_template',
//      '#template' => '<iframe id="reepay-payment" width="100%" height="300px" src="{{ url }}"></iframe>',
//      '#context' => ['url' => $form_url]
//    ];
    $form['#attached']['library'][] = 'commerce_payment_reepay/reepay';
    $form['#attached']['drupalSettings'] = [
      'reepay' => [
        'reepayApi' => $configuration['api_key'],
      ],
    ];
    $form['#attached']['library'][] = 'commerce_payment_reepay/handling';
    $form['number'] = [
      '#type' => 'textfield',
      '#title' => t('CreditCard number'),
      '#attributes' => [
        'data-reepay' => 'number'
      ]
    ];
    $form['month'] = [
      '#type' => 'textfield',
      '#title' => t('Month'),
      '#attributes' => [
        'data-reepay' => 'month'
      ]
    ];
    $form['year'] = [
      '#type' => 'textfield',
      '#title' => t('Year'),
      '#attributes' => [
        'data-reepay' => 'year'
      ]
    ];
    $form['cvv'] = [
      '#type' => 'textfield',
      '#title' => t('CVV'),
      '#attributes' => [
        'data-reepay' => 'cvv'
      ]
    ];
    $form['reepay-token'] = [
      '#type' => 'hidden',
      '#value' => '',
      '#attributes' => [
        'data-reepay' => 'token'
      ]
    ];
    $form['submit'] = [
      '#type' => 'button',
      '#value' => t('Submit'),
    ];
    return $this->buildRedirectForm($form, $form_state, '', []);
  }

  protected function buildRedirectForm(array $form, FormStateInterface $form_state, $redirect_url, array $data, $redirect_method = self::REDIRECT_GET) {
    return $form;
  }

  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $token = $values['payment_process']['offsite_payment']['reepay-token'];
    $payment = $this->entity;
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    // Get the configuration array.
    $configuration = $payment_gateway_plugin->getConfiguration();
    /** @var Order $order */
    $order = $payment->getOrder();
    $email = $order->getEmail();
    $shipments = $order->get('shipments');
    $client = new ReepayApi($configuration['private_key']);
    $number_formatter = \Drupal::service('commerce_price.number_formatter_factory')
      ->createInstance(NumberFormatterInterface::DECIMAL);
    $number_formatter->setMaximumFractionDigits(6);
    $number_formatter->setMinimumFractionDigits(2);
    $number_formatter->setGroupingUsed(FALSE);
    foreach ($shipments as $shipment_id) {
      $shipment = Shipment::load($shipment_id->target_id);
      $price = $shipment->getAmount()->getNumber();
      $total = $number_formatter->format($price);

      $date = $shipment->field_shipment_delivery_date->first()->value;
      $dueDate = DrupalDateTime::createFromTimestamp(strtotime($date))->format('Y-m-d') . 'T00:00:00';
      $data = new \stdClass();
      $data->create_customer = new \stdClass();
      //$data->create_customer->handle = "customer006";
      $data->create_customer->email = $order->getEmail();
      $data->plan = 'plan-d5740';
      $data->amount = $total;
      $data->test = $configuration['mode'];
      //$data->handle = "sub0002";
      $data->generateHandle = "true";
      $data->signup_method = "card_token";
      $data->plan_version = "1";
      $data->start_date = $dueDate;
      $data->end_date = $dueDate;
      $data->grace_duration = "172800";
      $data->card_token = $token;
      $data->no_trial = "true";
      $result = $client->createSubscription($data);
      \Drupal::logger('reepay')->notice(json_encode($result));
    }
    \Drupal::logger('reepay')->notice("submit");
    $payment = $this->entity;
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $payment_gateway_plugin->handlePayment($payment->getOrder(), $result);
  }

}