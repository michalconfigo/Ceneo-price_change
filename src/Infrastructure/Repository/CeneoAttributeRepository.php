<?php

namespace Ceneo\Infrastructure\Repository;

class CeneoAttributeRepository {

    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function getAttributesByCategoryIds(array $categoryIds): array {
    	if(empty($categoryIds)) return [];

    	foreach($categoryIds as $key => $categoryId) {
    		$categoryIds[$key] = $this->db->_real_escape($categoryId);
	    }

        $sql = 'SELECT * FROM ' . $this->db->prefix . 'wc_ceneo_attributes WHERE category_id IN (' . implode(', ', $categoryIds) . ')';
        $result = $this->db->get_results($sql);

        if(!empty($result)) return $result;
        return [];
    }
}
