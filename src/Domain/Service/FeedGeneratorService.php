<?php

namespace Ceneo\Domain\Service;

use Ceneo\Domain\Model\Category;
use Ceneo\Domain\Model\CurrentRunStats;
use Ceneo\Domain\Model\FeedGenerationRuntimeConfiguration;
use Ceneo\Domain\Model\WoocommerceCategoryWrapper;
use Ceneo\Application\Helper\LoggingHelper;
use Ceneo\Domain\Strategy\ExternalProductFeedGenerationStrategy;
use Ceneo\Domain\Strategy\GroupedProductFeedGenerationStrategy;
use Ceneo\Domain\Strategy\SimpleProductFeedGenerationStrategy;
use Ceneo\Domain\Strategy\VariableProductFeedGenerationStrategy;
use Ceneo\Domain\Validator\AvailabilityValidator;
use Ceneo\Domain\Validator\ExclusionValidator;
use Ceneo\Domain\Validator\PriceValidator;
use Ceneo\Domain\Validator\VisibilityValidator;

ini_set('xdebug.var_display_max_depth', '-1');
ini_set('xdebug.var_display_max_children', '-1');
ini_set('xdebug.var_display_max_data', '-1');

class FeedGeneratorService {

	private $optionService;
    private $loggingHelper;
	private $woocommerceCategoryService;

	public function __construct() {
		$this->optionService = new OptionService();
        $this->loggingHelper = new LoggingHelper();
		$this->woocommerceCategoryService = new WoocommerceCategoryService();
	}

	public function generate()
    {

        $runtimeConfiguration = $this->optionService->getRuntimeConfiguration();
        $this->loggingHelper->log("Feed generation process started.");

        # Restart chunk generation schedule
        if ( ! wp_next_scheduled( 'ceneo_generate_chunk' ) ) {
            wp_schedule_event(time(), 'everyhalfminute', 'ceneo_generate_chunk');
        }

        if (!$runtimeConfiguration->getActive()) {
            # Amount of products
            $allProducts = wp_count_posts('product');
            $amountOfProducts = $allProducts->publish;

            # Get current configuration for generator
            $configuration = $this->optionService->getConfiguration();

            # Set parameters for feed generation
            $runtimeConfiguration = new FeedGenerationRuntimeConfiguration();
            $runtimeConfiguration->setPriceRange($configuration->getPriceRange());
            $runtimeConfiguration->setExcludeMinPrice($configuration->getExcludeMinPrice());
            $runtimeConfiguration->setExcludeMaxPrice($configuration->getExcludeMaxPrice());
            $runtimeConfiguration->setMergeVariants($configuration->getMergeVariants());
            $runtimeConfiguration->setExcludeNotAvailable($configuration->getExcludeNotAvailable());

            $runtimeConfiguration->setActive(true);
            $runtimeConfiguration->setAmountOfProducts($amountOfProducts);
            $runtimeConfiguration->setCurrentOffset(0);

            $this->optionService->saveRuntimeConfiguration($runtimeConfiguration);

            # Set run stats to 0
            $currentRunStats = new CurrentRunStats();
            $currentRunStats->setNotProcessedProducts(0);
            $currentRunStats->setProcessedProducts(0);
            $currentRunStats->setAllProducts($amountOfProducts);

            $this->optionService->saveCurrentRunStats($currentRunStats);

            # Clear temporary file
            file_put_contents(wp_upload_dir()['basedir'] . '/' . $this->optionService->getFileName() . '-temp' . '.xml', '');
            $this->appendToTemporaryFile("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n");
            $this->appendToTemporaryFile("<offers xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"1\">\n");
        }
    }

	public function cancelGenerate() {

		# Stop generation
		$runtimeConfiguration = $this->optionService->getRuntimeConfiguration();
		$runtimeConfiguration->setActive(false);
		$this->optionService->saveRuntimeConfiguration($runtimeConfiguration);
        $this->loggingHelper->log("Feed generation process stopped.\n");

	}

	public function generateChunk() {
		# Get runtime configuration
		$runtimeConfiguration = $this->optionService->getRuntimeConfiguration();
		$currentRunStats = $this->optionService->getCurrentRunStats();

		if($runtimeConfiguration->getActive()) {

            $this->loggingHelper->log("Feed generation in progress. Generating new chunk.");

            # Set highest available memory limit for this run
            ini_set("memory_limit", "1024M");

            # Assume memory limit has been set correctly
            $memory_limit = 1024 * 1024 * 1024;

            # Get the actual memory PHP could allocate
            if (preg_match('/^(\d+)(.)$/', ini_get("memory_limit"), $matches)) {
                if ($matches[2] == 'M') {
                    $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
                } else if ($matches[2] == 'K') {
                    $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
                }
            }

            $processed = 0;

			# Get product for this run
			$args = array(
				'limit' => 2500,
				'offset' => $runtimeConfiguration->getCurrentOffset(),
				'orderby' => 'ID',
				'order' => 'DESC',
			);
			$products = wc_get_products( $args );

			/**
			 * @var $product \WC_Product
			 */
			foreach($products as $product) {
				# Validate product
				if( !$this->validate($product, [
					new ExclusionValidator($runtimeConfiguration),
                    new VisibilityValidator($runtimeConfiguration),
					new AvailabilityValidator($runtimeConfiguration),
					new PriceValidator($runtimeConfiguration)
				])) {
					$currentRunStats->setNotProcessedProducts($currentRunStats->getNotProcessedProducts() + 1);
					$this->optionService->saveCurrentRunStats($currentRunStats);
                    $processed++;
					continue;
				}

				# Choose appropriate strategy
				switch($product) {
					case $product instanceof \WC_Product_Variable:
						$strategy = new VariableProductFeedGenerationStrategy($runtimeConfiguration->getMergeVariants());
						break;
					case $product instanceof \WC_Product_Simple:
						$strategy = new SimpleProductFeedGenerationStrategy();
						break;
					case $product instanceof \WC_Product_Grouped:
						$strategy = new GroupedProductFeedGenerationStrategy();
						break;
					case $product instanceof \WC_Product_External:
						$strategy = new ExternalProductFeedGenerationStrategy();
						break;
					default:
                        $this->loggingHelper->log('Unsupported product detected while generating chunk and has been omitted.');
                        continue 2;
				}

				# Prepare categories for product
				$productIds = $product->get_category_ids();
				$results = [];
				$maxNestLength = 0;
				foreach($productIds as $productId) {
					$nestedCategories = [];
					$nestedCategories[] = $productId;
					foreach(get_ancestors($productId, 'product_cat', 'taxonomy') as $ancestor) {
						$nestedCategories[] = $ancestor;
					};

					if(count($nestedCategories) > $maxNestLength) $maxNestLength = count($nestedCategories);
					$results[] = $nestedCategories;
				}

				# Filter duplicated categories by longest category path
				while($maxNestLength > 0) {
					foreach($results as $categoryPath) {
						if(count($categoryPath) == $maxNestLength) {
							foreach($categoryPath as $categoryId) {
								foreach($results as $key => $categoryToRemove) {
									if(count($categoryToRemove) < $maxNestLength && in_array($categoryId, $categoryToRemove)) {
										unset($results[$key]);
									}
								}
							}
						}
					}
					$maxNestLength--;
				}

				$wrappedCategoryPaths = [];
				foreach($results as $categoryPath) {
					$wrappedCategoryPath = [];
					$wrappedCategoryPathTemp = $this->woocommerceCategoryService->getWrappedCategoryByWoocommerceCategoryId($categoryPath);
					foreach($categoryPath as $categoryId) {
						$wrappedCategoryPath[] = $wrappedCategoryPathTemp[$categoryId];
					}
					$wrappedCategoryPaths[] = array_reverse($wrappedCategoryPath);
				}

				if(count($wrappedCategoryPaths) > 0 ) {
					$currentRunStats->setProcessedProducts($currentRunStats->getProcessedProducts() + 1);
				} else {
					$currentRunStats->setNotProcessedProducts($currentRunStats->getNotProcessedProducts() + 1);
				}
				foreach($wrappedCategoryPaths as $wrappedCategoryPath) {
					$this->appendToTemporaryFile($strategy->generateProductXML($product, $wrappedCategoryPath));
				}

                $processed++;

                $product = null;
                $results = null;
                $wrappedCategoryPaths = null;

                # If we are trying to allocate more than 50% of memory, break
                if(memory_get_peak_usage(true) > $memory_limit * 0.7) {
                    $this->loggingHelper->log("Breaking loop. Memory limit of " . $memory_limit . " reached.");
                    break;
                }
			}

			# Save current run stats
			$this->optionService->saveCurrentRunStats($currentRunStats);

			# If we have any products left
			if($runtimeConfiguration->getCurrentOffset() + $processed < $runtimeConfiguration->getAmountOfProducts()) {
				$runtimeConfiguration->setCurrentOffset($runtimeConfiguration->getCurrentOffset() + $processed);
			} else {
				$this->appendToTemporaryFile("</offers>");
				$runtimeConfiguration->setActive(false);
				$this->optionService->saveRunTime(time());
				$this->copyTempFile();
			}
			$this->optionService->saveRuntimeConfiguration($runtimeConfiguration);
            $this->loggingHelper->log('Chunk generated successfully. Current progress is ' . $processed . ' products processed.');
		} else {
            $this->loggingHelper->log('Feed is already generated. Skipping chunk generation.');
        }
	}

	private function validate(\WC_Product $product, array $validators): bool {
		foreach($validators as $validator) {
			if(!$validator->validate($product)) return false;
		}
		return true;
	}

	public function appendToTemporaryFile($xml) {
		file_put_contents(wp_upload_dir()['basedir'] . '/' . $this->optionService->getFileName() . '-temp' . '.xml' , $xml, FILE_APPEND);
	}

	public function copyTempFile() {
		file_put_contents(
			wp_upload_dir()['basedir'] . '/' . $this->optionService->getFileName() . '.xml',
			file_get_contents(wp_upload_dir()['basedir'] . '/' . $this->optionService->getFileName() . '-temp' . '.xml')
		);
	}

	public function setNewFrequency(string $frequency): void {
		wp_clear_scheduled_hook('generateFeed');
		wp_schedule_event( time(), $frequency, 'generateFeed' );
	}
}
