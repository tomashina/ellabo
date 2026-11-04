<?php
// Run: php scripts/tests/catalog-request-gate.php
$root = dirname(__DIR__, 2);
require $root . '/system/library/catalogrequestgate.php';

if (isset($argv[1]) && $argv[1] === '--hold') {
	$gate = CatalogRequestGate::acquire($argv[2], 'concurrency');
	echo "locked\n";
	fflush(STDOUT);
	fgets(STDIN);
	exit(0); // Request exit must reclaim the lock without an explicit release.
}
function verifyGate($condition, $message) {
	if (!$condition) { throw new RuntimeException($message); }
}
function fixtureRequest($directory, $application, $mode) {
	$eventFile = $directory . '/events-' . uniqid() . '.log';
	$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/catalog-request-gate-fixture.php');
	foreach (array($directory, $eventFile, $application, $mode, '1') as $argument) { $command .= ' ' . escapeshellarg($argument); }
	$process = proc_open($command, array(0 => array('file', '/dev/null', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
	verifyGate(is_resource($process), 'Start framework fixture.');
	$output = stream_get_contents($pipes[1]);
	$error = stream_get_contents($pipes[2]);
	fclose($pipes[1]); fclose($pipes[2]);
	$status = proc_close($process);
	verifyGate($status === 0 && $error === '', 'Framework fixture failed: ' . $error);
	$events = is_file($eventFile) ? file_get_contents($eventFile) : '';
	return array(json_decode($output, true), $events);
}
function removeGateDirectory($directory) {
	foreach (scandir($directory) as $name) {
		if ($name === '.' || $name === '..') { continue; }
		$path = $directory . '/' . $name;
		if (is_dir($path)) { removeGateDirectory($path); } else { unlink($path); }
	}
	rmdir($directory);
}

$directory = sys_get_temp_dir() . '/ellabo-gate-test-' . uniqid();
mkdir($directory, 0700);
$children = array();
try {
	$warnings = array();
	set_error_handler(function($code, $message) use (&$warnings) { $warnings[] = $message; return true; });
	ob_start();
	file_put_contents($directory . '/not-a-directory', 'fixture');
	try {
		CatalogRequestGate::acquire($directory . '/not-a-directory', 'storage-error');
		throw new RuntimeException('Unavailable lock storage must refuse admission.');
	} catch (CatalogRequestGateException $e) {
		verifyGate($e->getMessage() === 'Catalog request lock storage unavailable.', 'Report storage failure safely.');
	} finally {
		$output = ob_get_clean();
		trigger_error('handler restored', E_USER_WARNING);
		restore_error_handler();
	}
	verifyGate($output === '' && $warnings === array('handler restored'), 'Suppress filesystem diagnostics and restore the previous handler.');
	try {
		CatalogRequestGate::acquire($directory, 'invalid-limit', 0);
		throw new RuntimeException('Invalid limit must refuse admission.');
	} catch (CatalogRequestGateException $e) {
		verifyGate($e->getMessage() === 'Invalid catalog request limit.', 'Reject invalid configuration.');
	}
	for ($slot = 0; $slot < 12; $slot++) {
		$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --hold ' . escapeshellarg($directory . '/concurrent');
		$process = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
		verifyGate(is_resource($process), 'Start concurrent request.');
		$children[] = array($process, $pipes);
		verifyGate(fgets($pipes[1]) === "locked\n", 'Each of twelve concurrent requests must acquire its own slot.');
	}
	$started = microtime(true);
	try {
		CatalogRequestGate::acquire($directory . '/concurrent', 'concurrency');
		throw new RuntimeException('Thirteenth request must be refused.');
	} catch (CatalogRequestGateException $e) {
		verifyGate($e->getMessage() === 'Catalog request limit reached.', 'Refuse exhausted slots.');
	}
	verifyGate(microtime(true) - $started < 0.5, 'Admission must be nonblocking.');
	foreach ($children as $child) { fwrite($child[1][0], "exit\n"); fclose($child[1][0]); }
	foreach ($children as $child) {
		fclose($child[1][1]); fclose($child[1][2]);
		verifyGate(proc_close($child[0]) === 0, 'Concurrent request exits cleanly.');
	}
	$children = array();
	$reused = CatalogRequestGate::acquire($directory . '/concurrent', 'concurrency');
	$reused->release(); $reused->release();
	verifyGate(count(glob($directory . '/concurrent/catalog-db-*/*.lock')) === 12, 'Stale files remain harmless and slots are reusable.');

	$identity = 'GateTest|localhost|3306|fixture-user|fixture-db';
	$held = CatalogRequestGate::acquire($directory . '/locks', $identity, 1);
	foreach (array('normal', 'cron', 'xml') as $mode) {
		list($response, $events) = fixtureRequest($directory, 'catalog', $mode);
		verifyGate(in_array('HTTP/1.1 503 Service Unavailable', $response['headers'], true), 'Saturation returns503 for every catalog route.');
		verifyGate(in_array('Retry-After: 5', $response['headers'], true), 'Saturation supplies Retry-After.');
		verifyGate(json_decode($response['body'], true)['success'] === false, 'Saturation returns clean AJAX JSON.');
		verifyGate(json_decode($response['body'], true)['error'] === 'Webshop je trenutačno opterećen. Pokušajte ponovno za nekoliko trenutaka.', 'Admission refusal explains webshop load.');
		verifyGate(strpos($events, 'db-open') === false && strpos($events, 'session-write') === false, 'Reject before connecting to MySQL or opening sessions.');
	}
	list($response, $events) = fixtureRequest($directory, 'admin', 'normal');
	verifyGate($response['body'] === 'ok' && strpos($events, 'db-open') !== false, 'Admin bypasses saturated catalog admission.');
	$held->release();

	foreach (array('normal', 'exit', 'exception') as $mode) {
		list($response, $events) = fixtureRequest($directory, 'catalog', $mode);
		verifyGate($events === "db-open\nsession-write:locked\npdo-close:locked\ndb-close:locked\nafter-cleanup:unlocked\n", 'Session write, existing PDO close, MySQL close and unlock must retain their order on ' . $mode . ': ' . $events);
	}
	list($response, $events) = fixtureRequest($directory, 'catalog', 'connect-failure');
	verifyGate(in_array('HTTP/1.1 503 Service Unavailable', $response['headers'], true), 'Connection failure remains503.');
	verifyGate(json_decode($response['body'], true)['error'] === 'Baza podataka trenutačno nije dostupna. Pokušajte ponovno za nekoliko trenutaka.', 'Database failure retains its distinct explanation.');
	$probe = CatalogRequestGate::acquire($directory . '/locks', $identity, 1);
	$probe->release();
	verifyGate(strpos($events, 'MySQL error: 1203') !== false, 'Connection failure retains its actual numeric diagnostic.');
} finally {
	foreach ($children as $child) {
		foreach ($child[1] as $pipe) { if (is_resource($pipe)) { fclose($pipe); } }
		if (is_resource($child[0])) { proc_terminate($child[0]); proc_close($child[0]); }
	}
	removeGateDirectory($directory);
}
echo "PASS: 12 process concurrency, pre-DB saturation, all catalog routes, admin bypass, session/PDO/MySQL shutdown order, exits, exceptions and stale locks.\n";
