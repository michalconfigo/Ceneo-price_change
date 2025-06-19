<?php

namespace Ceneo\Domain\Model;

class CurrentRunStats {
	private $processedProducts;
	private $notProcessedProducts;
	private $allProducts;

	/**
	 * @return mixed
	 */
	public function getAllProducts() {
		return $this->allProducts;
	}

	/**
	 * @param mixed $allProducts
	 */
	public function setAllProducts( $allProducts ): void {
		$this->allProducts = $allProducts;
	}

	/**
	 * @return mixed
	 */
	public function getProcessedProducts() {
		return $this->processedProducts;
	}

	/**
	 * @param mixed $processedProducts
	 */
	public function setProcessedProducts( $processedProducts ): void {
		$this->processedProducts = $processedProducts;
	}

	/**
	 * @return mixed
	 */
	public function getNotProcessedProducts() {
		return $this->notProcessedProducts;
	}

	/**
	 * @param mixed $notProcessedProducts
	 */
	public function setNotProcessedProducts( $notProcessedProducts ): void {
		$this->notProcessedProducts = $notProcessedProducts;
	}
}
