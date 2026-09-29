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
	private $error = array();
	private $breadcrumbs_types = array(
		'default'      => '',
		'direct'       => 'D',
		'short'        => 'S',
		'long'         => 'L',
		'last'         => 'T',
		'manufacturer' => 'M',
	);

	public function index() {
		$this->load->language('extension/module/breadcrumbs');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (('POST' == $this->request->server['REQUEST_METHOD']) && $this->validate()) {
			$this->model_setting_setting->editSetting('module_breadcrumbs', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link(
				'marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
			);
		}

		if (isset($this->error['permission'])) {
			$data['error_permission'] = $this->error['permission'];
		} else {
			$data['error_permission'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link(
				'marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true
			),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
				'extension/module/breadcrumbs',
				'user_token=' . $this->session->data['user_token'], true
			),
		);

		$data['action'] = $this->url->link(
			'extension/module/breadcrumbs', 'user_token=' . $this->session->data['user_token'], true
		);

		$data['cancel'] = $this->url->link(
			'marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true
		);

		if (isset($this->request->post['module_breadcrumbs_status'])) {
			$data['status'] = $this->request->post['module_breadcrumbs_status'];
		} else {
			$data['status'] = $this->config->get('module_breadcrumbs_status');
		}

		if (isset($this->request->post['module_breadcrumbs_type'])) {
			$data['breadcrumbs_type'] = $this->request->post['module_breadcrumbs_type'];
		} else {
			$data['breadcrumbs_type'] = $this->config->get('module_breadcrumbs_type');
		}

		$data['breadcrumbs_types'] = $this->breadcrumbs_types;

		if (isset($this->request->post['module_breadcrumbs_update_search'])) {
			$data['update_search'] = $this->request->post['module_breadcrumbs_update_search'];
		} else {
			$data['update_search'] = $this->config->get('module_breadcrumbs_update_search');
		}

		if (isset($this->request->post['module_breadcrumbs_update_manufacturer'])) {
			$data['update_manufacturer'] = $this->request->post['module_breadcrumbs_update_manufacturer'];
		} else {
			$data['update_manufacturer'] = $this->config->get('module_breadcrumbs_update_manufacturer');
		}

		if (!method_exists($this->document, 'addCustomScript') ||
			!method_exists($this->document, 'getCustomScripts')
		) {
			$data['breadcrumbs_json'] = false;
			$data['breadcrumbs_json_disabled'] = true;
		} else {
			if (isset($this->request->post['module_breadcrumbs_json'])) {
				$data['breadcrumbs_json'] = $this->request->post['module_breadcrumbs_json'];
			} else {
				$data['breadcrumbs_json'] = $this->config->get('module_breadcrumbs_json');
			}
		}



		if (isset($this->request->post['module_breadcrumbs_nolink'])) {
			$data['nolink'] = $this->request->post['module_breadcrumbs_nolink'];
		} else {
			$data['nolink'] = $this->config->get('module_breadcrumbs_nolink');
		}

		if (isset($this->request->post['module_breadcrumbs_bold'])) {
			$data['bold'] = $this->request->post['module_breadcrumbs_bold'];
		} else {
			$data['bold'] = $this->config->get('module_breadcrumbs_bold');
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/breadcrumbs', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/breadcrumbs')) {
			$this->error['permission'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$dir_stylesheet = DIR_CATALOG . 'view/theme/' .
			$this->config->get('theme_directory') . $this->config->get('config_theme') . '/stylesheet/';

		if ($dir_stylesheet) {
			$css_bold = $dir_stylesheet . 'breadcrumbs_plus_bold.css';
			$css_nolink = $dir_stylesheet . 'breadcrumbs_plus_nolink.css';

			if (!file_exists($css_bold)) {
				$css_text = "ul.breadcrumb li:last-child a {\n\tfont-weight: bold;\n}";
				file_put_contents($css_bold, $css_text, FILE_USE_INCLUDE_PATH);
			}

			if (!file_exists($css_nolink)) {
				$css_text = "ul.breadcrumb li:last-child a {\n\tcursor: default!important;\n\tpointer-events: none;\n\tcolor: inherit;\n}";
				file_put_contents($css_nolink, $css_text, FILE_USE_INCLUDE_PATH);
			}
		}

		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('breadcrumbs_update');
		$this->model_setting_event->deleteEventByCode('breadcrumbs_style');
		$this->model_setting_event->deleteEventByCode('breadcrumbs_script');

		// Catch all view/*/before to find breadcrumbs
		$this->model_setting_event->addEvent(
			'breadcrumbs_update',
			'catalog/view/*/before',
			'extension/module/breadcrumbs/updateBreadcrumbs'
		);

		//catalog/view/common/header/before
		$this->model_setting_event->addEvent(
			'breadcrumbs_style',
			'catalog/controller/common/header/before',
			'extension/module/breadcrumbs/styleBreadcrumbs'
		);

		//catalog/view/*/after
		$this->model_setting_event->addEvent(
			'breadcrumbs_script',
			'catalog/view/*/after',
			'extension/module/breadcrumbs/addJsonLdScript'
		);
	}

	public function uninstall() {
		$dir_stylesheet = DIR_CATALOG . 'view/theme/' .
			$this->config->get('theme_directory') .	$this->config->get('config_theme') . '/stylesheet/';

		if ($dir_stylesheet) {
			$css_bold = $dir_stylesheet . 'breadcrumbs_plus_bold.css';
			$css_nolink = $dir_stylesheet . 'breadcrumbs_plus_nolink.css';

			if (file_exists($css_bold)) {
				unlink($css_bold);
			}

			if (file_exists($css_nolink)) {
				unlink($css_nolink);
			}
		}

		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('breadcrumbs_update');
		$this->model_setting_event->deleteEventByCode('breadcrumbs_style');
		$this->model_setting_event->deleteEventByCode('breadcrumbs_script');
	}
}
