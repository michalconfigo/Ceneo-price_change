<?php


namespace Ceneo\Domain\Service;

use Ceneo\Domain\Model\Category;
use Ceneo\Infrastructure\Repository\AttributeMappingRepository;
use Ceneo\Infrastructure\Repository\CeneoAttributeRepository;
use Ceneo\Infrastructure\Repository\CeneoCategoryRepository;

class CategoryService
{
    private $ceneoCategoryRepository;
    private $ceneoAttributeRepository;

    public function __construct(){
        $this->ceneoCategoryRepository = new CeneoCategoryRepository();
        $this->ceneoAttributeRepository = new CeneoAttributeRepository();
    }

    public function saveCategories(array $categories): void {

        /** @var $category Category */
        foreach($categories as $category) {
            $this->ceneoCategoryRepository->saveCategory($category);
            if($category->getChildren() && count($category->getChildren()) > 0) {
                $this->saveCategories($category->getChildren());
            }
            if($category->getAttributes() && count($category->getAttributes()) > 0){
                $this->ceneoCategoryRepository->saveAttributes($category->getAttributes());
            }
        }
    }

    public function getAllCategories(): array {
	    $result = $this->mapArrayToCategoryTree();
	    return $result;
    }

	private function mapArrayToCategoryTree($parentIds = []): array {
    	$categories = $this->ceneoCategoryRepository->getCategoriesByParentIds($parentIds);
		if(empty($categories)) return [];

		$branch = [];
		$parentIds = [];


		# Map categories
		/** @var $element \WP_Term */
		foreach ($categories as $element) {
				$category = new Category();
				$category->setId($element->id);
				$category->setName($element->name);
				$category->setParent($element->parent);
				$parentIds[] = $element->id;
				$branch[$element->id] = $category;
		}

		# Populate attributes
		$attributes = $this->ceneoAttributeRepository->getAttributesByCategoryIds($parentIds);
		foreach($attributes as $element) {

			if($element->is_key_attribute) {
				$branch[$element->category_id]->addKeyAttribute($element->name);
			} else {
				$branch[$element->category_id]->addOptionalAttribute($element->name);
			}
		}

		# Get children
		$children = $this->mapArrayToCategoryTree($parentIds);
		foreach($children as $child) {
			$branch[$child->getParent()]->addChild($child);
		}
		return $branch;
	}

	public function flushAll() {
    	$this->ceneoCategoryRepository->flushAll();
	}
}
