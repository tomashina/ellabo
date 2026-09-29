<?php
class ControllerExtensionInformationInformationCategory extends Controller {
	public function index() {
		$this->load->language('information/information');

		$this->load->model('extension/catalog/information_category');
		$this->load->model('tool/image');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		if (isset($this->request->get['info_path'])) {
			$cat = '';

			$parts = explode('_', (string)$this->request->get['info_path']);

			$information_category_id = (int)array_pop($parts);

			foreach ($parts as $cat_id) {
				if (!$cat) {
					$cat = (int)$cat_id;
				} else {
					$cat .= '_' . (int)$cat_id;
				}

				$info_category_info = $this->model_extension_catalog_information_category->getCategory($cat_id);

				if ($info_category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $info_category_info['name'],
						'href' => $this->url->link('extension/information/information_category', 'info_path=' . $cat)
					);
				}
			}
		} else {
			$information_category_id = 0;
		}

		$information_category_info = $this->model_extension_catalog_information_category->getCategory($information_category_id);

		if ($information_category_info) {
			$this->document->setTitle($information_category_info['meta_title']);
			$this->document->setDescription($information_category_info['meta_description']);
			$this->document->setKeywords($information_category_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $information_category_info['name'],
				'href' => $this->url->link('extension/information/information_category', 'info_path=' . $this->request->get['info_path'])
			);

			$data['heading_title'] = $information_category_info['name'];
			
			$data['text_refine'] = $this->language->get('text_refine');
			
			$data['button_list'] = $this->language->get('button_list');
			$data['button_grid'] = $this->language->get('button_grid');
			$data['button_continue'] = $this->language->get('button_continue');

			if ($information_category_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($information_category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
			} else {
				$data['thumb'] = '';
			}
			
			
			$data['description'] = html_entity_decode($information_category_info['description'], ENT_QUOTES, 'UTF-8');

			$data['categories'] = array();
			
			$results = $this->model_extension_catalog_information_category->getCategories($information_category_id);
			
			foreach ($results as $result) {
				
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				$data['categories'][] = array(
					'information_category_id'  => $cat . '_' . $result['information_category_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'href'        => $this->url->link('extension/information/information_category', 'info_path=' . $this->request->get['info_path'] . '_' . $result['information_category_id'])
				);
			}
			
			$data['informations'] = array();
			
			$results = $this->model_extension_catalog_information_category->getInformationsByCategoryId($information_category_id);
			
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}
				
				$description = utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_product_description_length')) . '..';
				

				$data['informations'][] = array(
					'information_id'  => $cat . '_' . $result['information_category_id'],
					'thumb'       => $image,
					'name'        => $result['title'],
					'description' => $description,
					'href'        => $this->url->link('information/information', 'info_path=' . $this->request->get['info_path'] . '&information_id=' . $result['information_id'])
				);
			}
			
			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('extension/information/information_category', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('extension/information/information_category', 'info_path=' . $this->request->get['info_path'])
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	
	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('extension/catalog/information_category');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'cd.name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_extension_catalog_information_category->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_information_id' => $result['category_information_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}