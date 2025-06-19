<?php
/*

Plugin Name: Ceneo.pl - oficjalna wtyczka

Description: Pełna integracja sklepu z Ceneo - 3w1: integracja produktów, Zaufane Opinie, Kup Teraz

Author: Ceneo.pl sp. z o.o.

Plugin URI: https://www.ceneo.pl/poradniki/instrukcja-woocommerce

Author URI: https://www.ceneo.pl/

Text Domain: ceneo.pl

Version: 1.1.0

*/

define('CENEO_SERVERS', [
	'::1',
	'127.0.0.1',
	'185.56.211.79',
	'194.0.251.164',
	'178.21.154.6',
	'178.21.156.11',
	'178.21.156.14',
	'178.21.153.124',
	'178.21.153.125',
	'91.194.188.180',
	'5.134.208.158',
	'178.21.159.240',
	'91.217.19.216',
	'91.217.19.218',
	'159.253.247.1',
	'159.253.247.2',
	'159.253.247.3',
	'159.253.247.4',
	'159.253.247.5',
	'159.253.247.6',
	'78.8.255.193',
	'78.8.255.194',
	'78.8.255.195',
	'78.8.255.196',
	'78.8.255.197',
	'78.8.255.198',
	'147.135.222.32',
	'217.182.164.191',
	'37.187.172.106',
	'51.91.152.213',
	'51.77.52.107',
	'5.134.208.248',
	'5.134.208.249',
	'5.134.208.250',
	'5.134.208.251',
	'5.134.208.252',
	'5.134.208.253',
	'5.134.208.254',
	'5.134.208.255',
	'194.0.251.68',
	'194.0.251.69',
	'194.0.251.70',
	'194.0.251.71',
]);

// Load core packages and the autoloader.
use Ceneo\PluginManagement;

require __DIR__ . '/src/Autoloader.php';

if ( ! \Ceneo\Autoloader::init() ) {
    return;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'ceneo/v1', 'feed', [
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => array(new \Ceneo\Application\Controller\RestApiController(), 'getXMLFeed')
	]);
});

if ( ! function_exists( 'ceneo_force_xml_response' ) ) {
	function ceneo_force_xml_response( $served, $result, $request, $server ) {
		if ( '/ceneo/v1/feed' !== $request->get_route() ) {
			return $served;
		}
		$server->send_header( 'Content-Type', 'text/xml' );
		echo $result->get_data();
		exit;
	}

	add_filter( 'rest_pre_serve_request', 'ceneo_force_xml_response', 10, 4 );
}

register_activation_hook( __FILE__, array(new \Ceneo\PluginManagement(), 'activate') );
register_deactivation_hook( __FILE__, array(new \Ceneo\PluginManagement(), 'deactivate') );
register_uninstall_hook(__FILE__, array('Ceneo\PluginManagement', 'uninstall'));


if ( is_plugin_active( 'woocommerce-ceneo-official/woocommerce-ceneo-official.php' ) ) {

	add_filter( 'cron_schedules', array('Ceneo\PluginManagement', 'addCustomSchedules') );
	add_action('generateFeed', array(new \Ceneo\Domain\Service\FeedGeneratorService(), 'generate') );
	add_action('getNewOrdersFromCeneo', array(new \Ceneo\Application\Worker\CeneoBuyNowWorker(), 'getNewOrdersFromCeneo'));

	add_action( 'admin_menu', 'ceneo_admin_page' );
	add_action( 'admin_action_assign-attributes', array(new \Ceneo\Application\Controller\CategoryMappingController(), 'postIntegrationOptions') );
	add_action( 'admin_action_update-configuration', array(new \Ceneo\Application\Controller\DashboardController(), 'postDashboard') );
	add_action( 'admin_action_generate-feed', array(new \Ceneo\Application\Controller\DashboardController(), 'postGenerateFeed') );
	add_action( 'admin_action_stop-generate-feed', array(new \Ceneo\Application\Controller\DashboardController(), 'postStopGenerateFeed') );
	add_action( 'admin_action_update-trusted-opinions', array(new \Ceneo\Application\Controller\DashboardController(), 'postTrustedOpinionsOptions') );
	add_action( 'admin_action_synchronize-with-ceneo', array(new \Ceneo\Application\Worker\CeneoCategoriesWorker(), 'init'));
	add_action( 'admin_action_update-buy-now', array(new \Ceneo\Application\Controller\DashboardController(), 'postApiKey'));
	add_action( 'ceneo_generate_chunk', array( new \Ceneo\Domain\Service\FeedGeneratorService(), 'generateChunk' ) );
	add_action( 'ceneo_synchronize_with_ceneo', array(new \Ceneo\Application\Worker\CeneoCategoriesWorker(), 'getCategoriesAndAttributesFromCeneoAndSaveToDb'));
	add_action( 'wp_head', array(new \Ceneo\Application\Controller\ProductPageController(), 'injectCeneoScript'));
	add_action( 'woocommerce_billing_fields', array(new \Ceneo\Application\Controller\ProductPageController(), 'injectZoCheckbox'));
	add_action( 'woocommerce_checkout_update_order_meta', array(new \Ceneo\Application\Controller\ProductPageController(), 'saveZoCheckbox'));
	add_action( 'woocommerce_thankyou', array(new \Ceneo\Application\Controller\ProductPageController(), 'getFinalConfirmationScript'));
	add_action( 'save_post', array(new \Ceneo\Application\Controller\ProductPageController(), 'postCeneoSettingsMetaBox'), 10, 1 );
	add_action( 'woocommerce_product_options_attributes', array(new \Ceneo\Application\Controller\ProductPageController(), 'getSuggestedAttributes'));
	add_action( 'manage_shop_order_posts_custom_column', array(new \Ceneo\Application\Controller\OrderListController(), 'getCustomColumnData') );
	add_action( 'woocommerce_product_data_panels', array(new \Ceneo\Application\Controller\ProductPageController(), 'getCeneoSettingsMetaBox') );
	add_filter( 'manage_edit-shop_order_columns', array(new \Ceneo\Application\Controller\OrderListController(), 'getCustomColumn') );

	/**
	 * Handle custom ceneo query vars to get orders with the source and ceneo_id meta.
	 * @param array $query - Args for WP_Query.
	 * @param array $query_vars - Query vars from WC_Order_Query.
	 * @return array modified $query
	 */
	if ( ! function_exists( 'ceneo_handle_query_var' ) ) {
		function ceneo_handle_query_var( $query, $query_vars ) {
			if ( ! empty( $query_vars['source'] ) ) {
				$query['meta_query'][] = array(
					'key'   => 'source',
					'value' => esc_attr( $query_vars['source'] ),
				);
			}

			if ( ! empty( $query_vars['ceneo_id'] ) ) {
				$query['meta_query'][] = array(
					'key'   => 'ceneo_id',
					'value' => esc_attr( $query_vars['ceneo_id'] ),
				);
			}

            if ( ! empty( $query_vars['vat_id'] ) ) {
                $query['meta_query'][] = array(
                    'key'   => 'vat_id',
                    'value' => esc_attr( $query_vars['vat_id'] ),
                );
            }

			return $query;
		}

		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'ceneo_handle_query_var', 10, 2 );
	}

	if ( ! function_exists( 'ceneo_create_meta_tab' ) )
	{
		function ceneo_create_meta_tab($productDataTabs)
		{
			$productDataTabs['ceneo-settings'] = [
				'label' => 'Ustawienia Ceneo',
				'target' => 'ceneo_options',
				'class'		=> array( 'show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_external' ),
			];
			return $productDataTabs;
		}
		add_filter( 'woocommerce_product_data_tabs', 'ceneo_create_meta_tab' );
	}

    if ( ! function_exists('ceneo_vat_id_display_admin_order_meta')) {
        function ceneo_vat_id_display_admin_order_meta($order)
        {
            echo '<p><strong>' . __('NIP') . ':</strong> ' . ($order->get_meta('vat_id') ? $order->get_meta('vat_id') : '-') . '</p>';
        }
        add_action('woocommerce_admin_order_data_after_billing_address', 'ceneo_vat_id_display_admin_order_meta', 10, 1);
    }

	if ( ! function_exists( 'ceneo_before_order_item_meta' ) ) {
		function ceneo_before_order_item_meta( $order ) {
			if ( $order->get_meta( 'source' ) === 'ceneo' ) {
				echo '<img src = "' . plugin_dir_url( __FILE__ ) . 'src/assets/img/logo-ceneo-simple-orange.svg" class = "ceneo-label" />';
			}
		}
		add_action( 'woocommerce_admin_order_data_after_order_details', 'ceneo_before_order_item_meta', 10, 3 );
	}

	if ( ! function_exists( 'ceneo_admin_page' ) ) {
		function ceneo_admin_page() {

			add_menu_page(
				'Konfiguracja',
				'Ceneo',
				'manage_options',
				'ceneo-integration',
				array( new \Ceneo\Application\Controller\DashboardController(), 'getDashboard' ),
				plugin_dir_url( __FILE__ ) . 'src/assets/img/logo-ceneo-menu.svg',
				58
			);

			# add_submenu_page( 'wdvh-startpoint', 'page-from-plugin', 'menu-from-plugin', 'manage_options', 'plugin-slug', 'wvdh_settings_page' );
			add_submenu_page(
				'ceneo-integration',
				'Mapowanie kategorii',
				'Mapowanie kategorii',
				'manage_options',
				'ceneo-category-mapping',
				array( new \Ceneo\Application\Controller\CategoryMappingController(), 'getCategoryMapping' )
			);

		}
	}

	/**
	 *  Add custom styles for plugin
	 */
	if ( ! function_exists( 'ceneo_admin_scripts' ) ) {
		function ceneo_admin_scripts() {
			wp_register_style(
				'wc-ceneo-material-icons',
				'https://fonts.googleapis.com/icon?family=Material+Icons+Outlined',
				array(),
				'1.0.1'
			);

			wp_register_style(
				'wc-ceneo-style',
				plugin_dir_url( __FILE__ ) . 'src/assets/style/style.css',
				array(),
				'1.0.1'
			);

			wp_register_script(
				'wc-ceneo-script',
				plugin_dir_url( __FILE__ ) . 'src/assets/js/script.js',
				array(),
				'1.0.1'
			);

			wp_enqueue_style( 'wc-ceneo-material-icons' );
			wp_enqueue_style( 'wc-ceneo-style' );
			wp_enqueue_script( 'jquery-ui-accordion' );
			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-droppable' );
			wp_enqueue_script( 'jquery-ui-progressbar' );
			wp_enqueue_script( 'wc-ceneo-script' );
		}

		add_action( 'admin_enqueue_scripts', 'ceneo_admin_scripts' );
	}
}
