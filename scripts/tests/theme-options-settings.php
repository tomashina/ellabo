<?php
// Run: php scripts/tests/theme-options-settings.php
// Only synthetic settings are used; no shop configuration is loaded.
define('HTTP_CATALOG', 'http://catalog.test/');
define('DIR_TEMPLATE', 'themeoptions-test://templates/');
require dirname(__DIR__, 2) . '/system/library/themeoptions.php';

class ThemeOptionsSettingsStream {
	public $context;
	public static $files = array();
	public static $opens = array();
	public static $stats = array();
	private $contents = '';
	private $position = 0;

	public function stream_open($path, $mode, $options, &$opened_path) {
		if (!isset(self::$files[$path])) {
			return false;
		}
		self::$opens[$path] = isset(self::$opens[$path]) ? self::$opens[$path] + 1 : 1;
		$this->contents = self::$files[$path];
		$this->position = 0;
		return true;
	}

	public function stream_read($count) {
		$result = substr($this->contents, $this->position, $count);
		$this->position += strlen($result);
		return $result;
	}

	public function stream_eof() {
		return $this->position >= strlen($this->contents);
	}

	public function stream_stat() {
		return array('mode' => 0100644, 'size' => strlen($this->contents));
	}

	public function url_stat($path, $flags) {
		self::$stats[$path] = isset(self::$stats[$path]) ? self::$stats[$path] + 1 : 1;
		return isset(self::$files[$path]) ? array('mode' => 0100644, 'size' => strlen(self::$files[$path])) : false;
	}
}

class ThemeOptionsSettingsConfig {
	private $skin;
	private $store;
	public function __construct($skin, $store = 0) {
		$this->skin = $skin;
		$this->store = $store;
	}
	public function get($key) {
		$values = array('config_theme' => 'default', 'theme_default_directory' => 'zeexo', 'zeexo_skin' => $this->skin, 'config_store_id' => $this->store);
		return isset($values[$key]) ? $values[$key] : null;
	}
}

class ThemeOptionsSettingsRegistry {
	private $config;
	public function __construct($config) {
		$this->config = $config;
	}
	public function get($key) {
		if ($key !== 'config') {
			throw new RuntimeException('Unexpected service: ' . $key);
		}
		return $this->config;
	}
}

function sameThemeOptionsSetting($expected, $actual, $message) {
	if ($expected !== $actual) {
		throw new RuntimeException($message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true));
	}
}

function settingsPath($skin, $store = 'default') {
	return DIR_TEMPLATE . 'zeexo/skins/store_' . $store . '/' . $skin . '/settings.json';
}

function makeThemeOptionsSettings($skin, $store = 0) {
	return new ThemeOptions(new ThemeOptionsSettingsRegistry(new ThemeOptionsSettingsConfig($skin, $store)), new stdClass());
}

stream_wrapper_register('themeoptions-test', 'ThemeOptionsSettingsStream');
set_error_handler(function($code, $message) {
	throw new RuntimeException($message);
});

try {
	$settings = array(
		'plain' => 'original',
		'nested' => array('one' => array('two' => array('three' => 'value'))),
		'zero' => 0,
		'disabled' => false,
		'empty' => '',
		'payment' => array(array('sort' => 2, 'name' => 'second'), array('sort' => 1, 'name' => 'first'))
	);
	$path = settingsPath('blue');
	ThemeOptionsSettingsStream::$files[$path] = json_encode($settings);
	$options = makeThemeOptionsSettings('blue');
	sameThemeOptionsSetting('original', $options->get('plain'), 'Read the configured skin.');

	// A settings change is visible to a new request, without rereading within
	// the existing request. This also catches the original undefined $data guard.
	$settings['plain'] = 'updated';
	ThemeOptionsSettingsStream::$files[$path] = json_encode($settings);
	for ($call = 0; $call < 200; $call++) {
		sameThemeOptionsSetting('original', $options->get('plain'), 'Reuse settings for this request.');
	}
	sameThemeOptionsSetting(array('two' => array('three' => 'value')), $options->get('nested', 'one'), 'Preserve one nested key.');
	sameThemeOptionsSetting(array('three' => 'value'), $options->get('nested', 'one', 'two'), 'Preserve two nested keys.');
	sameThemeOptionsSetting('value', $options->get('nested', 'one', 'two', 'three'), 'Preserve three nested keys.');
	sameThemeOptionsSetting(null, $options->get('nested', 'missing'), 'Missing keys remain null.');
	foreach (array('zero' => 0, 'disabled' => false, 'empty' => '') as $key => $value) {
		sameThemeOptionsSetting($value, $options->get($key), 'Preserve falsy settings.');
	}
	sameThemeOptionsSetting(array(array('sort' => 1, 'name' => 'first'), array('sort' => 2, 'name' => 'second')), $options->get('payment'), 'Preserve sorted payment options.');
	sameThemeOptionsSetting('default', $options->get('store'), 'Preserve the default store name.');
	sameThemeOptionsSetting('blue', $options->get('skin'), 'Preserve the configured skin.');
	sameThemeOptionsSetting(1, ThemeOptionsSettingsStream::$opens[$path], 'Read the file exactly once per instance.');
	sameThemeOptionsSetting('updated', makeThemeOptionsSettings('blue')->get('plain'), 'A new request sees changed settings.');
	sameThemeOptionsSetting(2, ThemeOptionsSettingsStream::$opens[$path], 'Instances do not share a stale settings cache.');

	foreach (array('empty' => '', 'object' => '{}', 'missing' => null) as $skin => $contents) {
		$path = settingsPath($skin, 42);
		if ($contents !== null) {
			ThemeOptionsSettingsStream::$files[$path] = $contents;
		}
		$options = makeThemeOptionsSettings($skin, 42);
		sameThemeOptionsSetting(null, $options->get('unknown'), 'An empty or missing file has no settings.');
		$stats = ThemeOptionsSettingsStream::$stats[$path];
		ThemeOptionsSettingsStream::$files[$path] = '{"unknown":"changed"}';
		clearstatcache();
		sameThemeOptionsSetting(null, $options->get('unknown'), 'An empty or missing file is not reloaded during this request.');
		sameThemeOptionsSetting($stats, ThemeOptionsSettingsStream::$stats[$path], 'Do not restat an empty or missing file.');
		sameThemeOptionsSetting($contents === null ? 0 : 1, isset(ThemeOptionsSettingsStream::$opens[$path]) ? ThemeOptionsSettingsStream::$opens[$path] : 0, 'Do not reread an empty or missing file.');
		sameThemeOptionsSetting(42, $options->get('store'), 'Preserve a non-default store ID.');
		sameThemeOptionsSetting($skin, $options->get('skin'), 'Preserve the skin when settings are unavailable.');
	}
} finally {
	restore_error_handler();
	stream_wrapper_unregister('themeoptions-test');
}

echo "PASS: one settings load per request; nested, payment, store, empty and missing settings remain correct.\n";
