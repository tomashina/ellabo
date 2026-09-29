<?php

/*
Breadcrumbs+ v2.1

The Breadcrumbs+ extension is for CMS Opencart 3.x.
The module allows to manage product breadcrumbs.

<https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=35022>
<https://underr.space/notes/projects/project-008.html>
<https://github.com/underr-ua/ocmod3-breadcrumbs-plus>

This file is subject to the End Use License Agreement (EULA) <https://raw.githubusercontent.com/underr-ua/ocmod3-breadcrumbs-plus/master/EULA.txt>

Copyright (c) 2019 Andrii Burkatskyi aka underr
*/

class ControllerExtensionModuleBreadcrumbs extends Controller {
	private function getProductBreadcrumbs($product_bc_type = '', $get = array()) {
		$breadcrumbs = array();

		if ($product_bc_type && isset($get['product_id'])) {
			$product_id = $get['product_id'];

			$breadcrumbs[] = array(
				// 'text' => $this->language->get('text_home'),
				'text' => '<i class="fa fa-home"></i>',
				'href' => $this->url->link('common/home'),
			);

			$this->load->model('catalog/product');

			$product_info = $this->model_catalog_product->getProduct($product_id);

			if (isset($get['search']) || isset($get['tag'])) {
				$url = '';

				if (isset($get['search'])) {
					$url .= '&search=' . $get['search'];
				}

				if (isset($get['tag'])) {
					$url .= '&tag=' . $get['tag'];
				}

				if (isset($get['description'])) {
					$url .= '&description=' . $get['description'];
				}

				if (isset($get['category_id'])) {
					$url .= '&category_id=' . $get['category_id'];
				}

				if (isset($get['sub_category'])) {
					$url .= '&sub_category=' . $get['sub_category'];
				}

				if (isset($get['sort'])) {
					$url .= '&sort=' . $get['sort'];
				}

				if (isset($get['order'])) {
					$url .= '&order=' . $get['order'];
				}

				if (isset($get['page'])) {
					$url .= '&page=' . $get['page'];
				}

				if (isset($get['limit'])) {
					$url .= '&limit=' . $get['limit'];
				}

				$breadcrumbs[] = array(
					'text' => $this->language->get('text_search'),
					'href' => $this->url->link('product/search', $url),
				);
			}

			if ('D' == $product_bc_type) {
				//
			} elseif (in_array($product_bc_type, array('S', 'L', 'T'))) {
				$url = '';

				if (isset($get['sort'])) {
					$url .= '&sort=' . $get['sort'];
				}

				if (isset($get['order'])) {
					$url .= '&order=' . $get['order'];
				}

				if (isset($get['page'])) {
					$url .= '&page=' . $get['page'];
				}

				if (isset($get['limit'])) {
					$url .= '&limit=' . $get['limit'];
				}

				$this->load->model('extension/module/breadcrumbs');

				$path = '';
				$parts = explode(
					'_',
					$this->model_extension_module_breadcrumbs->getProductPath($product_id, $product_bc_type)
				);

				$category_id = (int)array_pop($parts);

				$this->load->model('catalog/category');

				foreach ($parts as $path_id) {
					if (!$path) {
						$path = $path_id;
					} else {
						$path .= '_' . $path_id;
					}

					$category_info = $this->model_catalog_category->getCategory($path_id);

					if ($category_info) {
						$breadcrumbs[] = array(
							'text' => $category_info['name'],
							'href' => $this->url->link('product/category', 'path=' . $path),
						);
					}
				}

				// Set the last category breadcrumb
				$category_info = $this->model_catalog_category->getCategory($category_id);

				if ($category_info) {
					if (!$path) {
						$path = $category_id;
					} else {
						$path .= '_' . $category_id;
					}

					$breadcrumbs[] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url),
					);
				}
			} elseif (isset($get['manufacturer_id']) || 'M' == $product_bc_type) {
				$url = '';

				if (isset($get['sort'])) {
					$url .= '&sort=' . $get['sort'];
				}

				if (isset($get['order'])) {
					$url .= '&order=' . $get['order'];
				}

				if (isset($get['page'])) {
					$url .= '&page=' . $get['page'];
				}

				if (isset($get['limit'])) {
					$url .= '&limit=' . $get['limit'];
				}

				$breadcrumbs[] = array(
					'text' => $this->language->get('text_brand'),
					'href' => $this->url->link('product/manufacturer'),
				);

				if (isset($get['manufacturer_id'])) {
					$manufacturer_id = $get['manufacturer_id'];
				} elseif (isset($product_info['manufacturer_id'])) {
					$manufacturer_id = $product_info['manufacturer_id'];
				} else {
					$manufacturer_id = 0; // just for secure
				}

				$this->load->model('catalog/manufacturer');

				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

				if ($manufacturer_info) {
					$breadcrumbs[] = array(
						'text' => $manufacturer_info['name'],
						'href' => $this->url->link(
							'product/manufacturer/info',
							'manufacturer_id=' . $manufacturer_id . $url
						),
					);
				}
			}

			$url = '';

			if (isset($get['path'])) {
				$url .= '&path=' . $get['path'];
			}

			if (isset($get['filter'])) {
				$url .= '&filter=' . $get['filter'];
			}

			if (isset($get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $get['manufacturer_id'];
			}

			if (isset($get['search'])) {
				$url .= '&search=' . $get['search'];
			}

			if (isset($get['tag'])) {
				$url .= '&tag=' . $get['tag'];
			}

			if (isset($get['description'])) {
				$url .= '&description=' . $get['description'];
			}

			if (isset($get['category_id'])) {
				$url .= '&category_id=' . $get['category_id'];
			}

			if (isset($get['sub_category'])) {
				$url .= '&sub_category=' . $get['sub_category'];
			}

			if (isset($get['sort'])) {
				$url .= '&sort=' . $get['sort'];
			}

			if (isset($get['order'])) {
				$url .= '&order=' . $get['order'];
			}

			if (isset($get['page'])) {
				$url .= '&page=' . $get['page'];
			}

			if (isset($get['limit'])) {
				$url .= '&limit=' . $get['limit'];
			}

			$breadcrumbs[] = array(
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', '&product_id=' . $product_id .  $url),
			);
		}

		return $breadcrumbs;
	}

	private function getBreadcrumbsJson($breadcrumbs = array()) {
		if (!$breadcrumbs || !is_array($breadcrumbs)) {
			return array();
		}

		$item_list = array();
		$loop = 0;

		foreach ($breadcrumbs as $key => $breadcrumb) {
			$e = array();

			$e['@type'] = 'ListItem';
			$e['position'] = $loop;

			if (0 == $loop) {
				$e['name'] = htmlspecialchars($this->config->get('config_name'));
			} else {
				$e['name'] = htmlspecialchars($breadcrumb['text']);
			}

			$e['item'] = $breadcrumb['href'];

			$item_list[] = $e;

			$loop++;
		}

		$json = array(
			'@context'        => 'http://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $item_list,
		);

		return json_encode($json);
	}

	private function removeLastBreadcrumb($breadcrumbs) {
		$count = count($breadcrumbs);

		if ($count > 1) {
			$breadcrumbs[$count - 1]['href'] = '';
		}

		return $breadcrumbs;
	}

	// catalog/view/*/before
	public function updateBreadcrumbs(&$route, &$data) {
		if (!isset($data['breadcrumbs']) || !is_array($data['breadcrumbs'])) {
			return;
		}

		if (!$this->config->get('module_breadcrumbs_status')) {
			return;
		}

		if (isset($this->request->get['product_id'])) {
			$skip = false;

			if ((isset($this->request->get['search']) || isset($this->request->get['tag'])) &&
				!$this->config->get('module_breadcrumbs_update_search')
			) {
				$skip = true;
			}

			if (isset($this->request->get['manufacturer_id']) &&
				!$this->config->get('module_breadcrumbs_update_manufacturer')
			) {
				$skip = true;
			}

			$product_bc_type = $this->config->get('module_breadcrumbs_type');

			if ($product_bc_type && !$skip) {
				$data['breadcrumbs'] = $this->getProductBreadcrumbs($product_bc_type, $this->request->get);
			}
		}

		if ($this->config->get('module_breadcrumbs_json') && method_exists($this->document, 'addCustomScript')) {
			$data['json_breadcrumbs'] = $this->getBreadcrumbsJson($data['breadcrumbs']);
			$this->document->addCustomScript('header', 'application/ld+json', $route, $data['json_breadcrumbs']);
		}

		if ($this->config->get('module_breadcrumbs_nolink')) {
			$data['breadcrumbs'] = $this->removeLastBreadcrumb($data['breadcrumbs']);
		}
	}

	// catalog/controller/common/header/before
	public function styleBreadcrumbs(&$route, &$data) {
		if (!$this->config->get('module_breadcrumbs_status')) {
			return;
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/stylesheet.css')) {
			$dir_stylesheet = 'catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/';
		} else {
			$dir_stylesheet = 'catalog/view/theme/default/stylesheet/';
		}

		if ($this->config->get('module_breadcrumbs_bold')) {
			$css_bold = $dir_stylesheet . 'breadcrumbs_plus_bold.css';

			if (file_exists($css_bold)) {
				$this->document->addStyle($css_bold);
			}
		}

		if ($this->config->get('module_breadcrumbs_nolink')) {
			$css_nolink = $dir_stylesheet . 'breadcrumbs_plus_nolink.css';

			if (file_exists($css_nolink)) {
				$this->document->addStyle($css_nolink);
			}
		}
	}

	//catalog/view/*/after
	public function addJsonLdScript(&$route, &$data, &$template) {
		$scripts = '';

		if ($this->config->get('module_breadcrumbs_json') && method_exists($this->document, 'getCustomScripts')) {
			if ($this->document->getCustomScripts('header')) {
				foreach ($this->document->getCustomScripts('header') as $script) {
					if ($script['title'] == $route) {
						$scripts .= '<script type="' .
							$script['type'] . '">' . "\n" . $script['text'] . "\n" .
							'</script>' . "\n";
					}
				}
			}

			$template = preg_replace('/<\/head>/', $scripts . '</head>', $template);
		}
	}
}
