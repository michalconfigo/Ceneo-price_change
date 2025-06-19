<?php

namespace Ceneo\Domain\Model;

class Mapping {
	private $woocommerceId;
	private $ceneoId;
	private $ceneoName;

	/**
	 * @return mixed
	 */
	public function getCeneoName() {
		return $this->ceneoName;
	}

	/**
	 * @param mixed $ceneoName
	 */
	public function setCeneoName( $ceneoName ): void {
		$this->ceneoName = $ceneoName;
	}

	/**
	 * @return mixed
	 */
	public function getWoocommerceId() {
		return $this->woocommerceId;
	}

	/**
	 * @param mixed $woocommerceId
	 */
	public function setWoocommerceId( $woocommerceId ): void {
		$this->woocommerceId = $woocommerceId;
	}

	/**
	 * @return mixed
	 */
	public function getCeneoId() {
		return $this->ceneoId;
	}

	/**
	 * @param mixed $ceneoId
	 */
	public function setCeneoId( $ceneoId ): void {
		$this->ceneoId = $ceneoId;
	}
}
