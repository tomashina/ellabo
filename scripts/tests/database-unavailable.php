<?php
// Run: php scripts/tests/database-unavailable.php
// Uses only a refused loopback connection and a local HTTP fixture; no shop config.
error_reporting(E_ALL);

function check($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

$root = dirname(__DIR__, 2);
spl_autoload_register(function($class) use ($root) {
	// Match OpenCart's case-folding autoloader, including ConnectionException.
	$file = $root . '/system/library/' . str_replace('\\', '/', strtolower($class)) . '.php';
	if (is_file($file)) {
		require_once $file;
	}
});

$driver = new mysqli_driver();
$reportMode = $driver->report_mode;

try {
	foreach (array(MYSQLI_REPORT_OFF, MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT) as $mode) {
		mysqli_report($mode);
		$warnings = array();
		set_error_handler(function($code, $message) use (&$warnings) {
			$warnings[] = $message;
			return true;
		});
		ob_start();
		try {
			new \DB\MySQLi('127.0.0.1', 'private-user', 'private-password', 'private-database', 1);
			throw new RuntimeException('The refused connection unexpectedly succeeded.');
		} catch (\DB\ConnectionException $e) {
			check($e->getCode() === 2002, 'Preserve the numeric MySQL connection error.');
			check($e->getMessage() === 'Database connection unavailable.', 'Sanitize the connection exception.');
			check($e->getPrevious() === null, 'Do not retain driver text containing account details.');
		} finally {
			$output = ob_get_clean();
			trigger_error('handler restored', E_USER_WARNING);
			restore_error_handler();
		}
		check($output === '', 'Connection failure must not print PHP warnings.');
		check($warnings === array('handler restored'), 'Suppress only connection warnings and restore the previous handler.');
	}
} finally {
	mysqli_report($reportMode);
}

$tempDir = sys_get_temp_dir() . '/ellabo-db-test-' . uniqid();
mkdir($tempDir, 0700);
$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
check($socket !== false, 'Allocate a local test port.');
$address = stream_socket_get_name($socket, false);
fclose($socket);
$process = null;

try {
	$environment = getenv();
	$environment['ELLABO_DB_TEST_LOG'] = $tempDir . '/bootstrap.log';
	$command = escapeshellarg(PHP_BINARY) . ' -S ' . escapeshellarg($address) . ' ' . escapeshellarg(__DIR__ . '/database-unavailable-fixture.php');
	$process = proc_open($command, array(
		0 => array('file', '/dev/null', 'r'),
		1 => array('file', $tempDir . '/server.log', 'a'),
		2 => array('file', $tempDir . '/server.log', 'a')
	), $pipes, $root, $environment);
	check(is_resource($process), 'Start the local HTTP fixture.');
	$ready = false;
	for ($attempt = 0; $attempt < 100; $attempt++) {
		$probe = @stream_socket_client('tcp://' . $address, $errno, $error, 0.1);
		if ($probe) {
			fclose($probe);
			$ready = true;
			break;
		}
		usleep(20000);
	}
	check($ready, 'Wait for the local HTTP fixture.');

	$message = 'Baza podataka trenutačno nije dostupna. Pokušajte ponovno za nekoliko trenutaka.';
	$requests = array(
		array('X-Requested-With: XMLHttpRequest', true, 1226),
		array('Accept: application/json', true, 1040),
		array('Accept: text/html', false, 1226)
	);
	foreach ($requests as $request) {
		$context = stream_context_create(array('http' => array(
			'ignore_errors' => true,
			'timeout' => 5,
			'header' => $request[0]
		)));
		$body = file_get_contents('http://' . $address . '/?code=' . $request[2], false, $context);
		$headers = implode("\n", $http_response_header);
		check(strpos($headers, '503 Service Unavailable') !== false, 'Return HTTP 503 for exhausted database capacity.');
		check(stripos($headers, 'Retry-After: 5') !== false, 'Supply a bounded manual retry hint.');
		check(stripos($headers, 'Cache-Control: no-store') !== false, 'Do not cache the failure response.');
		if ($request[1]) {
			check(stripos($headers, 'Content-Type: application/json; charset=utf-8') !== false, 'Send JSON for jQuery and JSON clients.');
			check(json_decode($body, true) === array('success' => false, 'error' => $message), 'Send a clean, actionable JSON error.');
		} else {
			check(stripos($headers, 'Content-Type: text/plain; charset=utf-8') !== false, 'Send plain text for page navigation.');
			check($body === $message, 'Send a clean, actionable page response.');
		}
		check(!preg_match('/private-|mysqli|Warning|SELECT|INSERT|UPDATE/', $body), 'Do not expose account details, SQL or PHP diagnostics.');
	}
	check(file_get_contents($tempDir . '/bootstrap.log') === "Database connection failed. MySQL error: 1226\nDatabase connection failed. MySQL error: 1040\nDatabase connection failed. MySQL error: 1226\n", 'Log each failure once with its numeric MySQL error only.');
} finally {
	if (is_resource($process)) {
		proc_terminate($process);
		proc_close($process);
	}
	foreach (glob($tempDir . '/*') as $file) {
		unlink($file);
	}
	rmdir($tempDir);
}

echo "PASS: warning/exception connection failures, autoloading, handler restoration, HTTP 503, JSON/text, and sanitized logging.\n";
