<?php
// Run with: php scripts/tests/import-feed.php
// Fixtures live only in a private temporary directory; no application/database bootstrap.
$temporary = sys_get_temp_dir() . '/ellabo-import-feed-' . uniqid('', true);
$root = $temporary . '/shop';
mkdir($root . '/xml', 0700, true);
define('DIR_ROOT', $root . '/');
require dirname(__DIR__, 2) . '/system/library/importfeed.php';

function feedExpect($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

function feedRemove($path) {
	if (is_dir($path) && !is_link($path)) {
		foreach (array_diff(scandir($path), array('.', '..')) as $entry) {
			feedRemove($path . '/' . $entry);
		}
		rmdir($path);
	} elseif (file_exists($path) || is_link($path)) {
		unlink($path);
	}
}

function feedProduct(array $replace = array(), array $omit = array()) {
	$fields = array_merge(array(
		'Sifra' => 'SYNTHETIC-SKU',
		'Raspolozivo' => '7',
		'MlpOsnovica' => '10.00',
		'MlpCijena' => '12.50',
		'MlpCijenaOld' => '15.00',
		'Naziv' => 'Synthetic fixture product',
		'BarCode' => '',
		'JedMjere' => 'KOM',
		'DrzavaPorijeklo' => '',
		'KolPaket' => '1',
		'Aktivno' => 'D',
		'WebView' => 'D'
	), $replace);
	$xml = '<PRODUCT>';
	foreach ($fields as $name => $value) {
		if (!in_array($name, $omit, true)) {
			$xml .= '<' . $name . '>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</' . $name . '>';
		}
	}
	return $xml . '</PRODUCT>';
}

function feedDocument($products) {
	return '<?xml version="1.0" encoding="UTF-8"?><PRODUCTS>' . $products . '</PRODUCTS>';
}

function feedFails($url, $profile = 'stock', $secrets = array()) {
	global $temporary;
	$caught = null;
	ob_start();
	try {
		ImportFeed::load($url, $profile);
	} catch (RuntimeException $exception) {
		$caught = $exception;
	} finally {
		$output = ob_get_clean();
	}
	feedExpect($caught instanceof RuntimeException, 'Invalid feed must fail closed with a controlled exception.');
	feedExpect($output === '', 'A rejected feed must not emit parser warnings or response data.');
	feedExpect(strlen($caught->getMessage()) > 5, 'Feed failures need a useful error message.');
	foreach (array_merge(array($temporary), $secrets) as $secret) {
		feedExpect(strpos($caught->getMessage(), $secret) === false, 'Exception must not expose local paths, credentials or configured URL secrets.');
	}
	return $caught;
}

$server = null;
$pipes = array();

try {
	$valid = feedDocument(feedProduct() . feedProduct(array('Sifra' => 'SECOND-SKU')));
	foreach (array(1, 2, 11, 12) as $suffix) {
		file_put_contents($root . '/xml/xml_ellabo_artikli' . $suffix . '.xml', $valid);
		foreach (array('www.ellabo.hr', 'ellabo.hr') as $host) {
			$xml = ImportFeed::load('https://' . $host . '/xml/xml_ellabo_artikli' . $suffix . '.xml');
			feedExpect($xml instanceof SimpleXMLElement, 'Both local hosts must load all four configured mirrors as XML.');
			feedExpect($xml->getName() === 'PRODUCTS' && count($xml->PRODUCT) === 2, 'Accept a valid multi-PRODUCT contract.');
			feedExpect((string)$xml->PRODUCT->Sifra === 'SYNTHETIC-SKU', 'The feed must come from the local fixture.');
		}
	}
	ImportFeed::load('http://www.ellabo.hr/xml/xml_ellabo_artikli1.xml', 'specials');
	ImportFeed::load('https://www.ellabo.hr/xml/xml_ellabo_artikli2.xml', 'products');
	echo "PASS both local hosts load all configured mirror paths and accept stock/products/specials schemas\n";

	$file = $root . '/xml/xml_ellabo_artikli1.xml';
	$url = 'https://www.ellabo.hr/xml/xml_ellabo_artikli1.xml';
	$invalid = array(
		'',
		'<html><head><title>Just a moment...</title></head><body>Cloudflare challenge</body></html>',
		'<PRODUCTS/>',
		feedDocument(feedProduct()),
		'<PRODUCTS><WRAPPER>' . feedProduct() . '</WRAPPER></PRODUCTS>',
		'<INVENTORY>' . feedProduct() . '</INVENTORY>',
		'<PRODUCTS><PRODUCT>',
		feedDocument(feedProduct() . feedProduct(array('Sifra' => ''))),
		feedDocument(feedProduct() . feedProduct(array('Sifra' => 'SECOND-SKU', 'Raspolozivo' => 'not-a-number'))),
		feedDocument(feedProduct() . feedProduct(array('Sifra' => 'SECOND-SKU', 'MlpCijena' => ''))),
		feedDocument(feedProduct() . feedProduct(array('Sifra' => 'SECOND-SKU'), array('MlpOsnovica'))),
		feedDocument(feedProduct() . feedProduct(array('Sifra' => 'SECOND-SKU'), array('Raspolozivo')))
	);
	foreach ($invalid as $document) {
		file_put_contents($file, $document);
		feedFails($url);
	}
	file_put_contents($file, feedDocument(feedProduct() . feedProduct(array('Sifra' => 'SECOND-SKU'), array('MlpCijenaOld'))));
	feedFails($url, 'specials');
	file_put_contents($file, feedDocument(feedProduct() . feedProduct(array('Sifra' => 'SECOND-SKU'), array('WebView'))));
	feedFails($url, 'products');
	file_put_contents($file, $valid);
	echo "PASS HTML, malformed/empty/singleton XML, wrong roots, nested products and incomplete row schemas fail closed\n";

	foreach (array(false, true) as $internal_errors) {
		libxml_clear_errors();
		libxml_use_internal_errors($internal_errors);
		file_put_contents($file, '<PRODUCTS><PRODUCT>');
		feedFails($url);
		feedExpect(libxml_use_internal_errors() === $internal_errors, 'Restore the caller libxml error mode after failure.');
		file_put_contents($file, $valid);
		ImportFeed::load($url);
		feedExpect(libxml_use_internal_errors() === $internal_errors, 'Restore the caller libxml error mode after success.');
		feedExpect(count(libxml_get_errors()) === 0, 'Do not leave parser errors in the global libxml buffer.');
	}
	libxml_use_internal_errors(false);
	$secretFile = $temporary . '/synthetic-secret.txt';
	file_put_contents($secretFile, 'SYNTHETIC-SECRET-VALUE');
	$entity = '<?xml version="1.0"?><!DOCTYPE PRODUCTS [<!ENTITY secret SYSTEM "file://' . $secretFile . '">]>';
	file_put_contents($file, $entity . '<PRODUCTS>' . str_replace('SYNTHETIC-SKU', '&secret;', feedProduct()) . feedProduct(array('Sifra' => 'SECOND-SKU')) . '</PRODUCTS>');
	feedFails($url, 'stock', array('SYNTHETIC-SECRET-VALUE'));
	file_put_contents($file, '<!DOCTYPE PRODUCTS SYSTEM "http://127.0.0.1:9/never-fetch.dtd">' . feedDocument(feedProduct()));
	feedFails($url);
	// Wide encodings hide ASCII DOCTYPE tokens from a raw string search.
	$wide = '<?xml version="1.0" encoding="UTF-16"?><!DOCTYPE PRODUCTS [<!ENTITY code "SYNTHETIC-SKU">]><PRODUCTS>' . str_replace('SYNTHETIC-SKU', '&code;', feedProduct()) . feedProduct(array('Sifra' => 'SECOND-SKU')) . '</PRODUCTS>';
	file_put_contents($file, "\xFF\xFE" . implode("\x00", str_split($wide)) . "\x00");
	feedFails($url);
	file_put_contents($file, $valid);
	echo "PASS entity/DOCTYPE input is rejected and libxml state is restored without leaking parser details\n";

	unlink($file);
	feedFails($url);
	$missing_apex = feedFails('https://ellabo.hr/xml/xml_ellabo_artikli1.xml');
	feedExpect($missing_apex instanceof ImportFeedException && strpos($missing_apex->getMessage(), 'Local import feed') === 0, 'A missing apex mirror must fail locally without trying HTTP.');
	mkdir($file, 0700);
	feedFails($url);
	rmdir($file);
	file_put_contents($file, $valid);
	chmod($file, 0000);
	if (!is_readable($file)) {
		feedFails($url);
	}
	chmod($file, 0600);
	echo "PASS missing and non-file/unreadable local mirrors fail without an HTTP fallback\n";

	$outside = $temporary . '/outside.xml';
	file_put_contents($outside, $valid);
	unlink($file);
	symlink($outside, $file);
	feedFails($url);
	unlink($file);
	file_put_contents($file, $valid);
	foreach (array('www.ellabo.hr', 'ellabo.hr') as $host) {
		foreach (array(
			'/xml/../xml/xml_ellabo_artikli1.xml',
			'/xml/%2e%2e/xml/xml_ellabo_artikli1.xml',
			'/xml/xml_ellabo_artikli3.xml'
		) as $unsafe) {
			feedFails('https://' . $host . $unsafe);
		}
	}
	rename($root . '/xml', $root . '/xml-original');
	mkdir($temporary . '/outside-root', 0700);
	file_put_contents($temporary . '/outside-root/xml_ellabo_artikli1.xml', $valid);
	symlink($temporary . '/outside-root', $root . '/xml');
	feedFails($url);
	unlink($root . '/xml');
	rename($root . '/xml-original', $root . '/xml');
	echo "PASS traversal, unknown local paths, file symlinks and XML-root symlink escapes are rejected\n";

	$oversized = fopen($file, 'wb');
	ftruncate($oversized, 64 * 1024 * 1024 + 1);
	fclose($oversized);
	feedFails($url);
	file_put_contents($file, $valid);
	echo "PASS oversized local files are rejected before parsing\n";

	// External feeds are configured by the application, never request parameters.
	// Exercise their HTTP transport only against a temporary loopback server.
	$socket = stream_socket_server('tcp://127.0.0.1:0', $error_code, $error_text);
	feedExpect($socket !== false, 'Allocate a loopback port for the external-feed fixture.');
	$address = stream_socket_get_name($socket, false);
	fclose($socket);
	$serverRoot = $temporary . '/http';
	mkdir($serverRoot, 0700);
	file_put_contents($serverRoot . '/valid.xml', $valid);
	file_put_contents($serverRoot . '/challenge.xml', '<html><head><title>Just a moment...</title></head><body>Challenge</body></html>');
	$command = 'exec ' . escapeshellarg(PHP_BINARY) . ' -S ' . escapeshellarg($address) . ' -t ' . escapeshellarg($serverRoot);
	$server = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('file', $temporary . '/http.out', 'a'), 2 => array('file', $temporary . '/http.err', 'a')), $pipes);
	feedExpect(is_resource($server), 'Start the isolated loopback HTTP fixture.');
	fclose($pipes[0]);
	$ready = false;
	for ($attempt = 0; $attempt < 100; $attempt++) {
		$connection = @stream_socket_client('tcp://' . $address, $error_code, $error_text, 0.05);
		if ($connection) {
			fclose($connection);
			$ready = true;
			break;
		}
		usleep(20000);
	}
	feedExpect($ready, 'Wait for the loopback fixture to start.');
	$remote = ImportFeed::load('http://' . $address . '/valid.xml?key=SYNTHETIC-URL-KEY');
	feedExpect((string)$remote->PRODUCT->Sifra === 'SYNTHETIC-SKU', 'A configured external HTTP XML feed remains supported.');
	feedFails('http://' . $address . '/challenge.xml?key=SYNTHETIC-URL-KEY', 'stock', array('SYNTHETIC-URL-KEY', $address, 'challenge.xml'));
	feedFails('http://' . $address . '/missing.xml?key=SYNTHETIC-URL-KEY', 'stock', array('SYNTHETIC-URL-KEY', $address, 'missing.xml'));
	feedFails('file://' . $outside);
	echo "PASS bounded external HTTP loading remains supported, while invalid/error responses have sanitized exceptions\n";
} finally {
	if (is_resource($server)) {
		proc_terminate($server);
		proc_close($server);
	}
	libxml_clear_errors();
	libxml_use_internal_errors(false);
	feedRemove($temporary);
}
