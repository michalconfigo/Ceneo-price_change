<?php

namespace Ceneo\Domain\Validator;

use Ceneo\Domain\Model\FeedGenerationRuntimeConfiguration;

abstract class Validator {

	/**
	 * @var FeedGenerationRuntimeConfiguration $runtimeConfiguration
	 */
	protected $runtimeConfiguration;

	public function __construct($runtimeConfiguration) {
		$this->runtimeConfiguration = $runtimeConfiguration;
	}

	public function validate(\WC_Product $product): bool {
		return true;
	}
}
