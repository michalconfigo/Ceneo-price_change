<?php

namespace Ceneo\Domain\Validator;

class AvailabilityValidator extends Validator {
	public function validate( \WC_Product $product ): bool {
		if ( !(get_post_status($product->get_id()) == 'publish') ||
             ( $this->runtimeConfiguration->getExcludeNotAvailable() && ! $product->is_in_stock() )
        ) {
            return false;
        }
		return true;
	}
}
