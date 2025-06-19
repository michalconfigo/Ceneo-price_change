<?php

namespace Ceneo\Domain\Service;

use Ceneo\Domain\Model\Attribute;
use Ceneo\Domain\Model\Category;
use Ceneo\Infrastructure\Api\CeneoApi;

class CeneoApiService {

    private $ceneoApi;

    public function __construct()
    {
        $this->ceneoApi = new CeneoApi();
    }

    public function getAttributes(): array {
        $categories = [];
        foreach($this->ceneoApi->getAttributes() as $attributesCategory) {
            $categories[] = $this->mapCategory($attributesCategory, new Category());
        }
        return $categories;
    }

    private function mapCategory(\SimpleXMLElement $sourceCategory, Category $destinationCategory, int $parent = null) {
        $destinationCategory->setId((int) $sourceCategory->Id);
        $destinationCategory->setName((string) $sourceCategory->Name);
        $destinationCategory->setParent($parent);

        if(isset($sourceCategory->Subcategories) && isset($sourceCategory->Subcategories->Attributes) && $sourceCategory->Subcategories->Attributes->count() > 0){
            $attributes = [];
            foreach($sourceCategory->Subcategories->Attributes->Attribute as $sourceAttribute) {
                $attribute = new Attribute();
                $attribute->setCategoryId($destinationCategory->getId());
                $attribute->setName((string) $sourceAttribute->Name);
                $attribute->setIsKey((string) $sourceAttribute->IsKeyAttribute);
                $attribute->setExampleValue((string) $sourceAttribute->Value);
                $attributes[] = $attribute;
            }
            $destinationCategory->setAttributes($attributes);
        }

        if (isset($sourceCategory->Subcategories) && $sourceCategory->Subcategories->Category->count() > 0) {
            $subcategories = [];
            foreach($sourceCategory->Subcategories->Category as $sourceSubcategory) {
                $subcategories[] = $this->mapCategory($sourceSubcategory, new Category(), $destinationCategory->getId());
            }
            $destinationCategory->setChildren($subcategories);
        }
        return $destinationCategory;
    }
}
