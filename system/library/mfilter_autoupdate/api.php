<?php namespace mfilter_autoupdate;
/*
 * Editing this file may result in loss of license which will be permanently blocked.
 * 
 * @license Commercial
 * @author info@ocdemo.eu
*/

class Api {
	
	private $url = 'https://api.ocdemo.eu/extension/';
	
	private $registry;
	
	private $status;
	
	public function __construct( $registry ) {
		$this->registry = $registry;
	}
	
	public function __call( $name, $arguments ) {
		if( in_array( $name, array( 'get', 'post', 'put', 'patch', 'delete' ) ) ) {
			$uri = array_shift( $arguments );
			
			$data = $arguments && is_array( $arguments[0] ) ? array_shift( $arguments ) : array();
			
			return $this->request( $name, $uri, $data );
		}
	}
	
	private function request( $method, $uri, array $data ) {		
		$response = new Curl();
		$response = $response
			->to( $this->url . $uri )
			->withData(array_replace($data, array(
				'oc_version' => VERSION,
			)))
			->{$method}();
			
		$this->status = $response->status;
		
		if( $response->status != 200 ) {
			return null;
		}
		
		return Collect::make( $response->content );
	}
	
	public function __get( $name ) {
		return $this->registry->get($name);
	}
	
	public function url() {
		return $this->url;
	}
	
	public function status() {
		return $this->status;
	}
}