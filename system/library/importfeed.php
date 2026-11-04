<?php
/** Load configured import feeds without requesting our local mirrors through HTTP. */
class ImportFeedException extends RuntimeException {}

class ImportFeed {
	const MAX_BYTES = 67108864;

	public static function load($url, $profile = 'stock') {
		if (!is_string($url) || preg_match('/[\r\n\x00]/', $url)) {
			throw new ImportFeedException('Import feed location is invalid.');
		}
		$parts = parse_url($url);
		if (!$parts || empty($parts['host']) || !isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), array('http', 'https'), true)) {
			throw new ImportFeedException('Import feed must use an HTTP or HTTPS location.');
		}

		if (strtolower(rtrim($parts['host'], '.')) === 'www.ellabo.hr') {
			$allowed = array('/xml/xml_ellabo_artikli1.xml', '/xml/xml_ellabo_artikli2.xml', '/xml/xml_ellabo_artikli11.xml', '/xml/xml_ellabo_artikli12.xml');
			$default_port = strtolower($parts['scheme']) === 'https' ? 443 : 80;
			if (!isset($parts['path']) || !in_array($parts['path'], $allowed, true) || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment']) || (isset($parts['port']) && $parts['port'] !== $default_port)) {
				throw new ImportFeedException('Local import feed location is not supported.');
			}
			$contents = self::readLocal(basename($parts['path']));
		} else {
			$contents = self::readRemote($url);
		}

		return self::parse($contents, $profile);
	}

	private static function readLocal($name) {
		$root = defined('DIR_ROOT') ? realpath(DIR_ROOT) : false;
		$directory = $root !== false ? realpath($root . '/xml') : false;
		$path = $directory !== false ? realpath($directory . '/' . $name) : false;
		if ($root === false || $directory === false || strpos($directory . DIRECTORY_SEPARATOR, $root . DIRECTORY_SEPARATOR) !== 0 || $path === false || strpos($path, $directory . DIRECTORY_SEPARATOR) !== 0 || !is_file($path) || !is_readable($path)) {
			throw new ImportFeedException('Local import feed is missing, unreadable or outside the mirror directory.');
		}
		$size = self::quietly(function() use ($path) { return filesize($path); });
		if ($size === false || $size > self::MAX_BYTES) {
			throw new ImportFeedException('Local import feed exceeds the supported size.');
		}
		$contents = self::quietly(function() use ($path) {
			$handle = fopen($path, 'rb');
			if ($handle === false) {
				return false;
			}
			try {
				return stream_get_contents($handle, self::MAX_BYTES + 1);
			} finally {
				fclose($handle);
			}
		});
		if ($contents === false) {
			throw new ImportFeedException('Local import feed could not be read.');
		}
		if (strlen($contents) > self::MAX_BYTES) {
			throw new ImportFeedException('Local import feed exceeds the supported size.');
		}
		return $contents;
	}

	private static function readRemote($url) {
		if (!function_exists('curl_init')) {
			throw new ImportFeedException('Remote import feed transport is unavailable.');
		}
		$contents = '';
		$oversized = false;
		$handle = curl_init($url);
		try {
			curl_setopt_array($handle, array(
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
				CURLOPT_USERAGENT => 'Ellabo import feed loader',
				CURLOPT_WRITEFUNCTION => function($handle, $chunk) use (&$contents, &$oversized) {
					if (strlen($contents) + strlen($chunk) > self::MAX_BYTES) {
						$oversized = true;
						return 0;
					}
					$contents .= $chunk;
					return strlen($chunk);
				}
			));
			$success = curl_exec($handle);
			$status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
			if ($oversized) {
				throw new ImportFeedException('Remote import feed exceeds the supported size.');
			}
			if ($success === false || $status !== 200) {
				throw new ImportFeedException('Remote import feed could not be downloaded successfully.');
			}
		} finally {
			curl_close($handle);
		}
		return $contents;
	}

	private static function parse($contents, $profile) {
		$fields = array('Sifra', 'Raspolozivo', 'MlpOsnovica', 'MlpCijena');
		$numeric = array('Raspolozivo', 'MlpOsnovica', 'MlpCijena');
		if ($profile === 'specials') {
			$fields[] = 'MlpCijenaOld';
			$numeric[] = 'MlpCijenaOld';
		} elseif ($profile === 'products') {
			$fields = array_merge($fields, array('Naziv', 'BarCode', 'JedMjere', 'DrzavaPorijeklo', 'KolPaket', 'Aktivno', 'WebView'));
		} elseif ($profile !== 'stock') {
			throw new ImportFeedException('Import feed schema is not supported.');
		}
		if ($contents === '' || strpos($contents, "\0") !== false || stripos($contents, '<!DOCTYPE') !== false || stripos($contents, '<!ENTITY') !== false) {
			throw new ImportFeedException('Import feed must contain product XML without document type declarations.');
		}
		$previous = libxml_use_internal_errors(true);
		try {
			$xml = simplexml_load_string($contents, 'SimpleXMLElement', LIBXML_NONET | LIBXML_COMPACT);
			if ($xml === false || $xml->getName() !== 'PRODUCTS' || count($xml->getDocNamespaces(true))) {
				throw new ImportFeedException('Import feed must contain a PRODUCTS root with product records.');
			}
			// ImportXML::collect() treats a singleton as a field map, not rows.
			// Refuse that shape before any products, stock or specials are changed.
			if (count($xml->PRODUCT) < 2) {
				throw new ImportFeedException('Import feed must contain at least two PRODUCT records for safe import.');
			}
			foreach ($xml->PRODUCT as $product) {
				foreach ($fields as $field) {
					if (count($product->{$field}) !== 1 || count($product->{$field}->children())) {
						throw new ImportFeedException('Import feed PRODUCT is missing required fields.');
					}
				}
				if (trim((string)$product->Sifra) === '') {
					throw new ImportFeedException('Import feed PRODUCT has no product code.');
				}
				foreach ($numeric as $field) {
					if (!is_numeric(str_replace(',', '.', trim((string)$product->{$field})))) {
						throw new ImportFeedException('Import feed PRODUCT has invalid stock or price values.');
					}
				}
			}
			return $xml;
		} finally {
			libxml_clear_errors();
			libxml_use_internal_errors($previous);
		}
	}

	private static function quietly($callback) {
		set_error_handler(function() { return true; });
		try {
			return $callback();
		} finally {
			restore_error_handler();
		}
	}
}
