<?php

namespace Ceneo\Application\Controller;

use Ceneo\Application\Helper\NoticeHelper;
use Ceneo\Application\Worker\CeneoBuyNowWorker;
use Ceneo\Domain\Model\FeedGenerationConfiguration;
use Ceneo\Domain\Model\TrustedOpinionsConfiguration;
use Ceneo\Domain\Service\BuyNowApiService;
use Ceneo\Domain\Service\FeedGeneratorService;
use Ceneo\Domain\Service\OptionService;

class DashboardController extends  Controller {

	private $optionService;
	private $feedGeneratorService;
	private $buyNowApiService;
	private $noticeService;

	public function __construct() {
		parent::__construct();

		$this->optionService = new OptionService();
		$this->feedGeneratorService = new FeedGeneratorService();
		$this->buyNowApiService = new BuyNowApiService();
		$this->noticeService = new NoticeHelper();
	}

	public function getDashboard() {

		echo $this->renderer->render('dashboard.html.twig', [
			"logo_url" => plugin_dir_url( __FILE__ ) . '../../assets/img/logo-ceneo-simple-orange.svg',
			"post_url" => admin_url( 'admin.php' ),
			"api_key" => esc_html($this->optionService->getApiKey()),
			"page_title" => esc_html(get_admin_page_title()),
			"fileurl" => get_site_url() . '?rest_route=/ceneo/v1/feed',

			"current_stats" => $this->optionService->getCurrentRunStats(),
			"runtime_configuration" => $this->optionService->getRuntimeConfiguration(),
			"configuration" => $this->optionService->getConfiguration(),
			"last_run_time" => esc_html(get_date_from_gmt(date('Y-m-d H:i:s', $this->optionService->getRunTime()))),
			"feed_generation_frequency" => esc_html($this->optionService->getFeedGenerationFrequency()),
			"trusted_opinions_configuration" => $this->optionService->getTrustedOpinionsConfiguration(),
			"notices" => $this->noticeService->getNotices()
		]);
	}

	public function postDashboard() {
		$configuration = new FeedGenerationConfiguration();
		$configuration->setExcludeNotAvailable(isset($_REQUEST['exclude-not-available']) ? isset($_REQUEST['exclude-not-available']) == 'true' : false);
		$configuration->setMergeVariants(isset($_REQUEST['merge-variants']) ? $_REQUEST['merge-variants'] == 'true' : false);
		$configuration->setExcludeMinPrice(isset($_REQUEST['exclude-min-price']) ? $_REQUEST['exclude-min-price'] == 'true' : false);
		$configuration->setExcludeMaxPrice(isset($_REQUEST['exclude-max-price']) ? $_REQUEST['exclude-max-price'] == 'true' : false);
		$configuration->setPriceRange([
			"min" => (isset($_REQUEST['exclude-min-price']) && isset($_REQUEST['min'])) ? ((float) sanitize_text_field($_REQUEST['min']) * 100) : null,
			"max" => (isset($_REQUEST['exclude-max-price']) && isset($_REQUEST['max'])) ? ((float) sanitize_text_field($_REQUEST['max']) * 100) : null
		]);
		$this->optionService->saveConfiguration($configuration);

		$this->optionService->saveFeedGenerationFrequency(sanitize_text_field($_REQUEST['frequency']));
		$this->feedGeneratorService->setNewFrequency($this->optionService->getFeedGenerationFrequency());

		$this->noticeService->addNotice('success', 'Konfiguracja generowania pliku XML została zaktualizowana.');
		wp_safe_redirect(wp_get_referer());
		exit();
	}

	public function postGenerateFeed() {
		$this->feedGeneratorService->generate();

		$this->noticeService->addNotice('success', 'Rozpoczęto proces generowania feed\'a XML.');
		wp_safe_redirect(wp_get_referer());
		exit();
	}

	public function postStopGenerateFeed() {
		$this->feedGeneratorService->cancelGenerate();

		$this->noticeService->addNotice('error', 'Proces generowania feed\'a XML został przerwany.');
		wp_safe_redirect(wp_get_referer());
		exit();
	}

	public function postTrustedOpinionsOptions() {
		$trustedOpinionsConfiguration = new TrustedOpinionsConfiguration();

		$questionnaireDays = sanitize_text_field($_REQUEST['questionnaire-days']);

		if(is_numeric($questionnaireDays) && $questionnaireDays > 0 && $questionnaireDays < 22) {
			$trustedOpinionsConfiguration->setQuestionnaireExpirationDays( $questionnaireDays );
		} else {
			$this->noticeService->addNotice('error', 'Ilość dni na wystawienie opinii powinna zawierać się w przediale 0-21');
			wp_safe_redirect(wp_get_referer());
			exit();
		}

		$trustedOpinionsConfiguration->setCeneoGUID(sanitize_text_field($_REQUEST['ceneo-guid']));
		$this->optionService->saveTrustedOpinionsConfiguration($trustedOpinionsConfiguration);

		$this->noticeService->addNotice('success', 'Konfiguracja Zaufanych Opinii została zaktualizowana.');
		wp_safe_redirect(wp_get_referer());
		exit();
	}

	public function postApiKey() {
		$ceneoApiKey = sanitize_text_field($_REQUEST['ceneo-api-key']);

		$apiKey = is_string($ceneoApiKey) ? $ceneoApiKey : '';

		if($this->buyNowApiService->validateToken($apiKey)) {
			$this->noticeService->addNotice( 'success', 'Klucz API został zapisany.' );
			$this->optionService->saveApiKey($apiKey);
		} else {
			$this->noticeService->addNotice('error', 'Niepoprawny klucz API. Wprowadź poprawny klucz API.');
		}
		wp_safe_redirect(wp_get_referer());
		exit();

	}
}
