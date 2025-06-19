<?php


namespace Ceneo\Application\Controller;


class OrderListController extends Controller {
	public function getCustomColumn(array $columns): array {
		$newColumns = [];
		foreach($columns as $columnName => $columnInfo) {
			$newColumns[$columnName] = $columnInfo;
			if('order_status' === $columnName) {
				$newColumns['source'] = 'Źródło';
			}
		}

		return $newColumns;
	}

	public function getCustomColumnData($column): void {
		if ( 'source' === $column ) {
			global $post;
			$order = wc_get_order($post->ID);
			if($order->get_meta('source') === 'ceneo') {
				echo '<img src = "' . esc_attr(plugin_dir_url( __FILE__ )) . '../../assets/img/logo-ceneo-simple-orange.svg" class = "ceneo-label" />';
			}
		}
	}
}
