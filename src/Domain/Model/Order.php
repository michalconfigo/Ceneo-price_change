<?php


namespace Ceneo\Domain\Model;


class Order {

	private $billingAddress = [];
	private $shippingAddress = [];
	private $shippingCost = [];
	private $products = [];
	private $id;

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId( string $id ): void {
		$this->id = $id;
	}

	/**
	 * @return array
	 */
	public function getBillingAddress(): array {
		return $this->billingAddress;
	}

	/**
	 * @param array $billingAddress
	 */
	public function setBillingAddress( array $billingAddress ): void {
		$this->billingAddress = $billingAddress;
	}

	/**
	 * @return array
	 */
	public function getShippingAddress(): array {
		return $this->shippingAddress;
	}

	/**
	 * @param array $shippingAddress
	 */
	public function setShippingAddress( array $shippingAddress ): void {
		$this->shippingAddress = $shippingAddress;
	}

	/**
	 * @return array
	 */
	public function getProducts(): array {
		return $this->products;
	}

	/**
	 * @param array $products
	 */
	public function setProducts( array $products ): void {
		$this->products = $products;
	}

	/**
	 * @return array
	 */
	public function getShippingCost(): array {
		return $this->shippingCost;
	}

	/**
	 * @param array $shippingCost
	 */
	public function setShippingCost( array $shippingCost ): void {
		$this->shippingCost = $shippingCost;
	}

}
