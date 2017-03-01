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
      'username' => $this->key,
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
    $response = $this->client->post($url, [
      'json' => $data,
      'headers' => $this->getHeaders(),
    ]);

    $responseBody = json_decode($response->getBody());

    return $responseBody;
  }

  /**
   * Get the password hash for a user.
   *
   * @param string $username
   *   The username to search for.
   *
   * @return mixed
   *   The password hash or FALSE.
   */
  public function createSubscription($data) {
    return $this->postRequest('subscription', $data);
  }

}
