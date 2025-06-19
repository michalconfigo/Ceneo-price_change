<?php

namespace Ceneo\Domain\Model;

class FeedGenerationRuntimeConfiguration {

	private $excludeNotAvailable;
	private $mergeVariants;
	private $excludeMinPrice;
	private $excludeMaxPrice;
	private $priceRange;
	private $active;
	private $amountOfProducts;
	private $currentOffset;

	/**
	 * @return mixed
	 */
	public function getExcludeMinPrice() {
		return $this->excludeMinPrice;
	}

	/**
	 * @param mixed $excludeMinPrice
	 */
	public function setExcludeMinPrice( $excludeMinPrice ): void {
		$this->excludeMinPrice = $excludeMinPrice;
	}

	/**
	 * @return mixed
	 */
	public function getExcludeMaxPrice() {
		return $this->excludeMaxPrice;
	}

	/**
	 * @param mixed $excludeMaxPrice
	 */
	public function setExcludeMaxPrice( $excludeMaxPrice ): void {
		$this->excludeMaxPrice = $excludeMaxPrice;
	}

	/**
	 * @return mixed
	 */
	public function getExcludeNotAvailable() {
		return $this->excludeNotAvailable;
	}

	/**
	 * @param mixed $excludeNotAvailable
	 */
	public function setExcludeNotAvailable( $excludeNotAvailable ): void {
		$this->excludeNotAvailable = $excludeNotAvailable;
	}

	/**
	 * @return mixed
	 */
	public function getMergeVariants() {
		return $this->mergeVariants;
	}

	/**
	 * @param mixed $mergeVariants
	 */
	public function setMergeVariants( $mergeVariants ): void {
		$this->mergeVariants = $mergeVariants;
	}

	/**
	 * @return mixed
	 */
	public function getPriceRange() {
		return $this->priceRange;
	}

	/**
	 * @param mixed $priceRange
	 */
	public function setPriceRange( $priceRange ): void {
		$this->priceRange = $priceRange;
	}

	/**
	 * @return mixed
	 */
	public function getActive() {
		return $this->active;
	}

	/**
	 * @param mixed $active
	 */
	public function setActive( $active ): void {
		$this->active = $active;
	}

	/**
	 * @return mixed
	 */
	public function getAmountOfProducts() {
		return $this->amountOfProducts;
	}

	/**
	 * @param mixed $amountOfProducts
	 */
	public function setAmountOfProducts( $amountOfProducts ): void {
		$this->amountOfProducts = $amountOfProducts;
	}

	/**
	 * @return mixed
	 */
	public function getCurrentOffset() {
		return $this->currentOffset;
	}

	/**
	 * @param mixed $currentOffset
	 */
	public function setCurrentOffset( $currentOffset ): void {
		$this->currentOffset = $currentOffset;
	}
}
