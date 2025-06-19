<?php

namespace Ceneo\Domain\Model;

class WoocommerceCategoryWrapper {
	private $woocommerceCategory;
	private $woocommerceAttributes;
	private $mappedCeneoCategory;
	private $mappedCeneoAttributes;
	private $children;

	/**
	 * @return mixed
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * @param mixed $children
	 */
	public function setChildren( $children ): void {
		$this->children = $children;
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
    public function getWoocommerceAttributes() {
        return $this->woocommerceAttributes;
    }

    /**
     * @param mixed $woocommerceAttributes
     */
    public function setWoocommerceAttributes( $woocommerceAttributes ): void {
        $this->woocommerceAttributes = $woocommerceAttributes;
    }

    /**
	 * @return mixed
	 */
	public function getMappedCeneoCategory() {
		return $this->mappedCeneoCategory;
	}

	/**
	 * @param mixed $mappedCeneoCategory
	 */
	public function setMappedCeneoCategory( $mappedCeneoCategory ): void {
		$this->mappedCeneoCategory = $mappedCeneoCategory;
	}

	/**
	 * @return mixed
	 */
	public function getMappedCeneoAttributes() {
		return $this->mappedCeneoAttributes;
	}

	/**
	 * @param mixed $mappedCeneoAttributes
	 */
	public function setMappedCeneoAttributes( $mappedCeneoAttributes ): void {
		$this->mappedCeneoAttributes = $mappedCeneoAttributes;
	}
}
