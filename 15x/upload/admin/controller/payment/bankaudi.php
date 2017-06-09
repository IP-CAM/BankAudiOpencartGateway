<?php
class ControllerPaymentBankaudi extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/bankaudi');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('bankaudi', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_accesscode'] = $this->language->get('entry_accesscode');
		$this->data['entry_securesecret'] = $this->language->get('entry_securesecret');
		$this->data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
		$this->data['entry_canceled_reversal_status'] = $this->language->get('entry_canceled_reversal_status');
		$this->data['entry_completed_status'] = $this->language->get('entry_completed_status');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/bankaudi', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/bankaudi', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['bankaudi_status'])) {
			$this->data['bankaudi_status'] = $this->request->post['bankaudi_status'];
		} else {
			$this->data['bankaudi_status'] = $this->config->get('bankaudi_status');
		}
		if (isset($this->request->post['bankaudi_completed_status_id'])) {
			$this->data['bankaudi_completed_status_id'] = $this->request->post['bankaudi_completed_status_id'];
		} else {
			$this->data['bankaudi_completed_status_id'] = $this->config->get('bankaudi_completed_status_id');
		}
		if (isset($this->request->post['bankaudi_canceled_reversal_status_id'])) {
			$this->data['bankaudi_canceled_reversal_status_id'] = $this->request->post['bankaudi_canceled_reversal_status_id'];
		} else {
			$this->data['bankaudi_canceled_reversal_status_id'] = $this->config->get('bankaudi_canceled_reversal_status_id');
		}
		if (isset($this->request->post['bankaudi_sort_order'])) {
			$this->data['bankaudi_sort_order'] = $this->request->post['bankaudi_sort_order'];
		} else {
			$this->data['bankaudi_sort_order'] = $this->config->get('bankaudi_sort_order');
		}
		if (isset($this->request->post['entry_securesecret'])) {
			$this->data['bankaudi_securesecret'] = $this->request->post['bankaudi_securesecret'];
		} else {
			$this->data['bankaudi_securesecret'] = $this->config->get('bankaudi_securesecret');
		}
		if (isset($this->request->post['bankaudi_accesscode'])) {
			$this->data['bankaudi_accesscode'] = $this->request->post['bankaudi_accesscode'];
		} else {
			$this->data['bankaudi_accesscode'] = $this->config->get('bankaudi_accesscode');
		}
		if (isset($this->request->post['bankaudi_merchant_id'])) {
			$this->data['bankaudi_merchant_id'] = $this->request->post['bankaudi_merchant_id'];
		} else {
			$this->data['bankaudi_merchant_id'] = $this->config->get('bankaudi_merchant_id');
		}

		$this->template = 'payment/bankaudi.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/bankaudi')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}


		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>
