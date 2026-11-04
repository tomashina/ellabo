<?php
// Apply only reviewed source deltas to OCMOD overrides, preserving extensions.
if (PHP_SAPI !== 'cli' || $argc < 3 || !in_array($argv[2], array('--check', '--apply'), true)) {
	fwrite(STDERR, "Usage: php scripts/sync-modification-overrides.php DOCUMENT_ROOT --check|--apply [BACKUP_DIRECTORY]\n");
	exit(2);
}

$root = rtrim($argv[1], '/');
$apply = $argv[2] === '--apply';
$backup = isset($argv[3]) ? rtrim($argv[3], '/') : null;
$patches = array(
	'system/library/db.php' => 'database-close.patch',
	'catalog/controller/startup/seo_url.php' => 'category-path.patch'
);
$temporary = array();

function overrideCommand($command, &$output) {
	$lines = array();
	$status = 1;
	exec($command . ' 2>&1', $lines, $status);
	$output = implode("\n", $lines);
	return $status;
}

try {
	$plans = array();
	foreach ($patches as $relative => $patchName) {
		$target = $root . '/system/storage/modification/' . $relative;
		if (!file_exists($target)) {
			continue;
		}
		if (is_link($target) || !is_file($target)) {
			throw new RuntimeException('Refusing non-regular override: ' . $relative);
		}
		$before = file_get_contents($target);
		$staged = tempnam(sys_get_temp_dir(), 'ellabo-override-');
		$temporary[] = $staged;
		if ($before === false || $staged === false || file_put_contents($staged, $before) !== strlen($before)) {
			throw new RuntimeException('Cannot stage override: ' . $relative);
		}
		$patch = __DIR__ . '/patches/' . $patchName;
		$arguments = ' --batch --silent --fuzz=0 --no-backup-if-mismatch --reject-file=- ' . escapeshellarg($staged) . ' ' . escapeshellarg($patch);
		if (overrideCommand('patch --forward' . $arguments, $output) !== 0) {
			file_put_contents($staged, $before);
			if (overrideCommand('patch --reverse --dry-run' . $arguments, $output) === 0) {
				echo 'Override already current: ' . $relative . "\n";
				continue;
			}
			throw new RuntimeException('Override differs from reviewed patch; deployment stopped: ' . $relative);
		}
		if (overrideCommand(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($staged), $output) !== 0) {
			throw new RuntimeException('Override syntax validation failed: ' . $relative);
		}
		$plans[] = array($relative, $target, $before, file_get_contents($staged), fileperms($target) & 0777);
	}

	// Validate every override before making the first change.
	if ($apply && count($plans)) {
		$backupPath = $backup !== null ? realpath($backup) : false;
		$rootPath = realpath($root);
		if ($backupPath === false || $rootPath === false || !is_dir($backupPath) || strpos($backupPath . '/', $rootPath . '/') === 0) {
			throw new RuntimeException('An existing outside-webroot backup directory is required.');
		}
		$backup = $backupPath;
	}
	foreach ($plans as $plan) {
		list($relative, $target, $before, $after, $mode) = $plan;
		if (!$apply) {
			echo 'Override ready: ' . $relative . "\n";
			continue;
		}
		if (file_get_contents($target) !== $before || is_link($target)) {
			throw new RuntimeException('Override changed during deployment: ' . $relative);
		}
		$backupFile = $backup . '/modification/' . $relative;
		if (!is_dir(dirname($backupFile)) && !mkdir(dirname($backupFile), 0700, true) && !is_dir(dirname($backupFile))) {
			throw new RuntimeException('Cannot create override backup directory.');
		}
		if (file_exists($backupFile) || file_put_contents($backupFile, $before) !== strlen($before) || !chmod($backupFile, 0600)) {
			throw new RuntimeException('Cannot preserve the original override: ' . $relative);
		}
		$publish = tempnam(dirname($target), '.ocmod-deploy-');
		$temporary[] = $publish;
		if ($publish === false || file_put_contents($publish, $after) !== strlen($after) || !chmod($publish, $mode) || !rename($publish, $target)) {
			throw new RuntimeException('Cannot atomically publish override: ' . $relative);
		}
		echo 'Override updated: ' . $relative . "\n";
	}
} catch (Throwable $e) {
	fwrite(STDERR, $e->getMessage() . "\n");
	exit(1);
} finally {
	foreach ($temporary as $file) {
		if ($file !== false && is_file($file)) {
			unlink($file);
		}
	}
}
