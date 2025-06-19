<?php

namespace Ceneo\Domain\Model;

class AttributeMapping {

	private $woocommerceName;
	private $woocommerceCategory;
	private $ceneoName;

	/**
	 * @return mixed
	 */
	public function getWoocommerceName() {
		return $this->woocommerceName;
	}

	/**
	 * @param mixed $woocommerceName
	 */
	public function setWoocommerceName( $woocommerceName ): void {
		$this->woocommerceName = $woocommerceName;
	}

	/**
	 * @return mixed
	 */
	public function getWoocommerceCategory() {
		return $this->woocommerceCategory;
	}

	/**
	 * @param mixed $woocommerceCategory
	 */
	public function setWoocommerceCategory( $woocommerceCategory ): void {
		$this->woocommerceCategory = $woocommerceCategory;
	}

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
}
