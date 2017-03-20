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

  private $key;
  private $baseUrl = 'https://api.reepay.com/v1/';
  private $client;

  /**
   * CrmApi constructor.
   */
  public function __construct($private_key) {
    $this->key = $private_key;

    $this->client = new Client([
      'base_uri' => $this->baseUrl,
      'auth' => [$this->key, ''],
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
   * @param CrmApiItemInterface $item
   *   The data to POST to the CRM for creation.
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

      $responseBody = $response->getBody();
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

  public function createInvoice($invoice, $subscriptionId) {
    return $this->postRequest('subscription/' . $subscriptionId . '/invoice', $invoice);
  }

}
