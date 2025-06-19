<?php

namespace Ceneo\Infrastructure\Repository;

use Ceneo\Domain\Model\Mapping;

class CeneoMappingRepository {

	private $db;

	public function __construct()
	{
		global $wpdb;
		$this->db = $wpdb;
	}

	public function saveCategoryMappings(array $mappings): void {
		if(!empty($mappings)) {
			$sql          = 'INSERT INTO ' . $this->db->prefix . 'wc_ceneo_mapping (wp_id, ceneo_id) VALUES ';
			$sql_partials = [];
			/** @var $mapping Mapping */
			foreach ( $mappings as $mapping ) {
				$sql_partials[] = '(' . $this->db->_real_escape( $mapping->getWoocommerceId() ) . ', ' . $this->db->_real_escape( $mapping->getCeneoId() ) . ')';
			}
			$sql .= implode( ', ', $sql_partials ) . ' ON DUPLICATE KEY UPDATE ceneo_id = VALUES(ceneo_id)';

			if ( $this->db->query( $sql ) === false ) {
				throw new \Exception();
			}
		}
	}

	public function deleteCategoryMappings(array $mappings): void {
		if(!empty($mappings)){
			$sql          = 'DELETE FROM ' . $this->db->prefix . 'wc_ceneo_mapping WHERE wp_id IN ';
			$sql_partials = [];
			/** @var $mapping Mapping */
			foreach ( $mappings as $mapping ) {
				$ids[] = $this->db->_real_escape( $mapping->getWoocommerceId() );
			}
			$sql .= '(' . implode( ', ', $ids ) . ')';

			if ( $this->db->query( $sql ) === false ) {
				throw new \Exception();
			}
		}
	}

	public function getCategoryMappings(): array {
		$sql = 'SELECT cm.wp_id as wp_id, cm.ceneo_id as ceneo_id, cc.name as ceneo_name FROM ' . $this->db->prefix . 'wc_ceneo_mapping AS cm LEFT JOIN ' . $this->db->prefix . 'wc_ceneo_categories AS cc ON cc.id = cm.ceneo_id';
		$results = $this->db->get_results($sql);
		if (!empty($results)) return $results;
		return [];
	}
}
