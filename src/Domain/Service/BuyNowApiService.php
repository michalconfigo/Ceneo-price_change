<?php


namespace Ceneo\Domain\Service;


use Ceneo\Application\Helper\LoggingHelper;
use Ceneo\Domain\Model\Order;
use Ceneo\Infrastructure\Api\BuyNowApi;

class BuyNowApiService {

	private $buyNowApi;
	private $optionService;
    private $loggingHelper;
	private $accessToken;

	public function __construct() {
		$this->buyNowApi = new BuyNowApi();
		$this->optionService = new OptionService();
        $this->loggingHelper = new LoggingHelper();
	}


	public function getNewOrders(): array {
        $this->accessToken = $this->buyNowApi->getOAuthToken($this->optionService->getApiKey());
        $lastOrdersSync = $this->optionService->getLastOrderSyncTime();

		$currentTime = time();
		$orders = $this->buyNowApi->getOrdersSinceDate($lastOrdersSync, $this->accessToken);
        $this->loggingHelper->log("Fetching orders from Ceneo from last sync at " . date('d-m-Y h:i:s A', $lastOrdersSync));

		if(!empty($orders)) {
			$cOrders = [];
			$iterator = [];
			while(!empty($orders)) {
				$order = array_pop($orders);

				$iterator[$order->Id] = isset($iterator[$order->Id]) ? ($iterator[$order->Id] + 1) : 0;

				$orderItems        = $this->buyNowApi->getOrderItems( $order->Id, $this->accessToken );
				$orderInvoiceData  = $this->buyNowApi->getOrderInvoiceData( $order->Id, $this->accessToken );
				$orderShippingData = $this->buyNowApi->getOrderShippingData( $order->Id, $this->accessToken );

				if($orderItems == null || $orderInvoiceData == null || $orderShippingData == null) { # If any request to Ceneo API failed
					if($iterator[$order->Id] < 5) { # And it didn't fail more than 4 times before
						array_unshift( $orders, $order ); # Add order to array for retry at the begining of it
                        $this->loggingHelper->log("Fetching order " . $order->DisplayedOrderId. " (" . $order->Id . ") failed with dependencies. Retrying.");
					} else {
                        $this->loggingHelper->log("Fetching order " . $order->DisplayedOrderId. " (" . $order->Id . ") failed with retries. Exiting.");
						exit(); # Break process and try another time. Potentially can run endlessly
					}
				} else {
                    $this->loggingHelper->log("Fetched order " . $order->DisplayedOrderId. " (" . $order->Id . ") with at: " . date('d-m-Y h:i:s A', $currentTime));
					$cOrder = new Order();
					$cOrder->setId( $order->Id );

                    $cOrder->setShippingAddress( [
                        'first_name' => $orderShippingData->ShippingFirstName,
                        'last_name'  => $orderShippingData->ShippingLastName,
                        'company'    => $orderShippingData->ShippingCompanyName,
                        'email'      => $orderShippingData->Email,
                        'phone'      => $orderShippingData->PhoneNumber,
                        'address_1'  => $orderShippingData->ShippingAddress,
                        'city'       => $orderShippingData->ShippingCity,
                        'state'      => $orderShippingData->ShippingRegion,
                        'postcode'   => $orderShippingData->ShippingPostCode,
                        'country'    => $orderShippingData->ShippingCountry
                    ] );

                    $cOrder->setShippingCost( [
                        'cost'   => $order->DeliveryCost,
                        'method' => $order->ShopDeliveryFormName
                    ] );

                    $cOrder->setBillingAddress( [
							'first_name' => isset($orderInvoiceData->InvoiceFirstName) ? $orderInvoiceData->InvoiceFirstName : $orderShippingData->ShippingFirstName,
							'last_name'  => isset($orderInvoiceData->InvoiceLastName) ? $orderInvoiceData->InvoiceLastName : $orderShippingData->ShippingLastName,
							'company'    => isset($orderInvoiceData->InvoiceCompanyName) ? $orderInvoiceData->InvoiceCompanyName : $orderShippingData->ShippingCompanyName,
							'email'      => $orderShippingData->Email,
							'phone'      => $orderShippingData->PhoneNumber,
							'address_1'  => isset($orderInvoiceData->InvoiceAddress) ? $orderInvoiceData->InvoiceAddress : $orderShippingData->ShippingAddress,
							'city'       => isset($orderInvoiceData->InvoiceCity) ? $orderInvoiceData->InvoiceCity : $orderShippingData->ShippingCity,
							'state'      => isset($orderInvoiceData->InvoiceRegion) ? $orderInvoiceData->InvoiceRegion : $orderShippingData->ShippingRegion,
							'postcode'   => isset($orderInvoiceData->InvoicePostCode) ? $orderInvoiceData->InvoicePostCode : $orderShippingData->ShippingPostCode,
							'country'    => isset($orderInvoiceData->InvoiceCountry) ? $orderInvoiceData->InvoiceCountry : $orderShippingData->ShippingCountry,
                            'vat_id'     => $orderInvoiceData->InvoiceNIP
						] );

					foreach ( $orderItems as $orderItem ) {
						$cOrderProducts   = $cOrder->getProducts();
						$cOrderProducts[] = [
							'id'    => $orderItem->ShopProductId,
							'price' => $orderItem->Price,
							'count' => $orderItem->Count
						];
						$cOrder->setProducts( $cOrderProducts );
					}
					$cOrders[] = $cOrder;
				}
			}
			$this->optionService->saveLastOrderSyncTime($currentTime);
			return $cOrders;
		}
        $this->loggingHelper->log("No orders to fetch");
		return [];
	}

	public function setOrderShopId($orderId, $shopId) {
        $this->accessToken = $this->buyNowApi->getOAuthToken($this->optionService->getApiKey());
		$this->buyNowApi->setOrderShopId($orderId, $shopId, $this->accessToken);
        $this->loggingHelper->log("Assigned ID " . $shopId . " to order " . $orderId);
	}

	public function validateToken(string $accessToken): bool {
		$oAuthToken = $this->buyNowApi->getOAuthToken($accessToken);
		if($oAuthToken === '') return false;
		return true;
	}
}
