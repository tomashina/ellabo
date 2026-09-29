<?php
class ModelExtensionTotalB2cRounding extends Model {

	public function getTotal(&$total) {
		$this->load->language('extension/total/b2c_rounding');

		if (!$this->config->get('total_b2c_rounding_status')) {
			return;
		}

		// payment_code
		$payment_code = $this->session->data['payment_method']['code']
			?? ($this->session->data['payment_code'] ?? '');

		if ($payment_code === '') {
			return;
		}

		// customer_group_id = 2 => pravna osoba
		$customer_group_id = 0;

		// guest checkout
		if (!empty($this->session->data['guest']['customer_group_id'])) {
			$customer_group_id = (int)$this->session->data['guest']['customer_group_id'];
		}

		// register/login (fallback)
		if (!$customer_group_id && !empty($this->session->data['customer_group_id'])) {
			$customer_group_id = (int)$this->session->data['customer_group_id'];
		}

		// logged-in kupac (fallback)
		if (!$customer_group_id && isset($this->customer) && method_exists($this->customer, 'isLogged') && $this->customer->isLogged()) {
			$customer_group_id = (int)$this->customer->getGroupId();
		}

		$is_legal_entity = ($customer_group_id === 2);

		// 2.0: pravna osoba + bank_transfer => bez korekcije
		if ($is_legal_entity && $payment_code === 'bank_transfer') {
			return;
		}

		// --- B2C (1.0): željeni total = zbroj stavki + bruto dostava ---
		$desired_total = 0.0;

		foreach ($this->cart->getProducts() as $product) {
			$desired_total += round((float)$product['total'], 2);
		}

		$shipping_total = 0.0;
		if (!empty($this->session->data['shipping_method']['cost'])) {
			$shipping_total = (float)$this->session->data['shipping_method']['cost'];

			if ($this->config->get('config_tax') && !empty($this->session->data['shipping_method']['tax_class_id'])) {
				$shipping_total = (float)$this->tax->calculate(
					$shipping_total,
					(int)$this->session->data['shipping_method']['tax_class_id'],
					true
				);
			}
		}

		$desired_total += round($shipping_total, 2);

		$desired_total = round($desired_total, 2);

		$current_total = (float)$total['total'];
		$diff = round($desired_total - $current_total, 2);

		if (abs($diff) < 0.01) {
			return;
		}

		$total['totals'][] = array(
			'code'       => 'b2c_rounding',
			'title'      => $this->language->get('text_title'),
			'value'      => $diff,
			'sort_order' => (int)$this->config->get('total_b2c_rounding_sort_order')
		);

		$total['total'] = $current_total + $diff;
	}
}
