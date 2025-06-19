<?php

namespace Ceneo\Domain\Service;

use Ceneo\Domain\Exception\OrderAlreadyExistsException;
use Ceneo\Domain\Exception\ProductDoesNotExistException;
use Ceneo\Domain\Model\Order;

class WoocommerceOrderService {
	public function createOrder(Order $ceneoOrder): int {

        $pf = new \WC_Product_Factory();

		//Check if order already exists
		$args = array(
			'limit' => -1,
			'status' => array('wc-delivered'),
			'ceneo_id' => $ceneoOrder->getId(),
		);
		if(!empty(wc_get_orders($args))) { throw new OrderAlreadyExistsException(); }

        //Check if all products exist before creating an order
        foreach($ceneoOrder->getProducts() as $product) {
            if(!$pf->get_product($product['id'])) {
                throw new ProductDoesNotExistException();
            }
        }

        //If products exist, create an order
        $order = wc_create_order();

        //And populate with products
		foreach($ceneoOrder->getProducts() as $product) {
			$woocommerceProduct = $pf->get_product($product['id']);
			//$woocommerceProduct->set_price($product['price']); - mozliwe ze niepotrzebne
			$order->add_product($woocommerceProduct, $product['count'] );
		}

		$order->set_address($ceneoOrder->getBillingAddress(), 'billing' );
		$order->set_address($ceneoOrder->getShippingAddress(), 'shipping');

		$shipping_tax = array();
		$shipping_rate = new \WC_Shipping_Rate( '', $ceneoOrder->getShippingCost()['method'], $ceneoOrder->getShippingCost()['cost'], $shipping_tax, 'custom_shipping_method');

		$item = new \WC_Order_Item_Shipping();
		$item->set_shipping_rate($shipping_rate);
		$order->add_item($item);

		$order->calculate_totals();
		$order->update_status('processing', 'Zamówienie złożone i opłacone za pośrednictwem serwisu Ceneo.', TRUE);
		$order->update_meta_data('source', 'ceneo');
		$order->update_meta_data('ceneo_id', $ceneoOrder->getId());

        if(isset($ceneoOrder->getBillingAddress()['vat_id'])) {
            $order->update_meta_data('vat_id', $ceneoOrder->getBillingAddress()['vat_id']);
        }
		return $order->save();
	}
}
