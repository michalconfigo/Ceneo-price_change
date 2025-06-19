<?php

namespace Ceneo\Domain\Service;

use Ceneo\Domain\Model\Attribute;
use Ceneo\Domain\Model\AttributeMapping;
use Ceneo\Domain\Model\Category;
use Ceneo\Domain\Model\Mapping;
use Ceneo\Infrastructure\Repository\AttributeMappingRepository;
use Ceneo\Infrastructure\Repository\CeneoCategoryRepository;
use Ceneo\Infrastructure\Repository\CeneoMappingRepository;

class MappingService {

	private $mappingRepository;
	private $attributeMappingRepository;

	public function __construct() {
		$this->mappingRepository = new CeneoMappingRepository();
		$this->attributeMappingRepository = new AttributeMappingRepository();

	}

	public function saveCategoryMappings($mappingsToSave): void {
		$mappingsToDelete = [];
		foreach($mappingsToSave as $key => $mapping) {
			if($mapping->getCeneoId() == '') {
				$mappingsToDelete[] = $mapping;
				unset($mappingsToSave[$key]);
			}
		}
		$this->mappingRepository->saveCategoryMappings($mappingsToSave);
		$this->mappingRepository->deleteCategoryMappings($mappingsToDelete);
	}

	public function saveAttributeMappings($mappingsToSave): void {
		$mappingsToDelete = [];
		foreach($mappingsToSave as $key => $mapping) {
			if($mapping->getCeneoName() == '') {
				$mappingsToDelete[] = $mapping;
				unset($mappingsToSave[$key]);
			}
		}
		$this->attributeMappingRepository->saveAttributeMappings($mappingsToSave);
		$this->attributeMappingRepository->deleteAttributeMappings($mappingsToDelete);
	}
}
