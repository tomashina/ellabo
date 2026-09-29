<?php
class ModelExtensionTotalShipping extends Model {
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
		if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
			$shipping_cost = (float)$this->session->data['shipping_method']['cost'];
			$shipping_value = $shipping_cost;
			$is_fiscal_20 = $this->isFiscal20();

			// 1.0 (fizicke osobe i ostali non-bank_transfer): prikaz/obracun dostave s PDV-om.
			if (!$is_fiscal_20 && $this->config->get('config_tax') && !empty($this->session->data['shipping_method']['tax_class_id'])) {
				$shipping_value = (float)$this->tax->calculate(
					$shipping_cost,
					(int)$this->session->data['shipping_method']['tax_class_id'],
					true
				);
			}

			$total['totals'][] = array(
				'code'       => 'shipping',
				'title'      => $this->session->data['shipping_method']['title'],
				'value'      => $shipping_value,
				'sort_order' => $this->config->get('total_shipping_sort_order')
			);

			if ($is_fiscal_20 && $this->session->data['shipping_method']['tax_class_id']) {
				$tax_rates = $this->tax->getRates($shipping_cost, $this->session->data['shipping_method']['tax_class_id']);

				foreach ($tax_rates as $tax_rate) {
					if (!isset($total['taxes'][$tax_rate['tax_rate_id']])) {
						$total['taxes'][$tax_rate['tax_rate_id']] = $tax_rate['amount'];
					} else {
						$total['taxes'][$tax_rate['tax_rate_id']] += $tax_rate['amount'];
					}
				}
			}

			$total['total'] += $shipping_value;
		}
	}
}
