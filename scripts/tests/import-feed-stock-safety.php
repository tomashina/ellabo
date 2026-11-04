<?php
// Run with: php scripts/tests/import-feed-stock-safety.php
// Real controller/import parser/collections; product reads and all SQL are in-memory stubs.
namespace Agmedia\Models\Product {
	class Product {
		public static $codes = array();
		public static $calls = 0;

		public static function pluck($field) {
			if ($field !== 'model') {
				throw new \RuntimeException('Unexpected product read in fixture.');
			}
			self::$calls++;
			return collect(self::$codes);
		}
	}
}

namespace {
	$temporary = sys_get_temp_dir() . '/ellabo-stock-safe-' . uniqid('', true);
	mkdir($temporary . '/xml', 0700, true);
	mkdir($temporary . '/storage', 0700);
	define('DIR_ROOT', $temporary . '/');
	define('DIR_STORAGE', $temporary . '/storage/');
	define('DB_PREFIX', 'synthetic_');
	define('IMPORT_XML_KEY', 'SYNTHETIC-TEST-KEY');
	define('IMPORT_XML_STANJE', 'https://www.ellabo.hr/xml/xml_ellabo_artikli1.xml');

	// Ignore PHP-version notices from legacy vendor signatures; every runtime
	// warning/notice still fails this test instead of being hidden in JSON output.
	set_error_handler(function($severity, $message, $file, $line) {
		if ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED) {
			return true;
		}
		if (!(error_reporting() & $severity)) {
			return false;
		}
		throw new \ErrorException($message, 0, $severity, $file, $line);
	});
	require dirname(__DIR__, 2) . '/vendor/autoload.php';
	require dirname(__DIR__, 2) . '/system/engine/registry.php';
	require dirname(__DIR__, 2) . '/system/engine/controller.php';
	require dirname(__DIR__, 2) . '/system/library/importfeed.php';
	require dirname(__DIR__, 2) . '/admin/controller/extension/module/product_quick_edit.php';
	require dirname(__DIR__, 2) . '/catalog/controller/extension/module/ag_specials_cron.php';

	class StockSafetyDatabase {
		public $queries = array();
		public function query($sql) {
			$this->queries[] = $sql;
			return (object)array('num_rows' => 1, 'row' => array(), 'rows' => array());
		}
		public function escape($value) {
			return addslashes($value);
		}
	}
	class StockSafetyResponse {
		public $headers = array();
		public $output = '';
		public function addHeader($header) { $this->headers[] = $header; }
		public function setOutput($output) { $this->output = $output; }
	}
	function stockExpect($condition, $message) {
		if (!$condition) throw new \RuntimeException($message);
	}
	function stockProduct($code) {
		return '<PRODUCT><Sifra>' . $code . '</Sifra><Raspolozivo>7</Raspolozivo><MlpOsnovica>10.00</MlpOsnovica><MlpCijena>12.50</MlpCijena></PRODUCT>';
	}
	function stockRun($document, array $codes) {
		global $temporary;
		$file = $temporary . '/xml/xml_ellabo_artikli1.xml';
		if ($document === null) {
			if (file_exists($file)) unlink($file);
		} else {
			file_put_contents($file, $document);
		}
		\Agmedia\Models\Product\Product::$codes = $codes;
		\Agmedia\Models\Product\Product::$calls = 0;
		$db = new StockSafetyDatabase();
		$response = new StockSafetyResponse();
		$request = (object)array('get' => array('key' => IMPORT_XML_KEY));
		$registry = new \Registry();
		$registry->set('request', $request);
		$registry->set('response', $response);
		$registry->set('db', $db);
		$class = new \ReflectionClass('ControllerExtensionModuleProductQuickEdit');
		$controller = $class->newInstanceWithoutConstructor();
		$property = new \ReflectionProperty('Controller', 'registry');
		$property->setAccessible(true);
		$property->setValue($controller, $registry);
		ob_start();
		try {
			$controller->updateStockFromXML();
		} finally {
			$output = ob_get_clean();
		}
		stockExpect($output === '', 'Import failures must not emit warnings or arbitrary response content.');
		$lock = fopen(DIR_STORAGE . 'locks/agmedia_update_stock.lock', 'c');
		stockExpect(flock($lock, LOCK_EX | LOCK_NB), 'Every completed import attempt must release its lock.');
		flock($lock, LOCK_UN);
		fclose($lock);
		return (object)array('db' => $db, 'response' => $response, 'reads' => \Agmedia\Models\Product\Product::$calls);
	}
	function stockRemove($path) {
		if (is_dir($path) && !is_link($path)) {
			foreach (array_diff(scandir($path), array('.', '..')) as $entry) stockRemove($path . '/' . $entry);
			rmdir($path);
		} else {
			unlink($path);
		}
	}
	function specialsRejectBeforeSql($controller_name) {
		global $temporary;
		file_put_contents($temporary . '/xml/xml_ellabo_artikli1.xml', '<PRODUCTS>' . stockProduct('SKU-A') . stockProduct('SKU-B') . '</PRODUCTS>');
		file_put_contents($temporary . '/xml/xml_ellabo_artikli11.xml', '<html><body>Cloudflare challenge</body></html>');
		$db = new StockSafetyDatabase();
		$response = new StockSafetyResponse();
		$registry = new \Registry();
		$registry->set('db', $db);
		$registry->set('response', $response);
		$controller = (new \ReflectionClass($controller_name))->newInstanceWithoutConstructor();
		$property = new \ReflectionProperty('Controller', 'registry');
		$property->setAccessible(true);
		$property->setValue($controller, $registry);
		$method = new \ReflectionMethod($controller_name, 'runUnifiedSpecialsUpdate');
		$method->setAccessible(true);
		$result = $method->invoke($controller, array(IMPORT_XML_STANJE, 'https://www.ellabo.hr/xml/xml_ellabo_artikli11.xml?key=SYNTHETIC-PRIVATE-QUERY'));
		stockExpect($db->queries === array(), 'Both specials controllers must validate every feed before transaction, staging, writes or ROLLBACK.');
		stockExpect(in_array('HTTP/1.1 503 Service Unavailable', $response->headers, true), 'Rejected specials feed must return controlled HTTP 503.');
		stockExpect(is_array($result) && isset($result['error']), 'Rejected specials feed must return a structured error.');
		stockExpect(strpos(json_encode($result), 'SYNTHETIC-PRIVATE-QUERY') === false, 'Specials errors must not disclose configured feed secrets.');
		stockExpect(strpos(json_encode($result), $temporary) === false, 'Specials errors must not disclose local file paths.');
	}

	try {
		foreach (array(null, '<html><body>Cloudflare challenge</body></html>', '<PRODUCTS/>', '<PRODUCTS><PRODUCT>') as $document) {
			$result = stockRun($document, array('SKU-A'));
			stockExpect($result->db->queries === array(), 'Invalid/missing feed must perform zero SQL writes, including schema changes and disable-all.');
			stockExpect($result->reads === 0, 'Validate the feed before querying existing product models.');
			stockExpect(in_array('HTTP/1.1 503 Service Unavailable', $result->response->headers, true), 'Invalid feed must return controlled HTTP 503.');
			stockExpect(isset(json_decode($result->response->output, true)['error']), 'Invalid feed must return a JSON error.');
		}
		echo "PASS missing, HTML, empty and malformed stock feeds perform zero SQL and return controlled JSON 503\n";

		foreach (array(
			array('<PRODUCTS>' . stockProduct('SKU-A') . '</PRODUCTS>', array('SKU-A')),
			array('<PRODUCTS>' . stockProduct('SKU-A') . stockProduct('SKU-B') . '</PRODUCTS>', array('UNMATCHED-SKU'))
		) as $fixture) {
			$result = stockRun($fixture[0], $fixture[1]);
			stockExpect($result->db->queries === array(), 'Singleton/unmatched collection must not stage rows, change schema or disable products.');
			stockExpect(in_array('HTTP/1.1 503 Service Unavailable', $result->response->headers, true), 'An empty matched collection must return HTTP 503.');
			stockExpect(isset(json_decode($result->response->output, true)['error']), 'An empty matched collection must return a JSON error.');
			if (count($fixture[1]) === 1 && $fixture[1][0] === 'SKU-A') {
				stockExpect($result->reads === 0, 'Reject singleton feeds before product reads because legacy ImportXML cannot collect them safely.');
			}
		}
		echo "PASS singleton and unmatched product collections leave the catalog unchanged and release the import lock\n";

		$result = stockRun('<PRODUCTS>' . stockProduct('SKU-A') . stockProduct('SKU-B') . '</PRODUCTS>', array('SKU-A'));
		stockExpect(!in_array('HTTP/1.1 503 Service Unavailable', $result->response->headers, true), 'A valid matched multi-product feed retains the successful path.');
		stockExpect(count($result->db->queries) > 0, 'A valid matched feed must reach the in-memory staging stub.');
		stockExpect(count(array_filter($result->db->queries, function($sql) { return strpos($sql, 'SET status = 0') !== false; })) === 1, 'The successful fixture exercises the original stock update sequence without a real database.');
		echo "PASS valid matched feeds still reach the stock update sequence through in-memory SQL stubs only\n";

		specialsRejectBeforeSql('ControllerExtensionModuleProductQuickEdit');
		specialsRejectBeforeSql('ControllerExtensionModuleAgSpecialsCron');
		echo "PASS both specials controllers validate all feeds before any SQL and return sanitized HTTP 503 errors\n";
	} finally {
		restore_error_handler();
		stockRemove($temporary);
	}
}
