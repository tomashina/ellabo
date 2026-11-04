<?php
namespace DB {
	class GateTest {
		public function __construct($hostname, $username, $password, $database, $port) {
			if ($GLOBALS['mode'] === 'connect-failure') {
				throw new ConnectionException(1203);
			}
			\recordGateEvent('db-open');
		}
		public function query($sql) { \recordGateEvent('session-write:' . (\gateOccupied() ? 'locked' : 'unlocked')); }
		public function __destruct() { \recordGateEvent('db-close:' . (\gateOccupied() ? 'locked' : 'unlocked')); }
	}
}
namespace {
	// Standalone CLI fixture; no shop configuration or network access.
	if (PHP_SAPI !== 'cli' || $argc !== 6) { exit(2); }
	$root = dirname(__DIR__, 2);
	define('DIR_SYSTEM', $root . '/system/');
	define('DIR_STORAGE', $argv[1] . '/');
	define('HTTP_CATALOG', 'fixture');
	$eventFile = $argv[2];
	$application_config = $argv[3];
	$mode = $argv[4];
	define('CATALOG_DB_REQUEST_LIMIT', (int)$argv[5]);
	$identity = 'GateTest|localhost|3306|fixture-user|fixture-db';
	require DIR_SYSTEM . 'library/catalogrequestgate.php';
	require DIR_SYSTEM . 'library/db/connectionexception.php';
	require DIR_SYSTEM . 'library/db.php';
	require DIR_SYSTEM . 'engine/registry.php';
	function recordGateEvent($event) { file_put_contents($GLOBALS['eventFile'], $event . "\n", FILE_APPEND); }
	function gateOccupied() {
		try {
			$probe = CatalogRequestGate::acquire(DIR_STORAGE . 'locks', $GLOBALS['identity'], CATALOG_DB_REQUEST_LIMIT);
			$probe->release();
			return false;
		} catch (CatalogRequestGateException $e) { return true; }
	}
	class Config {
		private $data = array('db_autostart' => true, 'db_engine' => 'GateTest', 'db_hostname' => 'localhost', 'db_port' => 3306, 'db_username' => 'fixture-user', 'db_password' => 'fixture-secret', 'db_database' => 'fixture-db', 'date_timezone' => 'UTC', 'error_log' => true, 'error_display' => true, 'url_autostart' => false, 'session_autostart' => false);
		public function load($name) {}
		public function has($name) { return isset($this->data[$name]); }
		public function get($name) { return isset($this->data[$name]) ? $this->data[$name] : null; }
	}
	class Log {
		public function __construct($filename) {}
		public function write($message) { recordGateEvent('log:' . $message); }
	}
	class Event { public function __construct($registry) {} }
	class Loader { public function __construct($registry) {} }
	class Request {}
	class Response {
		private $headers = array();
		private $output = '';
		public function addHeader($header) { $this->headers[] = $header; }
		public function setCompression($level) {}
		public function setOutput($output) { $this->output = $output; }
		public function output() { echo json_encode(array('headers' => $this->headers, 'body' => $this->output)); }
	}
	class Session {
		public function __construct($engine, $registry) {
			register_shutdown_function(function() use ($registry) { $registry->get('db')->query('SESSION WRITE'); });
		}
	}
	class Cache { public function __construct($engine, $expire) {} }
	class Language { public function __construct($directory) {} }
	class Document {}
	class FixturePdoConnection {
		public function disconnect() { recordGateEvent('pdo-close:' . (gateOccupied() ? 'locked' : 'unlocked')); }
	}
	class FixtureCapsule {
		public function getDatabaseManager() { return $this; }
		public function getConnections() { return array(new FixturePdoConnection()); }
	}
	$capsule = new FixtureCapsule();
	class Router {
		private $registry;
		public function __construct($registry) { $this->registry = $registry; }
		public function dispatch($route, $error) {
			$registry = $this->registry;
			if ($GLOBALS['application_config'] === 'catalog') {
				register_shutdown_function(function() use ($registry) {
					recordGateEvent('after-cleanup:' . (gateOccupied() ? 'locked' : 'unlocked'));
					$registry->get('db')->close(); // Explicit close remains idempotent.
				});
			}
			if ($GLOBALS['mode'] === 'exit') { exit(0); }
			if ($GLOBALS['mode'] === 'exception') { throw new RuntimeException('Expected fixture route failure.'); }
			$registry->get('response')->setOutput('ok');
		}
	}
	class Action { public function __construct($route) {} }
	$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
	if ($mode === 'cron') { $_GET = array('route' => 'extension/module/ag_specials_cron/updateSpecialPricesUnified', 'key' => 'fixture'); }
	if ($mode === 'xml') { $_GET = array('route' => 'extension/module/xml_export_order', 'key' => 'fixture'); }
	try {
		require DIR_SYSTEM . 'framework.php';
	} catch (RuntimeException $e) {
		if ($mode !== 'exception') { throw $e; }
	}
}
