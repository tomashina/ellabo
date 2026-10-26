<?php
// CLI deployment helper. Only replace the two known legacy blocks; retain OCMOD.
if (PHP_SAPI !== 'cli' || $argc !== 2) {
	fwrite(STDERR, "Usage: php scripts/patch-generated-mysqli.php /path/to/generated/system/library/db/mysqli.php\n");
	exit(2);
}

$target = $argv[1];
$temporary = null;
$status = 0;

try {
	if (is_link($target) || !is_file($target)) {
		throw new RuntimeException('Target must be an existing regular file, not a symlink.');
	}
	$contents = file_get_contents($target);
	if ($contents === false) {
		throw new RuntimeException('Cannot read the generated MySQLi file.');
	}

	$oldConnection = <<<'PHP'
	public function __construct($hostname, $username, $password, $database, $port = '3306') {
		$this->connection = new \mysqli($hostname, $username, $password, $database, $port);

		if ($this->connection->connect_error) {
			throw new \Exception('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno);
		}

PHP;
	$newConnection = <<<'PHP'
	public function __construct($hostname, $username, $password, $database, $port = '3306') {
		// Connection warnings contain account details and would corrupt AJAX JSON.
		// A local handler also works with OpenCart's custom warning handler.
		set_error_handler(function() {
			return true;
		});

		try {
			$connection = new \mysqli($hostname, $username, $password, $database, $port);
		} catch (\mysqli_sql_exception $e) {
			throw new ConnectionException($e->getCode());
		} finally {
			restore_error_handler();
		}

		if ($connection->connect_errno) {
			throw new ConnectionException($connection->connect_errno);
		}

		$this->connection = $connection;
PHP;
	$oldDestructor = <<<'PHP'
	public function __destruct() {
		$this->connection->close();
	}
PHP;
	$newDestructor = <<<'PHP'
	public function __destruct() {
		if ($this->connection) {
			$this->connection->close();
		}
	}
PHP;

	$oldCounts = array(substr_count($contents, $oldConnection), substr_count($contents, $oldDestructor));
	$newCounts = array(substr_count($contents, $newConnection), substr_count($contents, $newDestructor));
	if ($oldCounts === array(0, 0) && $newCounts === array(1, 1)) {
		fwrite(STDOUT, "Generated MySQLi is already patched; unchanged.\n");
		exit(0);
	}
	if ($oldCounts !== array(1, 1) || $newCounts !== array(0, 0)) {
		throw new RuntimeException('Unknown or partially patched MySQLi blocks; no changes made.');
	}

	// Match both blocks before creating a temporary file or touching the target.
	$patched = str_replace(array($oldConnection, $oldDestructor), array($newConnection, $newDestructor), $contents);
	$permissions = fileperms($target);
	if ($permissions === false) {
		throw new RuntimeException('Cannot read target permissions.');
	}
	$temporary = tempnam(dirname($target), '.mysqli-patch-');
	if ($temporary === false || dirname($temporary) !== realpath(dirname($target))) {
		throw new RuntimeException('Cannot create a temporary file beside the target.');
	}
	if (file_put_contents($temporary, $patched) !== strlen($patched)) {
		throw new RuntimeException('Cannot write the complete patched file.');
	}
	$lintOutput = array();
	$lintStatus = 1;
	exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($temporary) . ' 2>&1', $lintOutput, $lintStatus);
	if ($lintStatus !== 0) {
		throw new RuntimeException('Patched MySQLi failed PHP syntax validation; target unchanged.');
	}
	if (!chmod($temporary, $permissions & 07777)) {
		throw new RuntimeException('Cannot preserve target permissions.');
	}
	if (file_get_contents($target) !== $contents || is_link($target)) {
		throw new RuntimeException('Target changed during validation; target not replaced.');
	}
	if (!rename($temporary, $target)) {
		throw new RuntimeException('Cannot atomically replace the generated MySQLi file.');
	}
	$temporary = null;
	fwrite(STDOUT, "Patched generated MySQLi constructor and destructor; OCMOD settings preserved.\n");
} catch (Throwable $e) {
	fwrite(STDERR, $e->getMessage() . "\n");
	$status = 1;
} finally {
	if ($temporary !== null && is_file($temporary)) {
		unlink($temporary);
	}
}

exit($status);
