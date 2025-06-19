<?php

namespace Ceneo\Domain\Validator;

class ExclusionValidator extends Validator {
	public function validate( \WC_Product $product ): bool {
		$excludeFromFeed = get_post_meta($product->get_id(), '_ceneo_exclude_from_sync');
		return !(is_array($excludeFromFeed) && !empty($excludeFromFeed) && $excludeFromFeed[0] === '1');
	}
}
