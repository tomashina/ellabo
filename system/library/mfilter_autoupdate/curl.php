<?php namespace mfilter_autoupdate;
/*
 * Editing this file may result in loss of license which will be permanently blocked.
 * 
 * @license Commercial
 * @author info@ocdemo.eu
*/

use stdClass;

class Curl {

	protected $curlObject = null;
	
	protected $curlOptions = array(
		'RETURNTRANSFER' => true,
		'FAILONERROR' => false,
		'FOLLOWLOCATION' => false,
		'CONNECTTIMEOUT' => '',
		'TIMEOUT' => 30,
		'USERAGENT' => '',
		'URL' => '',
		'POST' => false,
		'HTTPHEADER' => array(),
		'SSL_VERIFYPEER' => false,
		'HEADER' => true,
	);
	
	protected $packageOptions = array(
		'data' => array(),
	);

	public function to( $url ) {
		return $this->withCurlOption( 'URL', $url );
	}

	public function withData( $data = array() ) {
		return $this->withPackageOption( 'data', $data );
	}

	public function withOption( $key, $value ) {
		return $this->withCurlOption( $key, $value );
	}

	protected function withCurlOption( $key, $value ) {
		$this->curlOptions[$key] = $value;

		return $this;
	}

	protected function withPackageOption( $key, $value ) {
		$this->packageOptions[$key] = $value;

		return $this;
	}

	public function get() {
		$this->appendDataToURL();

		return $this->send();
	}

	public function post() {
		$this->setPostParameters();

		return $this->send();
	}

	protected function setPostParameters() {
		$this->curlOptions['POST'] = true;

		$parameters = $this->packageOptions['data'];

		$this->curlOptions['POSTFIELDS'] = $parameters;
	}

	public function put() {
		$this->setPostParameters();

		return $this->withOption( 'CUSTOMREQUEST', 'PUT' )->send();
	}

	public function patch() {
		$this->setPostParameters();

		return $this->withOption( 'CUSTOMREQUEST', 'PATCH' )->send();
	}

	public function delete() {
		$this->appendDataToURL();

		return $this->withOption( 'CUSTOMREQUEST', 'DELETE' )->send();
	}

	protected function send() {
		$this->curlObject = curl_init();
		$options = $this->forgeOptions();
		curl_setopt_array( $this->curlObject, $options );

		$response = curl_exec( $this->curlObject );

		$responseHeader = null;

		$headerSize = curl_getinfo( $this->curlObject, CURLINFO_HEADER_SIZE );
		$responseHeader = substr( $response, 0, $headerSize );
		$response = substr( $response, $headerSize );

		$responseData = curl_getinfo( $this->curlObject );

		if( curl_errno( $this->curlObject ) ) {
			$responseData['errorMessage'] = curl_error( $this->curlObject );
		}

		curl_close( $this->curlObject );

		$response = json_decode( $response, true );

		return $this->returnResponse( $response, $responseData, $responseHeader );
	}

	protected function parseHeaders( $headerString ) {
		$headers = array_filter( array_map( function ($x) {
			$arr = array_map( 'trim', explode( ':', $x, 2 ) );
			
			if( count( $arr ) == 2 ) {
				return [ $arr[0] => $arr[1] ];
			}
		}, array_filter( array_map( 'trim', explode( "\r\n", $headerString ) ) ) ) );

		return $this->arrayCollapse( $headers );
	}

	protected function arrayCollapse( $array ) {
		$results = [];

		foreach( $array as $values ) {
			if( ! is_array( $values ) ) {
				continue;
			}

			$results = array_merge( $results, $values );
		}

		return $results;
	}

	protected function returnResponse( $content, array $responseData = array(), $header = null ) {
		$object = new stdClass();
		$object->content = $content;
		$object->status = $responseData['http_code'];
		$object->contentType = $responseData['content_type'];

		if( array_key_exists( 'errorMessage', $responseData ) ) {
			$object->error = $responseData['errorMessage'];
		}

		$object->headers = $this->parseHeaders( $header );

		return $object;
	}

	protected function forgeOptions() {
		$results = array();
		
		foreach( $this->curlOptions as $key => $value ) {
			$arrayKey = constant( 'CURLOPT_' . $key );

			if( $key == 'POSTFIELDS' && is_array( $value ) ) {
				$results[$arrayKey] = http_build_query( $value, null, '&' );
			} else {
				$results[$arrayKey] = $value;
			}
		}

		return $results;
	}

	protected function appendDataToURL() {
		$parameterString = '';
		
		if( is_array( $this->packageOptions['data'] ) && count( $this->packageOptions['data'] ) != 0 ) {
			$parameterString = '?' . http_build_query( $this->packageOptions['data'], null, '&' );
		}

		return $this->curlOptions['URL'] .= $parameterString;
	}
}