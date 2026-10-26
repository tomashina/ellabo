<?php
// Run with: php scripts/tests/theme-options-refine-search.php
define('HTTP_CATALOG', 'http://catalog.test/');
require dirname(__DIR__, 2) . '/system/library/themeoptions.php';

class RefineSearchRegistry {
	private $services;

	public function __construct($services) {
		$this->services = $services;
	}

	public function get($key) {
		return $this->services[$key];
	}
}

class RefineSearchConfig {
	private $product_count;

	public function __construct($product_count) {
		$this->product_count = $product_count;
	}

	public function get($key) {
		if ($key !== 'config_product_count') {
			throw new RuntimeException('Unexpected configuration key: ' . $key);
		}

		return $this->product_count;
	}
}

class RefineSearchLoader {
	public $models = array();

	public function model($model) {
		$this->models[] = $model;
	}
}

class RefineSearchCategory {
	public $parents = array();

	public function getCategories($parent_id) {
		$this->parents[] = $parent_id;

		return array(
			array('category_id' => 21, 'image' => 'catalog/cables.jpg', 'name' => 'Kabeli'),
			array('category_id' => 22, 'image' => '', 'name' => 'Pribor')
		);
	}
}

class RefineSearchProduct {
	public $calls = array();

	public function getTotalProducts($data) {
		$this->calls[] = $data;

		return $data['filter_category_id'] === 21 ? 3 : 0;
	}
}

class RefineSearchUrl {
	public function link($route, $arguments) {
		return 'index.php?route=' . $route . '&' . $arguments;
	}
}

function assertRefineSearchSame($expected, $actual, $message) {
	if ($expected !== $actual) {
		throw new RuntimeException($message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true));
	}
}

foreach (array('0', '1') as $product_count) {
	$loader = new RefineSearchLoader();
	$category = new RefineSearchCategory();
	$product = new RefineSearchProduct();
	$request = new stdClass();
	$request->get = array(
		'path' => '10_20',
		'filter' => '4,5',
		'sort' => 'p.price',
		'order' => 'DESC',
		'limit' => '25'
	);
	$registry = new RefineSearchRegistry(array(
		'config' => new RefineSearchConfig($product_count),
		'model_catalog_category' => $category,
		'model_catalog_product' => $product,
		'request' => $request,
		'url' => new RefineSearchUrl()
	));
	$options = new ThemeOptions($registry, $loader);
	$results = $options->refineSearch();
	$suffix = '&filter=4,5&sort=p.price&order=DESC&limit=25';

	assertRefineSearchSame(array(
		array(
			'thumb' => 'catalog/cables.jpg',
			'name' => $product_count === '1' ? 'Kabeli (3)' : 'Kabeli',
			'href' => 'index.php?route=product/category&path=10_20_21' . $suffix
		),
		array(
			'thumb' => '',
			'name' => $product_count === '1' ? 'Pribor (0)' : 'Pribor',
			'href' => 'index.php?route=product/category&path=10_20_22' . $suffix
		)
	), $results, 'Category output must preserve names, images, paths and filter parameters.');
	assertRefineSearchSame(array(20), $category->parents, 'Load children of the final category in the path.');
	assertRefineSearchSame(array('catalog/category'), $loader->models, 'Load the category model.');
	assertRefineSearchSame($product_count === '1' ? array(
		array('filter_category_id' => 21, 'filter_sub_category' => true),
		array('filter_category_id' => 22, 'filter_sub_category' => true)
	) : array(), $product->calls, 'Only query product counts when they are displayed.');
}

echo "PASS: hidden counts issue no queries; enabled counts, zero totals and category links remain correct.\n";
