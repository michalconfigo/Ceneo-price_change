<?php

namespace Ceneo\Infrastructure\Api;

class BuyNowApi {

	private $http;

	function __construct()
	{
		$this->http = new \WP_Http();
        $this->token = '';
	}

	public function getOAuthToken(string $apiKey): string {
        if($this->token === '') {
            $headers = [
                'Authorization' => 'Basic ' . $apiKey
            ];
            $response = $this->http->get("https://developers.ceneo.pl/AuthorizationService.svc/GetToken?grantType='client_credentials'", ['headers' => $headers]);
            if ($response instanceof \WP_Error || $response['response']['code'] != 204) {
                return '';
            }
            $this->token = $response['headers']['access_token'];
        }
        echo $this->token;
        return $this->token;
	}

	public function getOrdersSinceDate(int $date, $accessToken) {
		$headers = [
			'Authorization' => 'Bearer ' . $accessToken
		];
		$date = date('Y-m-d\TH:i:s', $date);
		$response = $this->http->get(
			'https://developers.ceneo.pl/BasketService.svc/Orders?$filter=OrderStateId eq 30 and CreatedDate gt DateTime\'' . $date . '\'&$format=json',
			['headers' => $headers]
		);
		if($response instanceof \WP_Error || $response['response']['code'] != 200) {
			return null;
		}
		$stdObject = json_decode($response['body']);
		return $stdObject->d->results;
	}

	public function setOrderWoocommerceId($order, $id) {

	}

	public function getOrderItems(string $guid, string $accessToken) {

		$headers = [
			'Authorization' => 'Bearer ' . $accessToken
		];

		$response = $this->http->get(
			'https://developers.ceneo.pl/BasketService.svc/Orders(guid\'' . $guid . '\')/OrderItems?$format=json',
			['headers' => $headers]
		);
		if($response instanceof \WP_Error || $response['response']['code'] != 200) {
			return null;
		}
		$stdObject = json_decode($response['body']);
		return $stdObject->d->results;
	}

	public function getOrderShippingData(string $guid, string $accessToken) {
		$headers = [
			'Authorization' => 'Bearer ' . $accessToken
		];

		$response = $this->http->get(
			'https://developers.ceneo.pl/BasketService.svc/Orders(guid\'' . $guid . '\')/ShippingData?$format=json',
			['headers' => $headers]
		);
		if($response instanceof \WP_Error || $response['response']['code'] != 200) {
			return null;
		}
		$stdObject = json_decode($response['body']);
		return end($stdObject->d->results);
	}

	public function getOrderInvoiceData(string $guid, string $accessToken) {
		$headers = [
			'Authorization' => 'Bearer ' . $accessToken
		];

		$response = $this->http->get(
			'https://developers.ceneo.pl/BasketService.svc/Orders(guid\'' . $guid . '\')/InvoiceData?$format=json',
			['headers' => $headers]
		);
		if($response instanceof \WP_Error || $response['response']['code'] != 200) {
			return null;
		}
		$stdObject = json_decode($response['body']);
		return end($stdObject->d->results);

	}

	public function setOrderShopId(string $guid, int $shopId, string $accessToken): bool {
		$headers = [
			'Authorization' => 'Bearer ' . $accessToken
		];

		$response = $this->http->get(
			'https://developers.ceneo.pl/BasketService.svc/SetOrders?orders=\'[{"OrderId":"' . $guid . '","ShopOrderId":"' . $shopId . '"}]\'',
			['headers' => $headers]
		);

		if($response instanceof \WP_Error || $response['response']['code'] != 200) {
			return false;
		}
		return true;

	}
}
