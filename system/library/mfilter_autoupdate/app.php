<?php namespace mfilter_autoupdate;
/*
 * Editing this file may result in loss of license which will be permanently blocked.
 * 
 * @license Commercial
 * @author info@ocdemo.eu
*/

class App {
	
	private $api;
	
	protected $registry;
	
	protected static $instance;
	
	protected $extensions = array();
	
	protected $errors = array();
	
	protected $messages = array();
	
	protected $fatal_error = false;
	
	protected $temp_files = array();
	
	protected $temp_dirs = array();
	
	public function getMessages() {
		return $this->messages;
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	public function hasFatalError() {
		return $this->fatal_error;
	}
	
	public function version( $extension_code ) {
		if( ! $this->api ) {
			return null;
		}
		
		return $this->api->get('version', array(
			'extension_code' => $extension_code,
		));
	}
	
	/**
	 * @return boolean
	 */
	public function update( $activation_key ) {
		if( ! $this->api ) {
			return null;
		}
		
		/* @var $version \mfilter_autoupdate\Collect */
		if( null == ( $version = $this->api->get( 'version', array( 'activation_key' => $activation_key ) ) ) ) {
			$this->errors[] = 'Update error #1';
			
			return false;
		}
		
		if( $version->status == 'error' ) {
			if( $version->has('message') ) {
				$this->errors[] = $version->message;
			} else {
				$this->errors[] = 'Update error #2';
			}
			
			return false;
		}
		
		if( ! $version->has( 'data' ) ) {
			$this->errors[] = 'Update error #3';
			
			return false;
		}
		
		if( ! $version->data->has('order_extension') ) {
			$this->messages[] = 'No available updates';
			
			return false;
		}
		
		$updated = false;
		
		foreach( $this->extensions as $extension ) {
			if( $version->data->order_extension->code == $extension['code'] ) {
				if( version_compare( $extension['version'], $version->data->order_extension->version, '<' ) ) {
					if( $this->_update( $activation_key, $extension['code'], $extension['type'], $version->data->order_extension->version ) ) {
						$updated = true;
					} else {
						$this->errors[] = 'Update error #4';
						
						return false;
					}
				}
			}
			
			foreach( $version->data->order_extension->related as $related_extension ) {
				if( $related_extension->code == $extension['code'] ) {
					if( version_compare( $extension['version'], $related_extension->version, '<' ) ) {
						if( $this->_update( $activation_key, $related_extension['code'], $extension['type'], $related_extension->version ) ) {
							$updated = true;
						} else {
							return false;
						}
					}
				}
			}
		}
		
		return $updated;
	}
	
	public function refresh_page() {
		echo '<form method="post" id="autoupdate-refresh-page">';
		echo '<br /><br ><center>Updating, please wait...</center>';
		
		foreach( $this->request->post as $key => $value ) {
			echo '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
		}
		
		echo '</form>';
		echo '<script type="text/javascript">document.getElementById("autoupdate-refresh-page").submit();</script>';
		
		exit;
	}
	
	protected function check_path( $path ) {
		if( ! is_dir( $path ) ) {
			$this->errors[] = sprintf( 'Path "%s" not exists', $path );
		} else if( ! is_readable( $path ) ) {
			$this->errors[] = sprintf( 'Path "%s" is not readable', $path );
		} else if( ! is_writable( $path ) ) {
			$this->errors[] = sprintf( 'Path "%s" is not writable', $path );
		} else {
			return true;
		}
		
		return false;
	}
	
	protected function _update( $activation_key, $extension_code, $extension_type, $extension_version ) {
		/* @var $temp_path string */
		$temp_path = DIR_CACHE;
		
		if( ! $this->check_path( $temp_path ) ) {
			return false;
		}
		
		/* @var $unzip_path string */
		$this->temp_dirs[] = $unzip_path = $temp_path . 'ocme_unzip_temp/';
		
		if( ! is_dir( $unzip_path ) && ! @ mkdir( $unzip_path, 0777 ) ) {
			$this->errors[] = sprintf( 'Isn\'t possible to create the dir "%s".', $unzip_path );
			
			return false;
		}
		
		if( ! $this->check_path( $unzip_path ) ) {			
			return $this->_rollbackUpdateChanges();
		}
		
		return $this->_downloadUpdate( $unzip_path, $activation_key, $extension_code, $extension_type, $extension_version );
	}
	
	protected function _downloadUpdate( $unzip_path, $activation_key, $extension_code, $extension_type, $extension_version ) {
		/* @var $response Collect */
		$response = $this->api->get('download', array(
			'activation_key' => $activation_key,
			'extension_code' => $extension_code,
			'extension_type' => $extension_type,
			'extension_version' => $extension_version,
		));
		
		if( ! $response || $response->status != 'success' || ! $response->data || ! $response->data->data ) {
			$this->errors[] = 'Update download error #1';
			
			return $this->_rollbackUpdateChanges();
		}
		
		/* @var $zip_path string */
		$this->temp_files[] = $zip_path = DIR_CACHE . 'tmp-' . substr( md5( $extension_code . $extension_version ), 0, 10 ) . '.zip';
		
		file_put_contents( $zip_path, base64_decode( $response->data->data ) );
		
		if( ! file_exists( $zip_path ) || ! is_file( $zip_path ) ) {
			$this->errors[] = 'Update download error #2';
			
			return $this->_rollbackUpdateChanges();
		}
		
		return $this->_unzipUpdate( $unzip_path, $zip_path, $extension_code );
	}
	
	protected function _unzipUpdate( $unzip_path, $zip_path, $extension_code ) {		
		/* @var $zip \ZipArchive */
		$zip = new \ZipArchive();
		
		if( $zip->open( $zip_path ) ) {
			$zip->extractTo( $unzip_path );
			$zip->close();
		} else {
			$this->errors[] = 'Update unzip error #1';
			
			return $this->_rollbackUpdateChanges();
		}
		
		foreach( $this->scanPath( $unzip_path ) as $file ) {
			if( is_dir( $file ) ) {
				$this->temp_dirs[] = $file;
			} else {
				$this->temp_files[] = $file;
			}
		}
		
		return $this->_moveUpdate( $unzip_path, $extension_code );
	}
	
	protected function scanPath( $path ) {
		/* @var $files array */
		$files = array();
		
		/* @var $paths array */
		$paths = array( rtrim( $path, '/' ) . '/*' );
		
		while( $paths ) {
			/* @var $next string */
			$next = array_shift( $paths );
			
			/* @var $file string */
			foreach( (array) glob( $next ) as $file ) {
				if( is_dir( $file ) ) {
					$paths[] = $file . '/*';
				}
				
				$files[] = $file;
			}
		}
		
		return $files;
	}
	
	protected function _moveUpdate( $unzip_path, $extension_code ) {
		/* @var $path_upload string */
		$path_upload = 'upload';
		
		if( $extension_code == 'mega_filter_plus' ) {
			$path_upload = 'upload_MegaFilterPlus';
		}
		
		if( ! $this->check_path( $unzip_path . $path_upload ) ) {
			$this->error[] = 'Update move error #1';
			
			return $this->_rollbackUpdateChanges();
		}
		
		/* @var $files array */
		$files = $this->scanPath( $unzip_path . $path_upload );
		
		/* @var $new_dirs array */
		$new_dirs = array();
		
		/* @var $new_files array */
		$new_files = array();
		
		/* @var $overwrite_files array */
		$overwrite_files = array();
		
		/* @var $file string */
		foreach( $files as $file ) {
			/* @var $destination string */
			$destination = str_replace('\\', '/', substr($file, strlen($unzip_path . $path_upload . '/')));
			
			/* @var $path string */
			if( null == ( $path = $this->convertToFullPath( $destination ) ) ) {
				continue;
			}
			
			if( is_dir( $file ) && ! is_dir( $path ) ) {
				if( mkdir( $path, 0777 ) ) {
					$new_dirs[] = $destination;
				}
			}
			
			if( is_file( $file ) ) {
				if( is_file( $path ) ) {
					$overwrite_files[$destination] = date('Y-m-d H:i:s', filectime( $path ));
				}
				
				if( rename( $file, $path ) ) {
					$new_files[] = $destination;
				}
			}
		}
		
		$this->_removeTemp();
		
		return true;
	}
	
	protected function convertToFullPath( $path ) {
		/* @var $dirs array */
		$dirs = array(
			'admin' => DIR_APPLICATION,
			'catalog' => DIR_CATALOG,
			'image' => DIR_IMAGE,
			'system' => DIR_SYSTEM,
			'vqmod' => DIR_SYSTEM . '../vqmod/',
		);
		
		/* @var $kdir string */
		/* @var $vdir string */
		foreach( $dirs as $kdir => $vdir ) {
			if( substr( $path, 0, strlen( $kdir ) ) == $kdir ) {
				return $vdir . substr( $path, strlen( $kdir ) + 1 );
			}
		}
		
		return null;
	}
	
	protected function _removeTemp() {
		foreach( $this->temp_files as $path ) {
			if( file_exists( $path ) && is_file( $path ) && is_readable( $path ) ) {
				if( @unlink( $path ) === true ) {
					continue;
				}
			}
			
			$this->errors[] = sprintf('Temp file "%s" has not been removed. Please do it manually.', $path);
		}
		
		foreach( array_reverse( $this->temp_dirs ) as $path ) {
			if( file_exists( $path ) && is_dir( $path ) && is_readable( $path ) ) {
				if( count( scandir( $path ) ) == 2 ) {
					if( @rmdir( $path ) === true ) {
						continue;
					}
				}
			}
			
			$this->errors[] = sprintf('Temp dir "%s" has not been removed. Please do it manually.', $path);
		}
	}
	
	protected function _rollbackUpdateChanges() {
		$this->_removeTemp();
		
		return false;
	}

	public function __construct( $registry, $extensions = array() ) {
		$this->registry = $registry;
		$this->extensions = $extensions;
		
		/* @var $ext string */
		foreach( array( 'curl', 'zip', 'zlib' ) as $ext ) {
			if( ! extension_loaded( $ext ) ) {
				$this->errors[] = 'Missing required PHP extension ' . $ext;
				$this->fatal_error = true;
				break;
			}
		}
		
		if( ! $this->fatal_error ) {
			$this->api = new \mfilter_autoupdate\Api( $registry );
		}
	}
	
	/**
	 * @return \static
	 */
	public static function make( $registry, $extensions = array() ) {
		if( ! self::$instance ) {
			self::$instance = new static( $registry, $extensions );
		}
		
		return self::$instance;
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}
	
}