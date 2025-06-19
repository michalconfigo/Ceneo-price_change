<?php

namespace Ceneo\Application\Worker;

ini_set("xdebug.var_display_max_children", '-1');
ini_set("xdebug.var_display_max_data", '-1');
ini_set("xdebug.var_display_max_depth", '-1');
/*
 * This worker is responsible of fetching Ceneo categories once a day and saving it to database
 */

use Ceneo\Application\Helper\LoggingHelper;
use Ceneo\Application\Helper\NoticeHelper;
use Ceneo\Domain\Service\CategoryService;
use Ceneo\Domain\Service\CeneoApiService;
use Ceneo\Domain\Service\OptionService;

class CeneoCategoriesWorker
{
    private $ceneoApiService;
    private $optionService;
    private $categoryService;
    private $noticeHelper;
    private $loggingHelper;

    public function __construct()
    {
        $this->ceneoApiService = new CeneoApiService();
        $this->categoryService = new CategoryService();
        $this->optionService = new OptionService();
        $this->noticeHelper = new NoticeHelper();
        $this->loggingHelper = new LoggingHelper();
    }

    public function init() {
        $this->optionService->saveCeneoSynchronizationInProgress(true);
        $this->loggingHelper->log("Ceneo categories synchronization started.");
        $this->noticeHelper->addNotice('success', 'Synchronizacja struktury Ceneo została rozpoczęta.');
	    wp_safe_redirect( wp_get_referer() );
	    exit();
    }

    public function getCategoriesAndAttributesFromCeneoAndSaveToDb() {
    	if($this->optionService->getCeneoSynchronizationInProgress()) {
            $this->loggingHelper->log("Ceneo categories synchronization in progress. Fetching categories and attributes.");
    		try {
			    # Get and save categories and attributes
			    $attributes = $this->ceneoApiService->getAttributes();
			    $this->categoryService->flushAll();
			    $this->categoryService->saveCategories( $attributes );
			    $this->optionService->saveCeneoSynchronizationInProgress( false );
			    $this->optionService->saveSyncTime(time());
                $this->loggingHelper->log("Categories synchronized successfully.");
		    } catch(\Exception $exception) {
                $this->loggingHelper->log("Categories synchronization failed. Retrying with next sync.");
		    }
	    } else {
            $this->loggingHelper->log("Ceneo categories are already fetched. Skipping.");
        }
    }
}
