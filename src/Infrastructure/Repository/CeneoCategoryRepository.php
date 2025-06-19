<?php

namespace Ceneo\Infrastructure\Repository;

use Ceneo\Domain\Model\Attribute;
use Ceneo\Domain\Model\Category;

class CeneoCategoryRepository {

	private $db;

	public function __construct()
	{
		global $wpdb;
		$this->db = $wpdb;
	}

	public function saveCategory(Category $category): void {
		$sql = '
                INSERT INTO ' . $this->db->prefix . 'wc_ceneo_categories (id, name, parent)
                VALUES (' . $category->getId() . ',"' . $category->getName() . '", ' . (is_null($category->getParent()) ? 'NULL' : $category->getParent()) . ')
                ON DUPLICATE KEY UPDATE name = "' . $category->getName() . '", parent = ' . (is_null($category->getParent()) ? 'NULL' : $category->getParent());
		if($this->db->query($sql) === false){
			throw new \Exception();
		}
	}

	public function saveAttributes(array $attributes): void {
		if(!empty($attributes)) {
			$sql          = 'INSERT INTO ' . $this->db->prefix . 'wc_ceneo_attributes (name, value, is_key_attribute, category_id) VALUES ';
			$sql_partials = [];
			/** @var $attribute Attribute */
			foreach ( $attributes as $attribute ) {
				$sql_partials[] = '("' . $this->db->_real_escape( $attribute->getName() ) . '", "' . $this->db->_real_escape( $attribute->getExampleValue() ) . '", ' . $this->db->_real_escape( $attribute->getIsKey() ) . ', ' . $this->db->_real_escape( $attribute->getCategoryId() ) . ')';
			}
			$sql .= implode( ', ', $sql_partials ) . ' ON DUPLICATE KEY UPDATE name = VALUES(name), value = VALUES(value)';

			if ( $this->db->query( $sql ) === false ) {
				throw new \Exception();
			}
		}
	}

	public function getCategoriesByParentIds(array $parentIds): array {
		$sql = 'SELECT id, name, parent FROM ' . $this->db->prefix . 'wc_ceneo_categories WHERE parent ' . ((is_null($parentIds) || count($parentIds) == 0) ? 'is NULL' : 'IN (' . implode(', ', $parentIds) . ')');
		$results = $this->db->get_results($sql);
		if (!empty($results)) return $results;
		return [];
	}

	public function getCategoriesByIds(array $ids): array {
		$sql = 'SELECT id, name, parent FROM ' . $this->db->prefix . 'wc_ceneo_categories WHERE id ' . ((is_null($ids) || count($ids) == 0) ? 'is NULL' : 'IN (' . implode(', ', $ids) . ')');
		$results = $this->db->get_results($sql);
		if (!empty($results)) return $results;
		return [];
	}

	public function flushAll(): void {
		$sql = 'TRUNCATE TABLE ' . $this->db->prefix . 'wc_ceneo_categories';
		$this->db->query($sql);
		$sql = 'TRUNCATE TABLE ' . $this->db->prefix . 'wc_ceneo_attributes';
		$this->db->query($sql);
	}
}
