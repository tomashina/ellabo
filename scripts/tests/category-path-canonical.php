<?php
// Run with: php scripts/tests/category-path-canonical.php
require dirname(__DIR__, 2) . '/system/engine/registry.php';
require dirname(__DIR__, 2) . '/system/engine/controller.php';
require dirname(__DIR__, 2) . '/system/library/config.php';
require dirname(__DIR__, 2) . '/system/library/url.php';
require dirname(__DIR__, 2) . '/catalog/controller/startup/seo_url.php';

define('DB_PREFIX', 'test_');

if (!function_exists('utf8_strlen')) {
	function utf8_strlen($value) {
		return strlen($value);
	}
}

class CategoryPathDatabase {
	public $ancestry_queries = 0;
	private $keywords = array(
		'webshop' => 'category_id=10',
		'rasvjeta-svjetiljke' => 'category_id=20',
		'senzori-gla-napajanja-pribor' => 'category_id=21',
		'zarulje-izvori-svjetla' => 'category_id=30',
		'led-zarulje' => 'category_id=31',
		'other-root' => 'category_id=40',
		'test-product' => 'product_id=900',
		'contact-information' => 'information_id=7',
		'test-page' => 'page_id=8',
		'test-brand' => 'manufacturer_id=9',
		'test-article' => 'article_id=11',
		'test-blog-category' => 'blog_category_id=12'
	);
	private $paths = array(
		10 => array(10),
		20 => array(10, 20),
		21 => array(10, 20, 21),
		30 => array(10, 30),
		31 => array(10, 30, 31),
		40 => array(40)
	);

	public function escape($value) {
		return addslashes($value);
	}

	public function query($sql) {
		$rows = array();

		if (preg_match("/^SELECT path_id FROM test_category_path WHERE category_id = '(\d+)' ORDER BY level ASC$/", $sql, $match)) {
			$this->ancestry_queries++;
			foreach (isset($this->paths[$match[1]]) ? $this->paths[$match[1]] : array() as $id) {
				$rows[] = array('path_id' => (string)$id);
			}
		} elseif (preg_match("/FROM test_seo_url WHERE keyword = '([^']*)'/", $sql, $match)) {
			if (isset($this->keywords[$match[1]])) {
				$rows[] = array('query' => $this->keywords[$match[1]], 'keyword' => $match[1]);
			}
		} elseif (preg_match("/FROM test_seo_url WHERE `query` = '([^']*)'/", $sql, $match)) {
			$keyword = array_search($match[1], $this->keywords, true);
			if ($keyword !== false) {
				$rows[] = array('query' => $match[1], 'keyword' => $keyword);
			}
		} else {
			throw new RuntimeException('Unexpected SQL: ' . $sql);
		}

		return (object)array('rows' => $rows, 'row' => $rows ? $rows[0] : array(), 'num_rows' => count($rows));
	}
}

class CategoryPathRedirect extends RuntimeException {
	public $location;
	public $status;

	public function __construct($location, $status) {
		parent::__construct('Redirect');
		$this->location = str_replace('&amp;', '&', $location);
		$this->status = $status;
	}
}

class CategoryPathResponse {
	public function redirect($location, $status) {
		throw new CategoryPathRedirect($location, $status);
	}
}

function categoryRequest($get, $seo = true, $filter_parameter = null, $url = null, $uri = null) {
	$config = new Config();
	$config->set('config_seo_url', $seo);
	$config->set('config_store_id', 0);
	$config->set('config_language_id', 1);
	$config->set('mfilter_url_param', $filter_parameter);
	$request = new stdClass();
	$request->get = $get;
	$request->server = array(
		'HTTPS' => 'on',
		'REQUEST_URI' => $uri !== null ? $uri : (isset($get['_route_']) ? '/' . $get['_route_'] : '/index.php?' . http_build_query($get))
	);
	$db = new CategoryPathDatabase();
	$registry = new Registry();
	$registry->set('config', $config);
	$registry->set('request', $request);
	$registry->set('db', $db);
	$registry->set('url', $url ?: new Url('http://shop.test/', 'https://shop.test/'));
	$registry->set('response', new CategoryPathResponse());
	$redirect = null;

	try {
		(new ControllerStartupSeoUrl($registry))->index();
	} catch (CategoryPathRedirect $result) {
		$redirect = $result;
	}

	return (object)array('request' => $request, 'db' => $db, 'redirect' => $redirect);
}

function categorySame($expected, $actual, $message) {
	if ($expected !== $actual) {
		throw new RuntimeException($message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true));
	}
}

function categoryRedirects($get, $expected_path) {
	$result = categoryRequest($get);
	categorySame(true, $result->redirect instanceof CategoryPathRedirect, 'Invalid chain must redirect before rendering');
	categorySame(301, $result->redirect->status, 'Canonical redirect must be permanent');
	categorySame('https', parse_url($result->redirect->location, PHP_URL_SCHEME), 'Redirect must retain HTTPS');
	categorySame($expected_path, parse_url($result->redirect->location, PHP_URL_PATH), 'Redirect must use actual category ancestry');
	categorySame(1, $result->db->ancestry_queries, 'Only one indexed ancestry lookup is needed');
	$next_get = array();
	parse_str((string)parse_url($result->redirect->location, PHP_URL_QUERY), $next_get);
	$next_get['_route_'] = ltrim($expected_path, '/');
	$next = categoryRequest($next_get);
	categorySame(null, $next->redirect, 'Canonical destination must not redirect again');
	return $result;
}

categoryRedirects(array('_route_' => 'webshop/rasvjeta-svjetiljke/senzori-gla-napajanja-pribor/webshop'), '/webshop');
categoryRedirects(array('_route_' => 'webshop/rasvjeta-svjetiljke/webshop/zarulje-izvori-svjetla'), '/webshop/zarulje-izvori-svjetla');
categoryRedirects(array('_route_' => 'webshop/webshop/webshop/webshop'), '/webshop');
categoryRedirects(array('_route_' => 'webshop/rasvjeta-svjetiljke/led-zarulje'), '/webshop/zarulje-izvori-svjetla/led-zarulje');
categoryRedirects(array('_route_' => 'other-root/senzori-gla-napajanja-pribor'), '/webshop/rasvjeta-svjetiljke/senzori-gla-napajanja-pribor');
categoryRedirects(array('route' => 'product/category', 'path' => '10_30_21'), '/webshop/rasvjeta-svjetiljke/senzori-gla-napajanja-pribor');
echo "PASS repeated and unrelated category chains redirect, including direct routes, without loops\n";

foreach (array('webshop/rasvjeta-svjetiljke/senzori-gla-napajanja-pribor', 'rasvjeta-svjetiljke/senzori-gla-napajanja-pribor') as $route) {
	$result = categoryRequest(array('_route_' => $route));
	categorySame(null, $result->redirect, 'Full and partial valid category paths must remain valid');
	categorySame(1, $result->db->ancestry_queries, 'Validate a multi-segment path with one lookup');
}
foreach (array(array('_route_' => 'senzori-gla-napajanja-pribor'), array('route' => 'product/category', 'path' => '21')) as $get) {
	$result = categoryRequest($get);
	categorySame(null, $result->redirect, 'Single-category paths must remain valid');
	categorySame(0, $result->db->ancestry_queries, 'Single-category paths must not add database queries');
}
echo "PASS valid full, partial and single-category paths are preserved\n";

$controls = array('filter' => '1,2', 'sort' => 'p.price', 'order' => 'DESC', 'page' => '3', 'limit' => '50', 'mfp' => '3-brand[7]', 'facet' => '5-stock[1]');
$get = array_merge(array('_route_' => 'webshop/webshop/led-zarulje', 'token' => 'private', 'user_token' => 'private-admin', 'api_token' => 'private-api', 'unrelated' => 'drop-me'), $controls);
$result = categoryRequest($get, true, 'facet');
$query = array();
parse_str((string)parse_url($result->redirect->location, PHP_URL_QUERY), $query);
categorySame($controls, $query, 'Category controls must survive while routing parameters and tokens are excluded');
categorySame(false, strpos($result->redirect->location, 'private') !== false, 'Do not expose login tokens');
$result = categoryRequest(array('_route_' => 'webshop/webshop', 'user_token' => 'private-admin'), true, 'user_token');
categorySame(false, strpos($result->redirect->location, 'private') !== false, 'A reserved custom filter parameter must not expose tokens');
echo "PASS filters, custom filter parameter, sorting and pagination survive without token exposure\n";

$result = categoryRequest(array('route' => 'product/category', 'path' => '10_30_21', 'page' => '2'), false);
$query = array();
parse_str((string)parse_url($result->redirect->location, PHP_URL_QUERY), $query);
categorySame(array('route' => 'product/category', 'path' => '10_20_21', 'page' => '2'), $query, 'Non-SEO routes must canonicalize to the same ancestry');
$next = categoryRequest($query, false);
categorySame(null, $next->redirect, 'Non-SEO canonical destination must not loop');
echo "PASS direct routes also work when SEO rewriting is disabled\n";

foreach (array('test-product', 'contact-information', 'test-page', 'test-brand', 'test-article', 'test-blog-category') as $keyword) {
	$result = categoryRequest(array('_route_' => $keyword));
	categorySame(null, $result->redirect, 'Non-category routes must not be changed');
	categorySame(0, $result->db->ancestry_queries, 'Non-category routes must not add ancestry queries');
}
$result = categoryRequest(array('_route_' => 'webshop/rasvjeta-svjetiljke/webshop/test-product'));
categorySame(null, $result->redirect, 'Products with category breadcrumbs must not be canonicalized by the category guard');
categorySame(0, $result->db->ancestry_queries, 'Product breadcrumb paths must not add ancestry queries');
foreach (array('product/product', 'information/information', 'common/home') as $route) {
	$result = categoryRequest(array('route' => $route, 'path' => '10_30_21'));
	categorySame(null, $result->redirect, 'Direct non-category routes must be preserved');
	categorySame(0, $result->db->ancestry_queries, 'Unrelated path parameters must not cause database queries');
}
$result = categoryRequest(array('path' => '10_30_21'));
categorySame(0, $result->db->ancestry_queries, 'A path parameter alone must not change the home route');
echo "PASS product, information, manufacturer, blog and unrelated routes are untouched\n";

$result = categoryRequest(array('route' => 'product/category', 'path' => '10_999'));
categorySame(null, $result->redirect, 'Unknown target categories retain the normal not-found behavior');
echo "PASS missing categories retain the normal not-found behavior\n";

class CategoryPathAmbiguousUrl {
	public function addRewrite($rewrite) {}
	public function link($route, $arguments, $secure) {
		return 'https://shop.test/webshop/webshop';
	}
}
$result = categoryRequest(array('_route_' => 'webshop/webshop'), true, null, new CategoryPathAmbiguousUrl());
categorySame(null, $result->redirect, 'An ambiguous rewrite must never redirect to itself');
categorySame('error/not_found', $result->request->get['route'], 'An impossible canonical URL must become not-found');
echo "PASS ambiguous SEO rewrites cannot create redirect loops\n";
