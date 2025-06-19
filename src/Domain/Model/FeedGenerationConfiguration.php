<?php

namespace Ceneo\Domain\Model;

class FeedGenerationConfiguration {
	private $excludeNotAvailable;
	private $mergeVariants;
	private $excludeMinPrice;
	private $excludeMaxPrice;
	private $priceRange;

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
}
