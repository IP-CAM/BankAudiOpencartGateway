<?php
class ModelPaymentBankAudi extends Model {
	public function getMethod($address, $total) {
		$this->language->load('payment/bankaudi');

	$status=$this->config->get('bankaudi_status');

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'bankaudi',
				'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('bankaudi_sort_order')
			);
		}

		return $method_data;
	}
}
?>
