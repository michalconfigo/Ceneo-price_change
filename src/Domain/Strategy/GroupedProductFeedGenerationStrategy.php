<?php


namespace Ceneo\Domain\Strategy;


use Ceneo\Domain\Model\Category;
use Ceneo\Domain\Model\WoocommerceCategoryWrapper;

class GroupedProductFeedGenerationStrategy extends FeedGenerationStrategy {
	public function generateProductXML( \WC_Product $product, array $wrappedCategoryPath ): string {
		return '';
	}
}
