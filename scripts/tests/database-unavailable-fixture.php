<?php
// Local HTTP fixture: fail at database bootstrap before any session or route runs.
if (PHP_SAPI !== 'cli-server' || !getenv('ELLABO_DB_TEST_LOG')) {
	http_response_code(404);
	exit;
}

$root = dirname(__DIR__, 2);
require $root . '/system/engine/registry.php';
require $root . '/system/library/response.php';
require $root . '/system/library/db/connectionexception.php';

class Config {
	private $data = array(
		'error_filename' => 'unused.log',
		'date_timezone' => 'UTC',
		'error_display' => true,
		'error_log' => true,
		'db_autostart' => true,
		'db_engine' => 'mysqli',
		'db_hostname' => 'private-host',
		'db_username' => 'private-user',
		'db_password' => 'private-password',
		'db_database' => 'private-database',
		'db_port' => 3306
	);
	public function load($name) {}
	public function get($name) { return isset($this->data[$name]) ? $this->data[$name] : null; }
	public function has($name) { return isset($this->data[$name]); }
}
class Log {
	public function __construct($filename) {}
	public function write($message) { file_put_contents(getenv('ELLABO_DB_TEST_LOG'), $message . "\n", FILE_APPEND); }
}
class Event { public function __construct($registry) {} }
class Loader { public function __construct($registry) {} }
class Request {}
class DB {
	public function __construct($engine, $hostname, $username, $password, $database, $port) {
		throw new \DB\ConnectionException(isset($_GET['code']) ? (int)$_GET['code'] : 1226);
	}
}
class Session {
	public function __construct($engine, $registry) {
		throw new RuntimeException('Session must not start after a failed database connection.');
	}
}

$application_config = 'test';
require $root . '/system/framework.php';
