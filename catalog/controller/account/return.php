<?php
class ControllerAccountReturn extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/return');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/return', $url, true)
		);

		$this->load->model('account/return');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['returns'] = array();

		$return_total = $this->model_account_return->getTotalReturns();

		$results = $this->model_account_return->getReturns(($page - 1) * 10, 10);

		foreach ($results as $result) {
			$data['returns'][] = array(
				'return_id'  => $result['return_id'],
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'href'       => $this->url->link('account/return/info', 'return_id=' . $result['return_id'] . $url, true)
			);
		}

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		$pagination->url = $this->url->link('account/return', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($return_total) ? (($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) + 1 : 0, ((($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) > ($return_total - $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'))) ? $return_total : ((($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) + $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')), $return_total, ceil($return_total / $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')));

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/return_list', $data));
	}

	public function info() {
		$this->load->language('account/return');

		if (isset($this->request->get['return_id'])) {
			$return_id = $this->request->get['return_id'];
		} else {
			$return_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/return');

		$return_info = $this->model_account_return->getReturn($return_id);

		if ($return_info) {
			$this->document->setTitle($this->language->get('text_return'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/return', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_return'),
				'href' => $this->url->link('account/return/info', 'return_id=' . $this->request->get['return_id'] . $url, true)
			);

			$data['return_id'] = $return_info['return_id'];
			$data['order_id'] = $return_info['order_id'];
			$data['date_ordered'] = date($this->language->get('date_format_short'), strtotime($return_info['date_ordered']));
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($return_info['date_added']));
			$data['firstname'] = $return_info['firstname'];
			$data['lastname'] = $return_info['lastname'];
			$data['email'] = $return_info['email'];
			$data['telephone'] = $return_info['telephone'];
			$data['product'] = $return_info['product'];
			$data['model'] = $return_info['model'];
			$data['quantity'] = $return_info['quantity'];
			$data['reason'] = $return_info['reason'];
			$data['opened'] = $return_info['opened'] ? $this->language->get('text_yes') : $this->language->get('text_no');
			$data['comment'] = nl2br(htmlspecialchars((string)$return_info['comment'], ENT_QUOTES, 'UTF-8'));
			$data['action'] = $return_info['action'];

			$data['histories'] = array();

			$results = $this->model_account_return->getReturnHistories($this->request->get['return_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => nl2br(htmlspecialchars((string)$result['comment'], ENT_QUOTES, 'UTF-8'))
				);
			}

			$data['continue'] = $this->url->link('account/return', $url, true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/return_info', $data));
		} else {
			$this->document->setTitle($this->language->get('text_return'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/return', '', true)
			);

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_return'),
				'href' => $this->url->link('account/return/info', 'return_id=' . $return_id . $url, true)
			);

			$data['continue'] = $this->url->link('account/return', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function add() {
		$this->load->language('account/return');

		$this->load->model('account/return');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->request->post = $this->sanitizeReturnInput($this->request->post);

			if ($this->validate()) {
				$return_data = $this->prepareReturnData($this->request->post);
				$return_id = $this->model_account_return->addReturn($return_data);

				$this->sendReturnEmails($return_id, $return_data);

				$this->response->redirect($this->url->link('account/return/success', '', true));
			}
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/return/add', '', true)
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['order_id'])) {
			$data['error_order_id'] = $this->error['order_id'];
		} else {
			$data['error_order_id'] = '';
		}

		if (isset($this->error['date_ordered'])) {
			$data['error_date_ordered'] = $this->error['date_ordered'];
		} else {
			$data['error_date_ordered'] = '';
		}

		if (isset($this->error['firstname'])) {
			$data['error_firstname'] = $this->error['firstname'];
		} else {
			$data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$data['error_lastname'] = $this->error['lastname'];
		} else {
			$data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		if (isset($this->error['return_products'])) {
			$data['error_return_products'] = $this->error['return_products'];
		} else {
			$data['error_return_products'] = '';
		}

		if (isset($this->error['reason'])) {
			$data['error_reason'] = $this->error['reason'];
		} else {
			$data['error_reason'] = '';
		}

		if (isset($this->error['refund_iban'])) {
			$data['error_refund_iban'] = $this->error['refund_iban'];
		} else {
			$data['error_refund_iban'] = '';
		}

		$data['action'] = $this->url->link('account/return/add', '', true);

		$this->load->model('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_account_order->getOrder($this->request->get['order_id']);
		}

		$this->load->model('catalog/product');

		if (isset($this->request->get['product_id'])) {
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		}

		if (isset($this->request->post['invoice_number'])) {
			$data['invoice_number'] = $this->request->post['invoice_number'];
		} elseif (!empty($order_info) && !empty($order_info['invoice_no'])) {
			$data['invoice_number'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
		} elseif (!empty($order_info)) {
			$data['invoice_number'] = $order_info['order_id'];
		} else {
			$data['invoice_number'] = '';
		}

		if (isset($this->request->post['order_id'])) {
			$data['order_id'] = (int)$this->request->post['order_id'];
		} elseif (!empty($order_info)) {
			$data['order_id'] = (int)$order_info['order_id'];
		} else {
			$data['order_id'] = 0;
		}

		if (isset($this->request->post['invoice_date'])) {
			$data['invoice_date'] = $this->request->post['invoice_date'];
		} elseif (!empty($order_info)) {
			$data['invoice_date'] = date('Y-m-d', strtotime($order_info['date_added']));
		} else {
			$data['invoice_date'] = '';
		}

		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($order_info)) {
			$data['firstname'] = $order_info['firstname'];
		} else {
			$data['firstname'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} elseif (!empty($order_info)) {
			$data['lastname'] = $order_info['lastname'];
		} else {
			$data['lastname'] = $this->customer->getLastName();
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($order_info)) {
			$data['email'] = $order_info['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} elseif (!empty($order_info)) {
			$data['telephone'] = $order_info['telephone'];
		} else {
			$data['telephone'] = $this->customer->getTelephone();
		}

		if (isset($this->request->post['product_id'])) {
			$data['product_id'] = (int)$this->request->post['product_id'];
		} elseif (!empty($product_info)) {
			$data['product_id'] = (int)$product_info['product_id'];
		} else {
			$data['product_id'] = 0;
		}

		if (isset($this->request->post['return_products'])) {
			$data['return_products'] = $this->getReturnProducts($this->request->post, true);
		} elseif (!empty($product_info)) {
			$data['return_products'] = array(array(
				'code'     => $product_info['model'],
				'quantity' => '1',
				'price'    => ''
			));
		} else {
			$data['return_products'] = array(array(
				'code'     => '',
				'quantity' => '1',
				'price'    => ''
			));
		}

		if (isset($this->request->post['return_reason_id'])) {
			$data['return_reason_id'] = $this->request->post['return_reason_id'];
		} else {
			$data['return_reason_id'] = '';
		}

		$this->load->model('localisation/return_reason');

		$data['return_reasons'] = $this->localizeReturnReasons($this->model_localisation_return_reason->getReturnReasons());

		if (isset($this->request->post['comment'])) {
			$data['comment'] = $this->request->post['comment'];
		} else {
			$data['comment'] = '';
		}

		if (isset($this->request->post['refund_iban'])) {
			$data['refund_iban'] = $this->request->post['refund_iban'];
		} else {
			$data['refund_iban'] = '';
		}

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('return', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_return_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->request->post['agree'])) {
			$data['agree'] = $this->request->post['agree'];
		} else {
			$data['agree'] = false;
		}

		$data['back'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/return_form', $data));
	}

	protected function sanitizeReturnInput($data) {
		$single_line_fields = array(
			'invoice_number' => 64,
			'invoice_date'   => 10,
			'firstname'      => 32,
			'lastname'       => 32,
			'email'          => 96,
			'telephone'      => 32,
			'refund_iban'    => 64
		);

		foreach ($single_line_fields as $field => $max_length) {
			$data[$field] = $this->sanitizePlainText(isset($data[$field]) ? $data[$field] : '', $max_length);
		}

		$data['comment'] = $this->sanitizePlainText(isset($data['comment']) ? $data['comment'] : '', 2000, true);
		$data['order_id'] = isset($data['order_id']) ? (int)$data['order_id'] : 0;
		$data['product_id'] = isset($data['product_id']) ? (int)$data['product_id'] : 0;
		$data['return_reason_id'] = isset($data['return_reason_id']) ? (int)$data['return_reason_id'] : 0;
		$data['return_products'] = $this->getReturnProducts($data, true);

		return $data;
	}

	protected function sanitizePlainText($value, $max_length = 0, $multiline = false) {
		$value = html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');
		$value = strip_tags($value);
		$value = str_replace(array("\r\n", "\r"), "\n", $value);
		$value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

		if (!$multiline) {
			$value = preg_replace('/\s+/u', ' ', $value);
		}

		$value = trim($value);

		if ($max_length && utf8_strlen($value) > $max_length) {
			$value = utf8_substr($value, 0, $max_length);
		}

		return $value;
	}

	protected function prepareReturnData($data) {
		$return_products = $this->getReturnProducts($data);
		$first_product = reset($return_products);
		$order_id = 0;

		if (!empty($data['order_id'])) {
			$this->load->model('account/order');
			$order_info = $this->model_account_order->getOrder((int)$data['order_id']);

			if ($order_info) {
				$order_id = (int)$order_info['order_id'];
			}
		}

		$product_codes = array();

		foreach ($return_products as $product) {
			$product_codes[] = $product['code'];
		}

		$product_summary = implode(', ', $product_codes);

		if (utf8_strlen($product_summary) > 255) {
			$product_summary = utf8_substr($product_summary, 0, 252) . '...';
		}

		$data['order_id'] = $order_id;
		$data['date_ordered'] = $data['invoice_date'];
		$data['product_id'] = isset($data['product_id']) ? (int)$data['product_id'] : 0;
		$data['product'] = $product_summary;
		$data['model'] = utf8_substr($first_product['code'], 0, 64);
		$data['quantity'] = (int)$first_product['quantity'];
		$data['opened'] = 0;
		$data['return_products'] = $return_products;
		$data['customer_comment'] = $data['comment'];
		$data['comment'] = $this->buildReturnStoredComment($data, $return_products);

		return $data;
	}

	protected function getReturnProducts($data, $include_empty = false) {
		$return_products = array();
		$products = isset($data['return_products']) && is_array($data['return_products']) ? $data['return_products'] : array();

		foreach (array_slice($products, 0, 20) as $product) {
			if (!is_array($product)) {
				continue;
			}

			$return_product = array(
				'code'     => $this->sanitizePlainText(isset($product['code']) ? $product['code'] : '', 64),
				'quantity' => $this->sanitizePlainText(isset($product['quantity']) ? $product['quantity'] : '', 10),
				'price'    => $this->sanitizePlainText(isset($product['price']) ? $product['price'] : '', 32)
			);

			if ($include_empty || $return_product['code'] !== '' || $return_product['quantity'] !== '' || $return_product['price'] !== '') {
				$return_products[] = $return_product;
			}
		}

		if ($include_empty && !$return_products) {
			$return_products[] = array(
				'code'     => '',
				'quantity' => '1',
				'price'    => ''
			);
		}

		return $return_products;
	}

	protected function validateReturnProducts($return_products) {
		if (!$return_products) {
			return false;
		}

		foreach ($return_products as $product) {
			if ($product['code'] === '' || utf8_strlen($product['code']) > 64) {
				return false;
			}

			if (!ctype_digit($product['quantity']) || (int)$product['quantity'] < 1 || (int)$product['quantity'] > 9999) {
				return false;
			}

			if ($product['price'] === '' || utf8_strlen($product['price']) > 32) {
				return false;
			}
		}

		return true;
	}

	protected function buildReturnStoredComment($data, $return_products) {
		$lines = array(
			$this->language->get('entry_invoice_number') . ': ' . $data['invoice_number'],
			$this->language->get('entry_invoice_date') . ': ' . $data['invoice_date'],
			$this->language->get('entry_refund_iban') . ': ' . $data['refund_iban'],
			'',
			$this->language->get('text_return_products_title') . ':'
		);

		foreach ($return_products as $index => $product) {
			$lines[] = ($index + 1) . '. ' . $this->language->get('entry_product_code') . ': ' . $product['code'] . ', ' . $this->language->get('entry_quantity') . ': ' . $product['quantity'] . ', ' . $this->language->get('entry_price') . ': ' . $product['price'];
		}

		if ($data['customer_comment'] !== '') {
			$lines[] = '';
			$lines[] = $this->language->get('entry_fault_detail') . ':';
			$lines[] = $data['customer_comment'];
		}

		return implode("\n", $lines);
	}

	protected function localizeReturnReasons($return_reasons) {
		$language_keys = array(
			'dead on arrival'               => 'return_reason_dead_on_arrival',
			'faulty, please supply details' => 'return_reason_faulty',
			'order error'                   => 'return_reason_order_error',
			'other, please supply details'  => 'return_reason_other',
			'received wrong item'           => 'return_reason_wrong_item'
		);

		foreach ($return_reasons as &$return_reason) {
			$name = isset($return_reason['name']) ? trim($return_reason['name']) : '';
			$lookup = function_exists('utf8_strtolower') ? utf8_strtolower($name) : strtolower($name);

			if (isset($language_keys[$lookup])) {
				$translated = $this->language->get($language_keys[$lookup]);

				if ($translated !== $language_keys[$lookup]) {
					$return_reason['name'] = $translated;
				}
			}
		}
		unset($return_reason);

		return $return_reasons;
	}

	protected function sendReturnEmails($return_id, $data) {
		try {
			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			$customer_email = isset($data['email']) ? $data['email'] : '';
			$mail_from = $this->getReturnMailFrom();
			$return_reason = '';

			$this->load->model('localisation/return_reason');

			foreach ($this->localizeReturnReasons($this->model_localisation_return_reason->getReturnReasons()) as $reason) {
				if ((int)$reason['return_reason_id'] === (int)$data['return_reason_id']) {
					$return_reason = $reason['name'];
					break;
				}
			}

			$message_details = $this->buildReturnEmailDetails($return_id, $data, $return_reason);
			$admin_message = html_entity_decode($this->language->get('mail_return_admin_intro'), ENT_QUOTES, 'UTF-8') . "\n\n" . $message_details;
			$admin_subject = sprintf($this->language->get('mail_return_admin_subject'), $store_name, $return_id);

			$admin_recipients = $this->getReturnAdminRecipients();

			if (!$admin_recipients) {
				$this->log->write('Return form mail warning: no valid admin email recipient configured.');
			}

			foreach ($admin_recipients as $admin_email) {
				try {
					$mail = $this->createReturnMail();
					$mail->setTo($admin_email);
					$mail->setFrom($mail_from);
					$mail->setSender($store_name);

					if (filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
						$mail->setReplyTo($customer_email);
					}

					$mail->setSubject($admin_subject);
					$mail->setText($admin_message);
					$mail->send();
				} catch (Exception $e) {
					$this->log->write('Return form admin mail error: ' . $e->getMessage());
				}
			}

			if (filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
				$customer_message = html_entity_decode($this->language->get('mail_return_customer_intro'), ENT_QUOTES, 'UTF-8') . "\n\n";
				$customer_message .= $message_details;
				$customer_message .= "\n" . html_entity_decode($this->language->get('mail_return_customer_footer'), ENT_QUOTES, 'UTF-8') . "\n";

				$mail = $this->createReturnMail();
				$mail->setTo($customer_email);
				$mail->setFrom($mail_from);
				$mail->setSender($store_name);
				$mail->setSubject(sprintf($this->language->get('mail_return_customer_subject'), $store_name, $return_id));
				$mail->setText($customer_message);
				$mail->send();
			}
		} catch (Exception $e) {
			$this->log->write('Return form mail error: ' . $e->getMessage());
		}
	}

	protected function getReturnMailFrom() {
		$smtp_username = trim((string)$this->config->get('config_mail_smtp_username'));
		$store_email = trim((string)$this->config->get('config_email'));

		if (filter_var($smtp_username, FILTER_VALIDATE_EMAIL)) {
			return $smtp_username;
		}

		return $store_email;
	}

	protected function getReturnAdminRecipients() {
		$recipients = array();
		$emails = array_merge(
			array((string)$this->config->get('config_email')),
			preg_split('/[\s,;]+/', (string)$this->config->get('config_mail_alert_email'), -1, PREG_SPLIT_NO_EMPTY)
		);

		foreach ($emails as $email) {
			$email = trim($email);

			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$recipients[] = $email;
			}
		}

		return array_values(array_unique($recipients));
	}

	protected function createReturnMail() {
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		return $mail;
	}

	protected function buildReturnEmailDetails($return_id, $data, $return_reason) {
		$fields = array(
			$this->language->get('mail_return_label_return_id') => $return_id,
			$this->language->get('entry_invoice_number') => $data['invoice_number'],
			$this->language->get('entry_invoice_date') => $data['invoice_date'],
			$this->language->get('entry_firstname') => $data['firstname'],
			$this->language->get('entry_lastname') => $data['lastname'],
			$this->language->get('entry_email') => $data['email'],
			$this->language->get('entry_telephone') => $data['telephone'],
			$this->language->get('entry_reason') => $return_reason,
			$this->language->get('entry_refund_iban') => $data['refund_iban']
		);
		$message = '';

		foreach ($fields as $label => $value) {
			$message .= $label . ': ' . $this->sanitizePlainText($value, 255) . "\n";
		}

		$message .= "\n" . $this->language->get('text_return_products_title') . ":\n";

		foreach ($data['return_products'] as $index => $product) {
			$message .= ($index + 1) . '. ' . $this->language->get('entry_product_code') . ': ' . $product['code'] . ', ' . $this->language->get('entry_quantity') . ': ' . $product['quantity'] . ', ' . $this->language->get('entry_price') . ': ' . $product['price'] . "\n";
		}

		if ($data['customer_comment'] !== '') {
			$message .= "\n" . $this->language->get('entry_fault_detail') . ":\n" . $data['customer_comment'] . "\n";
		}

		return $message;
	}

	protected function validate() {
		$invoice_number = isset($this->request->post['invoice_number']) ? $this->request->post['invoice_number'] : '';
		$invoice_date = isset($this->request->post['invoice_date']) ? $this->request->post['invoice_date'] : '';

		if ($invoice_number === '' || utf8_strlen($invoice_number) > 64) {
			$this->error['order_id'] = $this->language->get('error_order_id');
		}

		$date = DateTime::createFromFormat('Y-m-d', $invoice_date);

		if (!$date || $date->format('Y-m-d') !== $invoice_date) {
			$this->error['date_ordered'] = $this->language->get('error_date_ordered');
		}

		$firstname = isset($this->request->post['firstname']) ? $this->request->post['firstname'] : '';
		$lastname = isset($this->request->post['lastname']) ? $this->request->post['lastname'] : '';
		$email = isset($this->request->post['email']) ? $this->request->post['email'] : '';
		$telephone = isset($this->request->post['telephone']) ? $this->request->post['telephone'] : '';

		if ((utf8_strlen($firstname) < 1) || (utf8_strlen($firstname) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen($lastname) < 1) || (utf8_strlen($lastname) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($email) > 96) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($telephone) < 3) || (utf8_strlen($telephone) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if (!$this->validateReturnProducts($this->getReturnProducts($this->request->post))) {
			$this->error['return_products'] = $this->language->get('error_return_products');
		}

		$return_reason_id = isset($this->request->post['return_reason_id']) ? (int)$this->request->post['return_reason_id'] : 0;
		$valid_reason = false;

		$this->load->model('localisation/return_reason');

		foreach ($this->model_localisation_return_reason->getReturnReasons() as $reason) {
			if ((int)$reason['return_reason_id'] === $return_reason_id) {
				$valid_reason = true;
				break;
			}
		}

		if (!$valid_reason) {
			$this->error['reason'] = $this->language->get('error_reason');
		}

		$iban = strtoupper(preg_replace('/\s+/', '', isset($this->request->post['refund_iban']) ? $this->request->post['refund_iban'] : ''));

		if (!preg_match('/^[A-Z]{2}[0-9A-Z]{13,32}$/', $iban)) {
			$this->error['refund_iban'] = $this->language->get('error_refund_iban');
		}

		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('return', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		return !$this->error;
	}

	public function success() {
		$this->load->language('account/return');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/return', '', true)
		);

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}
