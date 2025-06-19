<?php


namespace Ceneo\Domain\Strategy;

use Ceneo\Domain\Model\WoocommerceCategoryWrapper;
use Ceneo\Domain\Service\OptionService;

class VariableProductFeedGenerationStrategy extends FeedGenerationStrategy {

	private $basketDisabled = false;
	private $mergeVariants = true;

	public function __construct($mergeVariants) {
		$this->mergeVariants = $mergeVariants;
	}

	public function generateProductXML( \WC_Product $product, array $wrappedCategoryPath ) : string {

		# Define basket state for all variants
		$basketMeta = get_post_meta($product->get_id(), '_ceneo_disable_buy_now');
		$this->basketDisabled = is_array($basketMeta) && !empty($basketMeta) && $basketMeta[0] === '1';
        $xml = '';

		if ($product instanceof \WC_Product_Variable) {
			$productVariations = [];

			if($this->mergeVariants) {
				foreach ( $product->get_children() as $childId ) {
					$productVariation = new \WC_Product_Variation( $childId );
                    if ($productVariation->is_in_stock()) {
                        $productVariations[$productVariation->get_price()][] = $productVariation;
                    }
				}
			} else {
				foreach ( $product->get_children() as $childId ) {
                    $productVariation = new \WC_Product_Variation( $childId );
                    if ($productVariation->is_in_stock()) {
                        $productVariations[][] = $productVariation;
                    }
				}
			}
			foreach($productVariations as $productVariationArray) {
				$xml .= $this->generateProductHeader(end($productVariationArray));

				$xml .= $this->generateProductCategory($wrappedCategoryPath);
				$xml .= $this->generateProductName(end($productVariationArray));

				$xml .= $this->generateProductImages(end($productVariationArray));


				$xml .= $this->generateProductAttributesFromProductArray($productVariationArray, end($wrappedCategoryPath));
				$xml .= $this->generateProductFooter();
			}
		}
        return $xml;
	}

	protected function generateProductHeader( \WC_Product $product ): string {
		if($product->managing_stock() && $product->get_stock_quantity() > 0) {
			$stock = $product->get_stock_quantity();
		} else {
			$stock = $product->is_in_stock() ? 99 : 0;
		}

        switch($product->get_availability()['class']) {
            case 'in-stock':
                $availability = '1';
                break;
            case 'available-on-backorder':
                $availability = '90';
                break;
            default:
                $availability = '0';
        }

		return "<o id=\"" . $product->get_id()
                . "\" url=\"" . htmlspecialchars($product->get_permalink())
                . "\" price=\"" . $product->get_price()
                . "\" avail=\"" . $availability
                . ($product->has_weight() ? "\" weight=\"" . $product->get_weight() : "")
                . "\" stock=\"" . $stock
                . "\" basket=\"" . ($this->basketDisabled ? '0' : '1')
                . "\">\n";
	}

	protected function generateProductAttributesFromProductArray( array $products, WoocommerceCategoryWrapper $wrappedCategory ): string {
		$productAttributes = [];
		foreach ( $products as $product ) {
			foreach($product->get_attributes() as $key => $attribute) {
				$productAttributes[$key][] = $attribute;
			}
		}

		if(count($productAttributes) > 0) {
			$xml = "<attrs>\n";
			/**
			 * @var $attribute \WC_Product_Attribute | string
			 * @var $key string
			 */
			foreach ( $productAttributes as $key => $attributesArray ) {
				$attributeValues = [];
				foreach($attributesArray as $attribute) {
					$attributeName  = ( isset( $wrappedCategory->getMappedCeneoAttributes()[ $key ] ) && $wrappedCategory->getMappedCeneoAttributes()[ $key ] != 'null' ? $wrappedCategory->getMappedCeneoAttributes()[ $key ] : ( ( substr( ( $attribute instanceof \WC_Product_Attribute ? $attribute->get_name() : $key ), 0, 3 ) === 'pa_' ) ? wc_attribute_label( $key ) : ( $attribute instanceof \WC_Product_Attribute ? $attribute->get_name() : ucfirst($key) ) ) );
					$attributeValues[] = ( $attribute instanceof \WC_Product_Attribute ? implode( ';', $attribute->get_options() ) : $attribute );
				}
                $attributeValues = array_filter($attributeValues, function($value) { return !is_null($value) && $value !== ''; });
				$xml            .= "<a name=\"" . $attributeName . "\"><![CDATA[" . implode(';', $attributeValues) . "]]></a>\n";
			}
			$xml .= "</attrs>\n";

			return $xml;
		}
		return '';
	}
}
