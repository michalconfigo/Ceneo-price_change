<?php


namespace Ceneo\Application\Controller;


use Ceneo\Domain\Service\OptionService;

class RestApiController extends Controller {
	public function getXMLFeed() {

		if(!in_array($_SERVER['REMOTE_ADDR'], CENEO_SERVERS)) {
			return new \WP_REST_Response(null, 403);
		}

		$optionsService = new OptionService();
		$filename = $optionsService->getFileName();
		$uploads = wp_get_upload_dir();
		$data = file_get_contents($uploads['basedir'] . '/' . $filename . '.xml');

		$response = new \WP_REST_Response($data, 200);

		// Set headers.
		$response->set_headers([
			'Cache-Control' => 'must-revalidate, no-cache, no-store, private',
			'Content-Type' => 'text/xml; charset=UTF-8',
		]);

		return $response;
	}
}
