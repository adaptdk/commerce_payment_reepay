<?php

namespace Drupal\commerce_payment_reepay\PluginForm\OffsiteRedirect;

use CommerceGuys\Intl\Formatter\NumberFormatterInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
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
    $form['#attributes']['class'][] = 'reepay-payment-form';
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
    \Drupal::logger('reepay')->notice(json_encode($values['payment_process']['offsite_payment']));
    $payment = $this->entity;
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    // Get the configuration array.
    $configuration = $payment_gateway_plugin->getConfiguration();
    /** @var Order $order */
    $order = $payment->getOrder();
    $email = $order->getEmail();
    $client = new ReepayApi($configuration['private_key']);
    $number_formatter = \Drupal::service('commerce_price.number_formatter_factory')
      ->createInstance(NumberFormatterInterface::DECIMAL);
    $number_formatter->setMaximumFractionDigits(6);
    $number_formatter->setMinimumFractionDigits(2);
    $number_formatter->setGroupingUsed(FALSE);
    // Create customer / Update
    $create_customer = new \stdClass();
    $create_customer->email = $order->getEmail();
    if ($order->getCustomer()->id() != 0) {
      $create_customer->handle = $order->getCustomer()->uuid();
    }
    else {
      $create_customer->generate_handle = TRUE;
    }
    $result = $client->createCustomer($create_customer);
    if (!is_array($result) || $result['message']->code != 11) {
      $customerHandle = $result->handle;
      \Drupal::logger('reepay')->notice("Reepay customer created");
    }
    else {
      $customerHandle = $order->getCustomer()->uuid();
      // @todo Check for active subscription.
      \Drupal::logger('reepay')->notice("Reepay customer already exists");
    }
    $create_sub = new \stdClass();
    $create_sub->customer = $customerHandle;
    $create_sub->plan = 'plan-d5740';
    $create_sub->signup_method = "card_token";
    $create_sub->card_token = $token;
    $create_sub->no_trial = "true";
    $create_sub->test = ($configuration['mode'] == 'test') ? TRUE : FALSE;
    $create_sub->generate_handle = true;
    $plan = $client->createSubscription($create_sub);
    \Drupal::logger('reepay')->notice("Reepay subscription created");
    $shipments = $order->get('shipments');
    foreach ($shipments->referencedEntities() as $shipment) {
      $price = $shipment->getTotalDeclaredValue()->getNumber();
      $total = $number_formatter->format($price);
      $total = str_replace(',', '', $total);
      $date = $shipment->field_shipment_delivery_date->first()->value;
      $dueDate = DrupalDateTime::createFromTimestamp(strtotime('-3 days', strtotime($date)))->format('Y-m-d') . 'T00:00:00';
      $data = new \stdClass();
      $data->handle = $shipment->uuid();
      $data->due = $dueDate;
      //$data->amount = $total;
      $data->order_lines = [];
      // Add delivery date for shipment to order text.
      foreach ($shipment->getItems() as $item) {
        $orderItem = OrderItem::load($item->getOrderItemId());
        $orderLine = new \stdClass();
        $orderLine->ordertext = $item->getTitle();
        $orderLine->amount = round($orderItem->getUnitPrice()->getNumber(), 0) . '00';
        $orderLine->quantity = $item->getQuantity();
        $data->order_lines[] = $orderLine;
      }
      $result = $client->createInvoice($data, $plan->handle);
      \Drupal::logger('reepay')->notice("Reepay Invoice created");
    }
    \Drupal::logger('reepay')->notice("Reepay subscription created");
    $payment = $this->entity;
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $payment_gateway_plugin->handlePayment($payment->getOrder(), $result);
  }

}
