<?php
// Synthetic extension overrides only; no generated production files or credentials.
$repo = dirname(__DIR__, 2);
$temporary = sys_get_temp_dir() . '/ellabo-overrides-' . uniqid();
mkdir($temporary, 0700);
$root = $temporary . '/shop';
$backup = $temporary . '/backup';
mkdir($root, 0700);
mkdir($backup, 0700);
$files = array(
	'system/library/db.php' => 'database-close.patch',
	'catalog/controller/startup/seo_url.php' => 'category-path.patch',
	'admin/controller/extension/module/product_quick_edit.php' => 'local-import-feed.patch',
	'catalog/controller/extension/module/ag_specials_cron.php' => 'local-specials-feed.patch'
);
$originals = array();

function expectOverride($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}
function runOverrideTest($arguments) {
	global $repo, $root;
	$output = array();
	exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($repo . '/scripts/sync-modification-overrides.php') . ' ' . escapeshellarg($root) . ' ' . $arguments . ' 2>&1', $output, $status);
	return $status;
}
function removeOverrideFixture($path) {
	if (is_dir($path) && !is_link($path)) {
		foreach (array_diff(scandir($path), array('.', '..')) as $entry) {
			removeOverrideFixture($path . '/' . $entry);
		}
		rmdir($path);
	} else {
		unlink($path);
	}
}

try {
	foreach ($files as $relative => $patch) {
		$target = $root . '/system/storage/modification/' . $relative;
		mkdir(dirname($target), 0700, true);
		copy($repo . '/' . $relative, $target);
		exec('patch --reverse --batch --silent --fuzz=0 ' . escapeshellarg($target) . ' ' . escapeshellarg($repo . '/scripts/patches/' . $patch) . ' 2>&1', $output, $status);
		expectOverride($status === 0, 'Build a legacy fixture from the reviewed source delta.');
		$contents = str_replace('<?php', "<?php\n// Synthetic extension customization must survive deployment.", file_get_contents($target));
		file_put_contents($target, $contents);
		chmod($target, 0640);
		$originals[$relative] = $contents;
	}
	expectOverride(runOverrideTest('--check') === 0, 'Preflight accepts supported extension overrides.');
	foreach ($originals as $relative => $contents) {
		expectOverride(file_get_contents($root . '/system/storage/modification/' . $relative) === $contents, 'Preflight must never mutate an override.');
	}
	$seo = $root . '/system/storage/modification/catalog/controller/startup/seo_url.php';
	file_put_contents($seo, '<?php // An unknown override.');
	expectOverride(runOverrideTest('--apply ' . escapeshellarg($backup)) !== 0, 'Unknown override stops publication.');
	expectOverride(file_get_contents($root . '/system/storage/modification/system/library/db.php') === $originals['system/library/db.php'], 'Validate all overrides before changing any.');
	file_put_contents($seo, $originals['catalog/controller/startup/seo_url.php']);
	expectOverride(runOverrideTest('--apply ' . escapeshellarg($root)) !== 0, 'Refuse backups inside the public document root.');
	expectOverride(runOverrideTest('--apply ' . escapeshellarg($backup)) === 0, 'Publish reviewed deltas.');
	foreach ($originals as $relative => $contents) {
		$target = $root . '/system/storage/modification/' . $relative;
		$expected = str_replace('<?php', "<?php\n// Synthetic extension customization must survive deployment.", file_get_contents($repo . '/' . $relative));
		expectOverride(file_get_contents($target) === $expected, 'Preserve extension additions exactly.');
		expectOverride((fileperms($target) & 0777) === 0640, 'Preserve runtime file permissions.');
		expectOverride(file_get_contents($backup . '/modification/' . $relative) === $contents, 'Back up the original override outside the webroot.');
	}
	expectOverride(runOverrideTest('--apply ' . escapeshellarg($backup)) === 0, 'A repeated deployment is idempotent.');
	echo "PASS: override preflight, all-file validation, extension preservation, backups, permissions and repeated deployment.\n";
} finally {
	removeOverrideFixture($temporary);
}
