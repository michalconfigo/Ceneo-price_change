<?php

namespace Ceneo\Domain\Strategy;

use Ceneo\Domain\Model\WoocommerceCategoryWrapper;

abstract class FeedGenerationStrategy {
	public function generateProductXML(\WC_Product $product, array $wrappedCategoryPath) : string {

		$xml = $this->generateProductHeader($product);

		$xml .= $this->generateProductCategory($wrappedCategoryPath);
		$xml .= $this->generateProductName($product);

		$xml .= $this->generateProductImages($product);
		$xml .= $this->generateProductDescription($product);

		$xml .= $this->generateProductAttributes($product, end($wrappedCategoryPath));
		$xml .= $this->generateProductFooter();

		return $xml;
	}

	protected function generateProductAttributes(\WC_Product $product, WoocommerceCategoryWrapper $wrappedCategory): string {
		$productAttributes = $product->get_attributes();

		if(count($productAttributes) > 0) {
			$xml = "<attrs>\n";
			/**
			 * @var $attribute \WC_Product_Attribute | string
			 * @var $key string
			 */
			foreach ( $product->get_attributes() as $key => $attribute ) {
                $attributeName  = ( isset( $wrappedCategory->getMappedCeneoAttributes()[ $key ] ) && $wrappedCategory->getMappedCeneoAttributes()[ $key ] != 'null' ? $wrappedCategory->getMappedCeneoAttributes()[ $key ] : ( ( substr( ( $attribute instanceof \WC_Product_Attribute ? $attribute->get_name() : $key ), 0, 3 ) === 'pa_' ) ? wc_attribute_label( $key ) : ( $attribute instanceof \WC_Product_Attribute ? $attribute->get_name() : ucfirst($key) ) ) );
                $attributeValue = ( $attribute instanceof \WC_Product_Attribute ? implode( ';', $attribute->get_options() ) : $attribute );
				$xml            .= "<a name=\"" . $attributeName . "\"><![CDATA[" . $attributeValue . "]]></a>\n";
			}
			$xml .= "</attrs>\n";

			return $xml;
		}
		return '';
	}

	protected function generateProductDescription(\WC_Product $product): string {
		return '<desc><![CDATA[' . $product->get_short_description() . $product->get_description() . ']]></desc>';
	}

	protected function generateProductName(\WC_Product $product): string {
		return "<name><![CDATA[" . $product->get_name() . "]]></name>\n";
	}

	protected function generateProductCategory(array $wrappedCategoryPath): string {
		$categoryNames = [];
		foreach($wrappedCategoryPath as $wrappedCategory) {
			$categoryNames[] = ( $wrappedCategory->getMappedCeneoCategory() === null ) ? $wrappedCategory->getWoocommerceCategory()->name : $wrappedCategory->getMappedCeneoCategory()->getName();
		}
		return "<cat><![CDATA[" . implode('/', $categoryNames) . "]]></cat>\n";
	}

	protected function generateProductImages(\WC_Product $product): string {
		$mainImageId = $product->get_image_id();
		$galleryImageIds = $product->get_gallery_image_ids();

		$returnString = '';
		if($mainImageId || $galleryImageIds) {
			$returnString .= "<imgs>\n";
			if($mainImageId) { $returnString .= "<main url=\"" . wp_get_attachment_image_url( $mainImageId, 'full' ) . "\"/>\n"; }
			foreach($galleryImageIds as $galleryImageId) {
				$returnString .= "<i url=\"" . wp_get_attachment_image_url( $galleryImageId, 'full' ) . "\"/>\n";
			}
			$returnString .= "</imgs>\n";
		}
		return $returnString;
	}

	protected function generateProductHeader(\WC_Product $product): string {

		# Define basket state for all variants
		$basketMeta = get_post_meta($product->get_id(), '_ceneo_disable_buy_now');
		$basketDisabled = is_array($basketMeta) && !empty($basketMeta) && $basketMeta[0] === '1';
      # mik
		$upss = 1;
		$nettocena = ($product->get_price());
		$newcena = ($nettocena * 0.05);
		$cena = ($nettocena - $newcena);
		//$product->get_id(11004) = ($cena * 1);
			///$product->get_id(11004);
				
	
	
		# mik
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

		if($product->managing_stock() && $product->get_stock_quantity() > 0) {
			$stock = $product->get_stock_quantity();
		} else {
			if($product->is_in_stock()) {
				$stock = 99;
			} else {
				$stock = 0;
			}
		}

        return "<o id=\"" . $product->get_id()
            . "\" url=\"" . htmlspecialchars($product->get_permalink())
            . "\" price=\"" . $cena
            . "\" avail=\"" . $upss
            . ($product->has_weight() ? "\" weight=\"" . $product->get_weight() : "")
            . "\" stock=\"" . $stock
            . "\" basket=\"" . ($basketDisabled ? '0' : '1')
            . "\">\n";
	}

	protected function generateProductFooter(): string {
		return "</o>\n";
	}
}
