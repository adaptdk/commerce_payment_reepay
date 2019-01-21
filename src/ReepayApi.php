<?php

namespace Drupal\commerce_payment_reepay;

use Drupal\commerce_order\Entity\Order;
use GuzzleHttp\Client;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\RequestException;

/**
 * Class CrmApi
 *
 * @package Drupal\interflora_crm
 */
class ReepayApi {

  private $baseUrl = 'https://api.reepay.com/v1/';
  private $client;

  /**
   * CrmApi constructor.
   *
   * @param string $privateKey
   *   The Reepay private key.
   */
  public function __construct($privateKey = '') {
    if ($privateKey) {
      $this->setupClient($privateKey);
    }
  }

  /**
   * Initiate client.
   *
   * @param string $privateKey
   *   The Reepay pricate key.
   */
  public function setupClient($privateKey): void {
    $this->client = new Client([
      'base_uri' => $this->baseUrl,
      'auth' => [$privateKey, ''],
    ]);
  }

  /**
   * Get the headers for a request.
   *
   * @return array
   *   An array with relevant header information.
   */
  protected function getHeaders() {
    return [
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
    ];
  }

  /**
   * Perform a POST request to the CRM.
   *
   * @param string $url
   *   The POST url to call.
   * @param string $data
   *   The data to POST to Reepay.
   *
   * @return mixed
   *   The server response.
   */
  protected function postRequest($url, $data) {
    try {
      $response = $this->client->post($url, [
        'json' => $data,
        'headers' => $this->getHeaders(),
      ]);

      $responseBody = json_decode($response->getBody());
    }
    catch (\Exception $exception) {
      $responseBody = [
        'code' => $exception->getCode(),
        'message' => json_decode($exception->getResponse()->getBody()->getContents()),
      ];
    }
    return $responseBody;
  }

  /**
   * Perform a POST request to the CRM.
   *
   * @param string $url
   *   The POST url to call.
   * @param string $data
   *   The data to POST to Reepay.
   *
   * @return mixed
   *   The server response.
   */
  protected function putRequest($url, $data) {
    try {
      $response = $this->client->put($url, [
        'json' => $data,
        'headers' => $this->getHeaders(),
      ]);

      $responseBody = json_decode($response->getBody());
    }
    catch (\Exception $exception) {
      $responseBody = [
        'code' => $exception->getCode(),
        'message' => json_decode($exception->getResponse()->getBody()->getContents()),
      ];
    }
    return $responseBody;
  }

  /**
   * Perform a GET request to Reepay.
   *
   * @param string $url
   *   The GET url to call.
   *
   * @return mixed
   *   The server response.
   */
  protected function getRequest($url, $options = []) {
    $options = array_merge($options, $this->getHeaders());
    try {
      $response = $this->client->get($url, $options);
      $responseBody = json_decode($response->getBody());
    }
    catch (RequestException $exception) {
      $responseBody = json_decode($exception->getResponse()->getBody()->getContents());
    }
    return $responseBody;
  }

  /**
   * @param bool $only_active
   * @return mixed
   */
  public function getListOfPlans($only_active = TRUE) {
    return $this->getRequest('plan', [
      'query' => [
        'only_active' => $only_active,
      ]
    ]);
  }

  /**
   * Create a new customer.
   *
   * @param string $data
   *   The customer data.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function createCustomer($customer) {
    return $this->postRequest('customer', $customer);
  }

  /**
   * Create a new subscription.
   *
   * @param string $data
   *   The subscription data.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function createSubscription($data) {
    return $this->postRequest('subscription', $data);
  }

  /**
   * Create a new invoice.
   *
   * @param string $data
   *   The invoice data.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function createInvoice($invoice, $subscriptionId) {
    return $this->postRequest('subscription/' . $subscriptionId . '/invoice', $invoice);
  }

  /**
   * Create a new plan.
   *
   * @param string $data
   *   The plan data.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function createPlan($data) {
    return $this->postRequest('plan', $data);
  }

  /**
   * Create a new addon.
   *
   * @param string $addOnData
   *   The addon data.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function createAddOn($addOnData) {
    return $this->postRequest('add_on', $addOnData);
  }

  /**
   * Load an invoice. Old version of getInvoice().
   *
   * @param string $invoice_id
   *   The invoice id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function loadInvoice($invoice_id) {
    return $this->getInvoice($invoice_id);
  }

  /**
   * Get an invoice.
   *
   * @param string $invoice_id
   *   The invoice id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function getInvoice($invoice_id) {
    return $this->getRequest('invoice/' . $invoice_id);
  }

  /**
   * Load a plan.
   *
   * @param string $plan_id
   *   The plan id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function getPlan($plan_id) {
    return $this->getRequest('plan/' . $plan_id);
  }

  /**
   * Load a subscription.
   *
   * @param string $subscription_id
   *   The subscription id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function getSubscription($subscription_id) {
    return $this->getRequest('subscription/' . $subscription_id);
  }

  /**
   * Load an addon.
   *
   * @param string $addOn
   *   The addon id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function getAddOn($addOn) {
    return $this->getRequest('add_on/' . $addOn);
  }

  /**
   * Load a subscription.
   *
   * @param string $subscription_id
   *   The subscription id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function getWebHook($id) {
    return $this->getRequest('webhook/' . $id);
  }

  /**
   * Cancel an invoice.
   *
   * @param string $invoice_id
   *   The invoice id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function cancelInvoice($invoice_id) {
    return $this->postRequest(sprintf('invoice/%s/cancel', $invoice_id), '');
  }

  /**
   * Cancel a subscription.
   *
   * @param string $subscription_id
   *   The subscription id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function cancelSubscription($subscription_id) {
    return $this->postRequest(sprintf('subscription/%s/cancel', $subscription_id), '');
  }

  /**
   * Cancel a subscription.
   *
   * @param string $subscription_id
   *   The subscription id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function cancelPlan($plan_id) {
    return $this->postRequest(sprintf('plan/%s/cancel', $plan_id), '');
  }

  /**
   * Delete an addon.
   *
   * @param string $addOnId
   *   The addon id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function updateAddOn($addOnId, $data) {
    return $this->putRequest(sprintf('add_on/%s', $addOnId), $data);
  }

  /**
   * Delete an addon.
   *
   * @param string $addOnId
   *   The addon id.
   *
   * @return mixed
   *   The response object or FALSE.
   */
  public function deleteAddOn($addOnId) {
    return $this->postRequest(sprintf('add_on/%s', $addOnId), '');
  }

}
