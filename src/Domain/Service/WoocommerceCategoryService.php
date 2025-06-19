<?php

namespace Ceneo\Domain\Service;

use Ceneo\Domain\Model\Category;
use Ceneo\Domain\Model\WoocommerceAttribute;
use Ceneo\Domain\Model\WoocommerceCategoryWrapper;
use Ceneo\Infrastructure\Repository\AttributeMappingRepository;
use Ceneo\Infrastructure\Repository\CeneoAttributeRepository;
use Ceneo\Infrastructure\Repository\CeneoCategoryRepository;
use Ceneo\Infrastructure\Repository\CeneoMappingRepository;

class WoocommerceCategoryService {

	private $ceneoMappingRepository;
	private $attributeMappingRepository;
	private $ceneoCategoryRepository;
	private $ceneoAttributeRepository;

    public function __construct() {
    	$this->ceneoMappingRepository = new CeneoMappingRepository();
    	$this->ceneoCategoryRepository = new CeneoCategoryRepository();
    	$this->ceneoAttributeRepository = new CeneoAttributeRepository();
    	$this->attributeMappingRepository = new AttributeMappingRepository();
    }

	private function buildNTree(array $elements, $parentId = 0): array {
		$branch = array();

		/* @var $element WoocommerceCategoryWrapper */
		foreach ($elements as $element) {
			$woocommerceCategory = $element->getWoocommerceCategory();
			if ($woocommerceCategory->parent == $parentId) {
				$children = $this->buildNTree($elements, $woocommerceCategory->term_id);
				if ($children) {
					$element->setChildren($children);
				}
				$branch[] = $element;
			}
		}

		return $branch;
	}

	public function getWrappedCategoryByWoocommerceCategoryId(array $categoryIds): array {

		$args = array(
			'include' => $categoryIds,
			'taxonomy' => 'product_cat',
			'show_count' => 1,
			'pad_counts'   => 1,
			'hierarchical' => 0,
			'orderby' => 'term_group',
			'hide_empty' => 0
		);

		$product_categories = get_terms($args);
		$woocommerceCategories = [];

		# Populate wrapped object with Woocommerce data
		foreach($product_categories as $product_category) {
			$woocommerceCategory = new WoocommerceCategoryWrapper();
			$woocommerceCategory->setWoocommerceCategory($product_category);

			$attributes = $this->attributeMappingRepository->getAttributeMappingByWPCategoryId($product_category->term_id);

			# Populate with mapping array from DB
			$mappedAttributes = [];
			foreach($attributes as $attribute) {
				$mappedAttributes[$attribute->wp_category_name] = $attribute->ceneo_name;
			}
			$woocommerceCategory->setMappedCeneoAttributes($mappedAttributes);

			# Assign as next element of the array
			$woocommerceCategories[$product_category->term_id] = $woocommerceCategory;
		}

		# Get mapping informations for categories
		$mappedCategories = $this->ceneoMappingRepository->getCategoryMappings();

		# Prepare arrays of ids
		$mappedCategoriesCeneoIds = [];

		# Prepare mapping ceneoId => WooCommerceId
		$categoryMap = [];

		# Populate array if IDs and a map
		foreach($mappedCategories as $mappedCategory) {
			if(in_array($mappedCategory->wp_id, $categoryIds)) {
				$mappedCategoriesCeneoIds[]               = $mappedCategory->ceneo_id;
				$categoryMap[ $mappedCategory->ceneo_id ] = $mappedCategory->wp_id;
			}
		}

		# Get ceneo categories for mapped entities
		$ceneoCategories = $this->ceneoCategoryRepository->getCategoriesByIds($mappedCategoriesCeneoIds);

		# Get ceneo attributes for mapped entities
		$attributes = $this->ceneoAttributeRepository->getAttributesByCategoryIds($mappedCategoriesCeneoIds);

		# Assign categories for mapped entities
		foreach($ceneoCategories as $ceneoCategory) {
			$category = new Category();
			$category->setId($ceneoCategory->id);
			$category->setName($ceneoCategory->name);
			$category->setParent($ceneoCategory->parent);

			$woocommerceCategories[$categoryMap[$ceneoCategory->id]]->setMappedCeneoCategory($category);
		}

		# Assign attributes for mapped entities
		foreach($attributes as $attribute) {
			$ceneoCategory = $woocommerceCategories[ $categoryMap[ $attribute->category_id ] ]->getMappedCeneoCategory();
			if ( $attribute->is_key_attribute ) {
				$ceneoCategory->addKeyAttribute( $attribute->name );
			} else {
				$ceneoCategory->addOptionalAttribute( $attribute->name );
			}
			$woocommerceCategories[ $categoryMap[ $attribute->category_id ] ]->setMappedCeneoCategory( $ceneoCategory );
		}

		return $woocommerceCategories;
	}

    public function getAttibutesByWoocommerceCategory(\WP_Term $category): array {
        $category_slug = $category->slug;

        $query_args = array(
            'status'    => 'publish',
            'limit'     => -1,
            'category'  => array( $category_slug ),
        );

        $data = array();
        foreach( wc_get_products($query_args) as $product ){
            foreach( $product->get_attributes() as $taxonomy => $attribute ){
                if(!isset($data[$taxonomy])) {
                    $woocommerceAttribute = new WoocommerceAttribute();
                    $woocommerceAttribute->setId( $taxonomy );
                    $woocommerceAttribute->setName( ( substr( $attribute->get_name(), 0, 3 ) === 'pa_' ) ? wc_attribute_label( $taxonomy ) : $attribute->get_name() );
                    $woocommerceAttribute->setCategory( $category->term_id );
                    $data[ $taxonomy ] = $woocommerceAttribute;
                }
            }
        }

        return array_values($data);
    }

    public function getWrappedCategories(): array {
	    $args = array(
		    'taxonomy' => 'product_cat',
		    'show_count' => 1,
		    'pad_counts'   => 1,
		    'hierarchical' => 0,
		    'orderby' => 'name',
		    'hide_empty' => 0
	    );
	    $product_categories = get_terms($args);
	    $woocommerceCategories = [];

	    # Populate wrapped object with Woocommerce data
	    foreach($product_categories as $product_category) {
		    $woocommerceCategory = new WoocommerceCategoryWrapper();
		    $woocommerceCategory->setWoocommerceCategory($product_category);
            $woocommerceCategory->setWoocommerceAttributes($this->getAttibutesByWoocommerceCategory($product_category));

		    $attributes = $this->attributeMappingRepository->getAttributeMappingByWPCategoryId($product_category->term_id);

		    # Populate with mapping array from DB
		    $mappedAttributes = [];
		    foreach($attributes as $attribute) {
			    $mappedAttributes[$attribute->wp_category_name] = $attribute->ceneo_name;
		    }
		    $woocommerceCategory->setMappedCeneoAttributes($mappedAttributes);

		    # Assign as next element of the array
		    $woocommerceCategories[$product_category->term_id] = $woocommerceCategory;
	    }

	    # Get mapping informations for categories
	    $mappedCategories = $this->ceneoMappingRepository->getCategoryMappings();

	    # Prepare arrays of ids
	    $mappedCategoriesCeneoIds = [];

	    # Prepare mapping ceneoId => WooCommerceId
	    $categoryMap = [];

	    # Populate array if IDs and a map
	    foreach($mappedCategories as $mappedCategory) {
			$mappedCategoriesCeneoIds[] = $mappedCategory->ceneo_id;
			$categoryMap[$mappedCategory->ceneo_id][] = $mappedCategory->wp_id;
	    }

	    # Get ceneo categories for mapped entities
	    $ceneoCategories = $this->ceneoCategoryRepository->getCategoriesByIds($mappedCategoriesCeneoIds);

	    # Get ceneo attributes for mapped entities
	    $attributes = $this->ceneoAttributeRepository->getAttributesByCategoryIds($mappedCategoriesCeneoIds);

	    # Assign categories for mapped entities
	    foreach($ceneoCategories as $ceneoCategory) {
		    $category = new Category();
		    $category->setId($ceneoCategory->id);
		    $category->setName($ceneoCategory->name);
		    $category->setParent($ceneoCategory->parent);
            foreach($categoryMap[$ceneoCategory->id] as $wcCategory) {
                $woocommerceCategories[$wcCategory]->setMappedCeneoCategory($category);
            }
	    }

	    # Assign attributes for mapped entities
	    foreach($attributes as $attribute) {
            foreach($categoryMap[$attribute->category_id] as $wcCategory) {
                $ceneoCategory = $woocommerceCategories[$wcCategory]->getMappedCeneoCategory();
                if ($attribute->is_key_attribute) {
                    $ceneoCategory->addKeyAttribute($attribute->name);
                } else {
                    $ceneoCategory->addOptionalAttribute($attribute->name);
                }
                $woocommerceCategories[$wcCategory]->setMappedCeneoCategory($ceneoCategory);
            }
	    }

	    return $this->buildNTree($woocommerceCategories);
    }
}
