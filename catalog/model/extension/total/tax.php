<?php
class ModelExtensionTotalTax extends Model {
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

		if (!$this->config->get('config_tax')) {
			return;
		}

		$decimals = (int)$this->currency->getDecimalPlace($this->session->data['currency']);

		// 1.0: nema document-level PDV stavke
		if (!$this->isFiscal20()) {
			// Fizička osoba (customer_group_id = 1): prikaži informativnu PDV stavku.
			if ($this->getActiveCustomerGroupId() !== 1) {
				return;
			}

			$gross_base = 0.0;
			$has_base = false;

			if (!empty($total['totals'])) {
				foreach ($total['totals'] as $t) {
					if (empty($t['code'])) {
						continue;
					}

					if ($t['code'] === 'sub_total' || $t['code'] === 'shipping') {
						$gross_base += (float)$t['value'];
						$has_base = true;
					}
				}
			}

			if (!$has_base || $gross_base <= 0) {
				return;
			}

			$vat = round($gross_base - ($gross_base / 1.25), $decimals);

			if ($vat <= 0) {
				return;
			}

			$total['totals'][] = array(
				'code'       => 'tax',
				'title'      => 'PDV (uključen 25%)',
				'value'      => $vat,
				'sort_order' => $this->config->get('total_tax_sort_order')
			);

			return;
		}

		$base = 0.0;
		$has_subtotal = false;

		if (!empty($total['totals'])) {
			foreach ($total['totals'] as $t) {
				if (empty($t['code'])) continue;

				if ($t['code'] === 'sub_total') {
					$base += (float)$t['value'];
					$has_subtotal = true;
				}

				if ($t['code'] === 'shipping') {
					$base += (float)$t['value'];
				}
			}
		}

		if (!$has_subtotal || $base <= 0) {
			return;
		}

		$vat = round($base * 0.25, $decimals);

		$total['taxes'] = array();

		if ($vat > 0) {
			$total['totals'][] = array(
				'code'       => 'tax',
				'title'      => 'PDV (25%)',
				'value'      => $vat,
				'sort_order' => $this->config->get('total_tax_sort_order')
			);

			$total['total'] += $vat;
		}
	}
}
