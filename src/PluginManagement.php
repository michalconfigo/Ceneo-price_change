<?php

namespace Ceneo;
use Ceneo\Application\Helper\LoggingHelper;
use Ceneo\Domain\Model\FeedGenerationConfiguration;
use Ceneo\Domain\Model\TrustedOpinionsConfiguration;
use Ceneo\Domain\Service\OptionService;

require_once( ABSPATH . 'wp-admin/includes/upgrade.php');

class PluginManagement {

	private $optionService;
    private $loggingHelper;

	public function __construct() {
		$this->optionService = new OptionService();
        $this->loggingHelper = new LoggingHelper();
	}

	public static function install() {
        global $wpdb;

        $createTablesSql = "CREATE TABLE " . $wpdb->prefix . "wc_ceneo_categories (
		    id mediumint(9) NOT NULL,
		    name varchar(255) NOT NULL COLLATE 'utf8mb4_bin',
		    parent mediumint(9),
		    PRIMARY KEY (id)
	    );";

        \dbDelta($createTablesSql);

        $createTablesSql = "CREATE TABLE " . $wpdb->prefix . "wc_ceneo_attributes (
		    name varchar(127) NOT NULL COLLATE 'utf8mb4_bin',
		    value varchar(255) NOT NULL COLLATE 'utf8mb4_bin',
		    is_key_attribute boolean NOT NULL,
		    category_id mediumint(9) NOT NULL,
		    PRIMARY KEY (name, category_id)
	    );";

        \dbDelta($createTablesSql);

        $createTablesSql = "CREATE TABLE " . $wpdb->prefix . "wc_ceneo_mapping (
            wp_id mediumint(9) NOT NULL,
            ceneo_id mediumint(9) NOT NULL,
            PRIMARY KEY (wp_id)
        );";

        \dbDelta($createTablesSql);

	    $createTablesSql = "CREATE TABLE " . $wpdb->prefix . "wc_ceneo_attribute_mapping (
            wp_category_id mediumint(9) NOT NULL,
            wp_category_name varchar(127) NOT NULL COLLATE 'utf8mb4_bin',
            ceneo_name varchar(127) NOT NULL COLLATE 'utf8mb4_bin',
            PRIMARY KEY (wp_category_id, wp_category_name)
        );";

	    \dbDelta($createTablesSql);
    }

    public function activate() {
        $this->loggingHelper->log("Plugin activation started.");
    	PluginManagement::install();
	    add_filter('cron_schedules', array('Ceneo\PluginManagement', 'addCustomSchedules') );
	    add_action('generateFeed', array(new \Ceneo\Domain\Service\FeedGeneratorService(), 'generate') );
        add_action('ceneo_generate_chunk', array( new \Ceneo\Domain\Service\FeedGeneratorService(), 'generateChunk' ) );
	    add_action('getNewOrdersFromCeneo', array(new \Ceneo\Application\Worker\CeneoBuyNowWorker(), 'getNewOrdersFromCeneo'));
        add_action('ceneo_synchronize_with_ceneo', array(new \Ceneo\Application\Worker\CeneoCategoriesWorker(), 'getCategoriesAndAttributesFromCeneoAndSaveToDb'));
        add_action('ceneo_rotate_logs', array(new \Ceneo\Application\Helper\LoggingHelper(), 'rotate'));

        wp_schedule_event( time(), 'everyhalfminute', 'ceneo_rotate_logs');
	    wp_schedule_event( time(), 'quarterly', 'generateFeed' );
	    wp_schedule_event( time(), 'quarterly', 'getNewOrdersFromCeneo');
        wp_schedule_event( time(), 'everyhalfminute', 'ceneo_generate_chunk');
        wp_schedule_event( time(), 'everyhalfminute', 'ceneo_synchronize_with_ceneo');

	    //Set options default values
	    $feedGenerationConfiguration = new FeedGenerationConfiguration();
	    $feedGenerationConfiguration->setMergeVariants(true);
	    $feedGenerationConfiguration->setExcludeNotAvailable(true);
	    $this->optionService->saveConfiguration($feedGenerationConfiguration);

	    $trustedOpinionsConfiguration = new TrustedOpinionsConfiguration();
	    $trustedOpinionsConfiguration->setQuestionnaireExpirationDays(5);
	    $this->optionService->saveTrustedOpinionsConfiguration($trustedOpinionsConfiguration);
        $this->loggingHelper->log("Plugin activated.");
    }

    public function deactivate() {
	    wp_clear_scheduled_hook('generateFeed');
	    wp_clear_scheduled_hook('getNewOrdersFromCeneo');
	    wp_clear_scheduled_hook('ceneo_generate_chunk');
	    wp_clear_scheduled_hook('ceneo_synchronize_with_ceneo');
        wp_clear_scheduled_hook('sentinelWorker');
        $this->loggingHelper->log("Plugin deactivated.");


	    //Removce all options
	    $this->optionService->removeAllOptions();
    }

    public static function uninstall() {
    	// CLear all hooks
	    wp_clear_scheduled_hook('generateFeed');
	    wp_clear_scheduled_hook('getNewOrdersFromCeneo');
	    wp_clear_scheduled_hook('ceneo_generate_chunk');
	    wp_clear_scheduled_hook('ceneo_synchronize_with_ceneo');
        wp_clear_scheduled_hook('sentinelWorker');

	    //Removce all options
	    $optionService = new OptionService();
	    $optionService->removeAllOptions();

	    //Drop not used databases
	    global $wpdb;
	    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_ceneo_categories" );
	    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_ceneo_attributes" );
	    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_ceneo_mapping" );
	    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wc_ceneo_attribute_mapping" );
    }

    public static function addCustomSchedules($schedules) {
	    // add a 'weekly' schedule to the existing set
	    $schedules['quarterly'] = array(
		    'interval' => 900,
		    'display' => __('Every 15 Minutes')
	    );
	    $schedules['everyhalfminute'] = array(
		    'interval' => 30,
		    'display' => __('Every half minute')
	    );
	    $schedules['halfly'] = array(
		    'interval' => 1800,
		    'display' => __('Every 30 Minutes')
	    );
	    $schedules['twohourly'] = array(
		    'interval' => 7200,
		    'display' => __('Every 2 Hours')
	    );
	    return $schedules;
    }
}
