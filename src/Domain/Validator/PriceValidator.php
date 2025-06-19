<?php

namespace Ceneo\Domain\Validator;

class PriceValidator extends Validator {
	public function validate( \WC_Product $product ): bool {
		if ( $product instanceof \WC_Product_Variable ) {
            if ( $product->get_variation_price() === '') return false;
			if ( $this->runtimeConfiguration->getExcludeMinPrice() && ( (int) $product->get_variation_price( 'max' ) * 100 ) < $this->runtimeConfiguration->getPriceRange()['min'] * 1 ) {
				return false;
			}
			if ( $this->runtimeConfiguration->getExcludeMaxPrice() && ( (int) $product->get_variation_price( 'min' ) * 100 ) > $this->runtimeConfiguration->getPriceRange()['max'] * 1 ) {
				return false;
			}
		} else {
            if ( $product->get_price() === '') return false;
			if ( $this->runtimeConfiguration->getExcludeMinPrice() && ( (int) $product->get_price() * 100 ) < $this->runtimeConfiguration->getPriceRange()['min'] ||  $this->runtimeConfiguration->getExcludeMaxPrice() && ( (int) $product->get_price() * 100 ) > $this->runtimeConfiguration->getPriceRange()['max'] ) {
				return false;
			}
		}

		return true;
	}
}
