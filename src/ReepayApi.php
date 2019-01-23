<?php

namespace Drupal\commerce_payment_reepay;

use Drupal\commerce_order\Entity\Order;
use Drupal\serialization\Encoder\XmlEncoder;
use GuzzleHttp\Client;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class CrmApi
 *
 * @package Drupal\interflora_crm
 */
class ReepayApi {

  /**
   * Base reepay url.
   *
   * @var string
   */
  private $baseUrl = 'https://api.reepay.com/v1/';

  /**
   * The guzzle client.
   *
   * @var Client
   */
  private $client;

  private $serializer;

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
    $encoders = ['json' => new JsonDecode()];
    $normalizers = [new GetSetMethodNormalizer()];
    $this->serializer = new Serializer($normalizers, $encoders);
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
   * @param string $class
   *   Name of the class to deserialize to.
   *
   * @return string|array
   *   The server response.
   */
  protected function postRequest($url, $data = '', $class = '') {
    try {
      $response = $this->client->post($url, [
        'json' => $data,
        'headers' => $this->getHeaders(),
      ]);

      $responseBody = $this->handleResponse($response->getBody()->getContents(), $class);
    }
    catch (RequestException $exception) {
      $responseBody = [
        'code' => $exception->getCode(),
        'message' => $this->handleException($exception),
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
   * @param string $class
   *   Name of the class to deserialize to.
   *
   * @return array
   *   The server response.
   */
  protected function putRequest($url, $data, $class): array {
    try {
      $response = $this->client->put($url, [
        'json' => $data,
        'headers' => $this->getHeaders(),
      ]);

      $responseBody = $this->handleResponse($response->getBody()->getContents(), $class);
    }
    catch (RequestException $exception) {
      $responseBody = [
        'code' => $exception->getCode(),
        'message' => $this->handleException($exception),
      ];
    }
    return $responseBody;
  }

  /**
   * Perform a GET request to Reepay.
   *
   * @param string $url
   *   The GET url to call.
   * @param string $class
   *   Name of the class to deserialize to.
   * @param array $options
   *   Extra options for the request.
   *
   * @return mixed
   *   The server response.
   */
  protected function getRequest($url, $class, $options = []) {
    $options = array_merge($options, $this->getHeaders());
    try {
      $response = $this->client->get($url, $options);
      $responseBody = $this->handleResponse($response->getBody()->getContents(), $class);
    }
    catch (RequestException $exception) {
      $responseBody = $this->handleException($exception);
    }
    return $responseBody;
  }

  /**
   * @param string $body
   *   The content to deserialize.
   * @param string $class
   *   The content class name.
   *
   * @return object
   *   A class or a json object.
   */
  protected function handleResponse($body, $class) {
    $className = 'Drupal\\commerce_payment_reepay\\Model\\' . $class;
    if ($class !== '' && class_exists($className)) {
      $content = $this->serializer->deserialize($body, $className, 'json');
    }
    else {
      $content = json_decode($body);
    }
    return $content;
  }

  /**
   * @param \GuzzleHttp\Exception\RequestException $exception
   *
   * @return mixed
   */
  protected function handleException(RequestException $exception) {
    return json_decode(
      $exception
        ->getResponse()
        ->getBody()
        ->getContents()
    );
  }

  /**
   * @param bool $only_active
   * @return mixed
   */
  public function getListOfPlans($only_active = TRUE) {
    return $this->getRequest('plan', 'PlanList', [
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
    return $this->postRequest('customer', $customer, 'ReepayCustomer');
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
    return $this->postRequest('subscription', $data, 'ReepaySubscription');
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
    $url = sprintf('subscription/%s/invoice', $subscriptionId);
    return $this->postRequest($url, $invoice, 'ReepayInvoice');
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
    return $this->postRequest('plan', $data, 'ReepayPlan');
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
    return $this->postRequest('add_on', $addOnData, 'ReepayAddOn');
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
    return $this->getRequest(sprintf('invoice/%s', $invoice_id), 'ReepayInvoice');
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
    return $this->getRequest(sprintf('plan/%s', $plan_id), 'ReepayPlan');
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
    return $this->getRequest(sprintf('subscription/%s', $subscription_id), 'ReepaySubscription');
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
    return $this->getRequest(sprintf('add_on/%s', $addOn), 'ReepayAddOn');
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
    $url = sprintf('webhook/%s/request', $id);
    \Drupal::logger('Subscription')->notice($url);
    return $this->getRequest($url, 'ReepayWebHook');
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
    return $this->postRequest(sprintf('invoice/%s/cancel', $invoice_id));
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
    return $this->postRequest(sprintf('subscription/%s/cancel', $subscription_id));
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
    return $this->postRequest(sprintf('plan/%s/cancel', $plan_id));
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
    return $this->putRequest(sprintf('add_on/%s', $addOnId), $data, 'ReepayAddOn');
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
    return $this->postRequest(sprintf('add_on/%s', $addOnId));
  }

}
