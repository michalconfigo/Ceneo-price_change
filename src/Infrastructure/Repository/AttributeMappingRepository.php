<?php

namespace Ceneo\Infrastructure\Repository;

use Ceneo\Domain\Model\AttributeMapping;

class AttributeMappingRepository {

	private $db;

	public function __construct()
	{
		global $wpdb;
		$this->db = $wpdb;
	}

	public function saveAttributeMappings(array $mappings): void {
		if(!empty($mappings)) {

			$sql = 'INSERT INTO ' . $this->db->prefix . 'wc_ceneo_attribute_mapping (wp_category_id, wp_category_name, ceneo_name) VALUES ';
			$sql_partials = [];

			/** @var $mapping AttributeMapping */
			foreach ( $mappings as $mapping ) {
				$sql_partials[] = '(' . $this->db->_real_escape( $mapping->getWoocommerceCategory() ) . ', "' . $this->db->_real_escape( $mapping->getWoocommerceName() ) . '", ' . ($mapping->getCeneoName() ? '"' . $this->db->_real_escape( $mapping->getCeneoName() ) . '"' : 'NULL') . ')';
			}

			$sql .= implode( ', ', $sql_partials ) . ' ON DUPLICATE KEY UPDATE ceneo_name = VALUES(ceneo_name)';

			if ( $this->db->query( $sql ) === false ) {
				throw new \Exception();
			}
		}
	}

	public function deleteAttributeMappings(array $mappings): void {
		if(!empty($mappings)) {

			$sql = 'DELETE FROM ' . $this->db->prefix . 'wc_ceneo_attribute_mapping WHERE ';
			$sql_partials = [];

			/** @var $mapping AttributeMapping */
			foreach ( $mappings as $mapping ) {
				$sql_partials[] = 'wp_category_id = ' . $this->db->_real_escape( $mapping->getWoocommerceCategory() ) . ' AND wp_category_name = "' . $this->db->_real_escape( $mapping->getWoocommerceName() ) . '"';
			}

			$sql .= implode( ' OR ', $sql_partials );

			if ( $this->db->query( $sql ) === false ) {
				throw new \Exception();
			}
		}
	}

	public function getAttributeMappingByWPCategoryId(int $wpCategoryId): array {
		if (empty($wpCategoryId)) return [];
		$sql = 'SELECT wp_category_id, wp_category_name, ceneo_name FROM ' . $this->db->prefix . 'wc_ceneo_attribute_mapping WHERE wp_category_id = ' . $this->db->_real_escape($wpCategoryId);
		$results = $this->db->get_results($sql);
		if (!empty($results)) return $results;
		return [];
	}
}
