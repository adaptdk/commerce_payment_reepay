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
    // Create customer / Update
    ...
    $create_customer = new \stdClass();
    $create_customer = new \stdClass();
    $create_customer->email = $order->getEmail();
    $create_customer->handle = $user->id();
    $result = $client->createCustomer($create_customer);

    $create_sub = new \stdClass();
    $create_sub->customer = $user->ud();
    $create_sub->plan = 'plan-d5740';
    $create_sub->signup_method = "card_token";
    $create_sub->card_token = $token;
    $create_sub->no_trial = "true";
    $create_sub->test = ($configuration['mode'] == 'test') ? TRUE : FALSE;
    $create_sub->generate_handle = true;
    $result = $client->createSubscription($create_sub);

    foreach ($shipments as $shipment_id) {
      $shipment = Shipment::load($shipment_id->target_id);
      $price = $shipment->getAmount()->getNumber();
      $total = $number_formatter->format($price);
      $total = str_replace(',', '', $total);
      $date = $shipment->field_shipment_delivery_date->first()->value;
      $dueDate = DrupalDateTime::createFromTimestamp(strtotime($date))->format('Y-m-d') . 'T00:00:00';
      $data = new \stdClass();
      $data->customer = new \stdClass();
      $data->handle = $shipment->uuid();
      $date->settle->due = $dueDate - 3days;
      //$data->plan = 'plan-d5740';
      $data->amount = $total;
      $data->order_lines = [];
      // Add delivery date for shipment to order text.
      foreach ($shipment->getItems() as $item) {
        $orderLine = new \stdClass();
        $orderLine->ordertext = $item->label();
        $orderLine->amount = $item->total()->getNumber();
        $orderLine->amount = $item->getQuantity();
        $data->order_lines[] = $orderLine;
      }
      $result = $client->createInvoice($data);
      // $sub_handle = $result->handle;
      // https://api.reepay.com/v1/subscription/{handle}/invoice
      \Drupal::logger('reepay')->notice(json_encode($result));
    }
    \Drupal::logger('reepay')->notice("submit");
    $payment = $this->entity;
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $payment_gateway_plugin->handlePayment($payment->getOrder(), $result);
  }

}