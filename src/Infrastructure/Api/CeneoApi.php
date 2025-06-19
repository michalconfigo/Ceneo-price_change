<?php

namespace Ceneo\Infrastructure\Api;

class CeneoApi {

    private $httpClient;

    function __construct()
    {
        $this->httpClient = new \WP_Http();
    }

    public function getAttributes(): \SimpleXMLElement {
        $response = $this->httpClient->request('https://developers.ceneo.pl/api/v3/atrybuty', ['method' => 'GET']);

	    if ( ! is_wp_error( $response ) ) {
		    if ( $response['response']['code'] == 200 ) {
			    return new \SimpleXMLElement( $response['body'] );
		    }
		    throw new \Exception();
	    } else {
	    	throw new \Exception();
	    }
    }
}
