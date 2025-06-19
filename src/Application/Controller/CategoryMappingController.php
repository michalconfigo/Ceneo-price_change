<?php

namespace Ceneo\Application\Controller;

use Ceneo\Application\Helper\NoticeHelper;
use Ceneo\Domain\Model\AttributeMapping;
use Ceneo\Domain\Model\Mapping;
use Ceneo\Domain\Service\CategoryService;
use Ceneo\Domain\Service\MappingService;
use Ceneo\Domain\Service\OptionService;
use Ceneo\Domain\Service\WoocommerceCategoryService;

class CategoryMappingController extends Controller {

    private $categoryService;
    private $woocommerceCategoryService;
    private $mappingService;
    private $optionService;
    private $noticeHelper;

    public function __construct()
    {
        parent::__construct();

        $this->categoryService = new CategoryService();
        $this->woocommerceCategoryService = new WoocommerceCategoryService();
        $this->mappingService = new MappingService();
        $this->optionService = new OptionService();
        $this->noticeHelper = new NoticeHelper();
    }

    public function getCategoryMapping() {
        echo $this->renderer->render(
                'category-mapping.html.twig',
                [
	                    "logo_url" => plugin_dir_url( __FILE__ ) . '../../assets/img/logo-ceneo-simple-orange.svg',
                        "post_url" => admin_url( 'admin.php' ),
                        "page_title" => esc_html(get_admin_page_title()),
                        "is_synchronization_in_progress" => $this->optionService->getCeneoSynchronizationInProgress(),
                        "ceneo_categories" => $this->categoryService->getAllCategories(),
	                    "categories" => $this->woocommerceCategoryService->getWrappedCategories(),
                        "notices" => $this->noticeHelper->getNotices(),
	                    "last_sync_time" => esc_html($this->optionService->getSyncTime())
                ]
        );
    }

    public function postIntegrationOptions() {
        $categoryMappings = [];
        $attributeMappings = [];

        foreach($_REQUEST as $key => $value) {
            $value = sanitize_text_field($value);

            if(isset($key) && $key != '' && isset($value)) {

	            if ( substr( $key, 0, 4 ) == 'cat-' ) {
		            $mapping = new Mapping();
		            $mapping->setWoocommerceId( substr( $key, 4 ) );
		            $mapping->setCeneoId( sanitize_text_field($value) );
		            $mapping->setCeneoName('Nazwa kategorii');
		            $categoryMappings[] = $mapping;
	            }

	            if ( substr( $key, 0, 5 ) == 'attr-' ) {
		            $mapping = new AttributeMapping();
		            $mapping->setWoocommerceCategory( substr( $key, 5 , strpos($key, ':') - 5) );
		            $mapping->setWoocommerceName(substr($key, strpos($key, ':') + 1));
		            $mapping->setCeneoName( sanitize_text_field($value) );
		            $attributeMappings[] = $mapping;
	            }
            }

        }
        if(count($categoryMappings) > 0) { $this->mappingService->saveCategoryMappings($categoryMappings); }
        if(count($attributeMappings) > 0) { $this->mappingService->saveAttributeMappings($attributeMappings); }

        $this->noticeHelper->addNotice('success', 'Mapowanie kategorii zostało zaktualizowane.');
	    wp_safe_redirect( wp_get_referer() );

	    exit();
    }
}
