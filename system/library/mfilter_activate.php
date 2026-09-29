<?php

/**
 * Editing this file may result in loss of license which will be permanently blocked.
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 */

class MegaFilterActivate {
	
	private $ctrl;
	
	private $name = 'mega_filter_pro';
	
	public static function e( $activation_key ) {
		return base64_encode( json_encode( array( 'ak' => $activation_key, 'time' => time() ) ) );
	}
	
	public static function d( $activation_key ) {
		if( is_array( $activation_key ) ) {
			return $activation_key;
		}
		
		return json_decode( base64_decode( $activation_key ), true );
	}
	
	public static function cl( $license ) {
		if( ! is_array( $license ) ) {
			$license = self::d( $license );
		}
		
		if( ! isset( $license['ak'] ) ) {
			return false;
		}
		
		if( ! isset( $license['time'] ) ) {
			return false;
		}
		
		if( preg_match( '/^[a-zA-Z0-9]{5}\-[a-zA-Z0-9]{5}\-[a-zA-Z0-9]{5}\-[a-zA-Z0-9]{5}\-[a-zA-Z0-9]{5}\-[a-zA-Z0-9]{5}\-[a-zA-Z0-9]{5}\-[a-zA-Z0-9]{5}$/', $license['ak'] ) ) {
			return true;
		}
		
		return false;
	}
	
	private function _hasMFilterPlus() {
		if( ! file_exists( DIR_SYSTEM . 'library/mfilter_plus.php' ) ) {
			return false;
		}
		
		return true;
	}
	
	public function dak() {
		if( null == ( $license = $this->config->get('mfilter_license') ) ) {
			return;
		}
		
		if( ! is_array( $license ) ) {
			return;
		}
		
		if( ! isset( $license['ak'] ) ) {
			return;
		}
		
		$url = 'http://activate.ocdemo.eu/?' . $this->base_url_params( $this->base_url(), array(
			'ak' => $license['ak'],
			'e' => $this->name,
			'a' => 'dak'
		));
					
		if( false != ( $response = $this->getURL( $url ) ) ) {
			if( $response != '-1' && $response != '1' ) {
				$response = unserialize( $response );

				if( $response['status'] == 'success' ) {
					$ak = self::e( $response['ak'] );
					
					$this->db->query( "UPDATE `" . DB_PREFIX . "setting` SET `value` = '" . $this->db->escape( $ak ) . "', serialized = '0' WHERE `key` = 'mfilter_license'" );
					
					$this->config->set('mfilter_license', $ak );
					
					return true;
				}
			}
		}
		
		return false;
	}
	
	private function base_url_params( $url, $params = array() ) {
		$pu = parse_url( $url );
		
		if( empty( $pu['host'] ) ) {
			return null;
		}
		
		$p = array();
		
		foreach( $params as $k => $v ) {
			$p[] = $k . '=' . urlencode( $v );
		}
		
		$p[] = 'h=' . urlencode( $pu['host'] );
		$p[] = 'p=' . urlencode( isset( $pu['path'] ) ? $pu['path'] : '/' );
		$p[] = 'o=' . urlencode( VERSION );
		
		$ce = $this->config->get('config_email');
		
		if( is_array( $ce ) ) {
			$ce = current( $ce );
		}
		
		$p[] = 'ce=' . urlencode( $ce );
		
		return implode( '&', $p );
	}
	
	private function base_url() {
		return defined( 'HTTP_CATALOG' ) ? HTTP_CATALOG : HTTP_SERVER;
	}
	
	public function activate( $version, $license, $get_version = false ) {		
		$extensions = array( $this->name => $version );
		
		if( $this->_hasMFilterPlus() ) {
			$extensions['mega_filter_plus'] = $this->config->get('mfilter_plus_version');
		}
		
		$domains = array( 0 => $this->base_url() );
		
		foreach( $this->db->query( "SELECT * FROM `" . DB_PREFIX . "store` LIMIT 10" )->rows as $row ) {
			$domains[$row['store_id']] = $row['url'];
		}
		
		foreach( $domains as $store_id => $url ) {			
			$request = array();
			
			if( isset( $license['ak'] ) ) {
				$request[] = 'ak=' . $license['ak'];
			} else if( isset( $license['activation_key'] ) ) {
				$request[] = 'ak=' . $license['activation_key'];
			}
			
			if( null != ( $base_url_params = $this->base_url_params( $url ) ) ) {
				$theme = $this->db->query( "SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` IN('config_theme','config_template') AND `code`='config' AND `store_id`=" . (int) $store_id )->row;
				
				$request[] = 't=' . urlencode( isset( $theme['value'] ) ? (string) $theme['value'] : '' );
				
				if( $get_version ) {
					$request[] = 'cl=1';
					$request[] = 'gv=1';
				}
				
				foreach( $extensions as $extension => $version ) {
					$url = 'http://activate.ocdemo.eu/?' . $this->base_url_params( $url, array(
						'e' => $extension,
						'v' => $version,
					)) . '&' . implode( '&', $request );
					
					if( false != ( $response = $this->getURL( $url ) ) ) {
						if( $response == '-1' ) {
							return $this->fail();
						} else if( $response != '1' ) {
							$response = unserialize( $response );

							if( $response['status'] == 'success' ) {								
								if( $get_version ) {
									return array( 'latest_version' => $response['latest_version'] );
								}
							} else {
								return $this->fail( $response['message'] );
							}
						}
					} else {
						$msg = 'Error connecting to activation server.<br /><br />The cause of problem is your server which can\'t connect with external resources. You have to report this issue to the support of your hosting company because only they have the possibility to unlock such service.';
						
						return $this->fail( $msg );
					}
				}
			}
		}
		
		return true;
	}
	
	public function fail( $message = false ) {
		$this->db->query( "DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` = 'mfilter_license'" );
		$this->config->set( 'mfilter_license', '' );
		
		return $message;
	}
	
	private function getURL($url, $post = null) {		
		if( ! function_exists( 'curl_init' ) ) {
			if( false != ( $response = file_get_contents( $url ) ) ) {
				return $response;
			}
			
			return false;
		}
		
		$ch = curl_init();
		curl_setopt($ch, 10002, $url);
		curl_setopt($ch, 19913, 1);
		curl_setopt($ch, 64, false);
		curl_setopt($ch, 81, 0);
		if(!empty($post)) {
			curl_setopt($ch, 47, true);
			curl_setopt($ch, 10015, $post);
		} 
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	public static function make( $ctrl ) {
		return new MegaFilterActivate( $ctrl );
	}
	
	public function __construct( $ctrl ) {
		$this->ctrl = $ctrl;
	}
	
	public function __get( $name ) {
		return $this->ctrl->{$name};
	}
}