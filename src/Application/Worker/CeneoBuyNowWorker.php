<?php


namespace Ceneo\Application\Worker;


use Ceneo\Application\Helper\LoggingHelper;
use Ceneo\Domain\Exception\OrderAlreadyExistsException;
use Ceneo\Domain\Exception\ProductDoesNotExistException;
use Ceneo\Domain\Model\Order;
use Ceneo\Domain\Service\BuyNowApiService;
use Ceneo\Domain\Service\WoocommerceOrderService;

class CeneoBuyNowWorker {

    private $loggingHelper;

	public function __construct() {
        $this->loggingHelper = new LoggingHelper();
	}

	public function getNewOrdersFromCeneo(): void {
		$buyNowService = new BuyNowApiService();
		$woocommerceOrderService = new WoocommerceOrderService();
		$orders = $buyNowService->getNewOrders();
		if(!empty($orders)) {
			/** @var Order $order */
			foreach ( $orders as $order ) {
				try {
					$orderId = $woocommerceOrderService->createOrder( $order );
					$buyNowService->setOrderShopId( $order->getId(), $orderId );
				} catch ( ProductDoesNotExistException $exception ) {
					$this->loggingHelper->log("Products from order " . esc_html($order->getId()) . " not found.");
				} catch ( OrderAlreadyExistsException $exception) {
                    $this->loggingHelper->log("Order " . esc_html($order->getId()) . " already exists.");
                } catch (\Exception $exception) {
                    $this->loggingHelper->log("Not known exception. Skipping.");
                }
			}
		}
	}
}
