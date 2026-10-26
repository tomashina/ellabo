<?php
// Run: php scripts/tests/patch-generated-mysqli.php
function verify($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}
function runPatch($helper, $target) {
	$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($helper) . ' ' . escapeshellarg($target);
	$process = proc_open($command, array(0 => array('file', '/dev/null', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
	verify(is_resource($process), 'Start patch helper.');
	$output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	return array(proc_close($process), $output);
}
function constructorPrefix($contents) {
	$start = strpos($contents, "\tpublic function __construct(");
	$end = strpos($contents, "\t\t" . '$this->connection->set_charset');
	return substr($contents, $start, $end - $start);
}

$root = dirname(__DIR__, 2);
$helper = $root . '/scripts/patch-generated-mysqli.php';
$legacy = file_get_contents(__DIR__ . '/fixtures/generated-mysqli.php');
$source = file_get_contents($root . '/system/library/db/mysqli.php');
$oldDestructor = "\tpublic function __destruct() {\n\t\t" . '$this->connection->close();' . "\n\t}";
$newDestructor = "\tpublic function __destruct() {\n\t\tif (" . '$this->connection' . ") {\n\t\t\t" . '$this->connection->close();' . "\n\t\t}\n\t}";
$expected = str_replace(array(constructorPrefix($legacy), $oldDestructor), array(constructorPrefix($source), $newDestructor), $legacy);
$tempDir = sys_get_temp_dir() . '/ellabo-mysqli-patch-' . uniqid();
mkdir($tempDir, 0700);
$target = $tempDir . '/generated mysqli.php';

try {
	file_put_contents($target, $legacy);
	chmod($target, 0750);
	list($status, $output) = runPatch($helper, $target);
	verify($status === 0, 'Known generated file patches successfully: ' . $output);
	verify(file_get_contents($target) === $expected, 'Change only the source constructor/destructor, preserving every OCMOD byte.');
	verify(substr_count(file_get_contents($target), 'SET SESSION group_concat_max_len = 65535') === 1, 'Preserve the generated group_concat setting.');
	clearstatcache(true, $target);
	verify((fileperms($target) & 07777) === 0750, 'Preserve file mode.');
	$inode = fileinode($target);
	list($status, $output) = runPatch($helper, $target);
	clearstatcache(true, $target);
	verify($status === 0 && strpos($output, 'already patched') !== false, 'Recognize an already patched file.');
	verify(file_get_contents($target) === $expected && fileinode($target) === $inode, 'Idempotent run must not rewrite the file.');

	$unknown = str_replace('connect_error', 'unexpected_connect_error', $legacy);
	$mixed = str_replace($oldDestructor, $newDestructor, $legacy);
	$invalidPhp = $legacy . "\nthis is invalid PHP;\n";
	foreach (array($unknown, $mixed, $invalidPhp) as $contents) {
		file_put_contents($target, $contents);
		list($status, $output) = runPatch($helper, $target);
		verify($status !== 0, 'Refuse unknown/mixed blocks and invalid patched PHP.');
		verify(file_get_contents($target) === $contents, 'Failure must leave the target unchanged.');
		verify(count(glob($tempDir . '/.mysqli-patch-*')) === 0, 'Failure must remove temporary files.');
	}
} finally {
	foreach (glob($tempDir . '/*') as $file) {
		unlink($file);
	}
	rmdir($tempDir);
}

echo "PASS: generated MySQLi patch, OCMOD preservation, mode, idempotency, unknown/mixed refusal, lint failure and cleanup.\n";
