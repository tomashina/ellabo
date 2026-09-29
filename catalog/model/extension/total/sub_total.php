<?php
class ModelExtensionTotalSubTotal extends Model {
	private function getActiveCustomerGroupId() {
		if (!empty($this->session->data['guest']['customer_group_id'])) {
			return (int)$this->session->data['guest']['customer_group_id'];
		}

		if (!empty($this->session->data['customer_group_id'])) {
			return (int)$this->session->data['customer_group_id'];
		}

		if ($this->customer && $this->customer->isLogged()) {
			return (int)$this->customer->getGroupId();
		}

		return (int)$this->config->get('config_customer_group_id');
	}

	private function getPaymentCode() {
		if (!empty($this->session->data['payment_method']['code'])) {
			return (string)$this->session->data['payment_method']['code'];
		}

		if (!empty($this->session->data['payment_code'])) {
			return (string)$this->session->data['payment_code'];
		}

		return '';
	}

	private function isFiscal20() {
		return ($this->getActiveCustomerGroupId() === 2 && $this->getPaymentCode() === 'bank_transfer');
	}

	public function getTotal($total) {
		$this->load->language('extension/total/sub_total');

		$decimals = (int)$this->currency->getDecimalPlace($this->session->data['currency']);

		$sub_total = 0;
		$is_fiscal_20 = $this->isFiscal20();

		foreach ($this->cart->getProducts() as $product) {
			if ($is_fiscal_20) {
				// 2.0: VP/neto
				$sub_total += round((float)$product['total'], $decimals);
			} else {
				// 1.0: MP/bruto po stavci
				$line = round((float)$product['price'], $decimals) * (int)$product['quantity'];
				$sub_total += round($line, $decimals);
			}
		}

		// voucheri kao i prije
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$sub_total += (float)$voucher['amount'];
			}
		}

		$sub_total = round($sub_total, $decimals);

		$total['totals'][] = array(
			'code'       => 'sub_total',
			'title'      => $this->language->get('text_sub_total'),
			'value'      => $sub_total,
			'sort_order' => $this->config->get('sub_total_sort_order')
		);

		$total['total'] += $sub_total;
	}
}
