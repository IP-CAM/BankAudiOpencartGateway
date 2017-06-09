<?php
class ControllerPaymentBankaudi extends Controller {
	public function submit(){
		$order_id=$this->session->data['order_id'];
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$SECURE_SECRET = $this->config->get("bankaudi_securesecret");
		$appendAmp = 0;
		$vpcURL = "";
		$newHash = "";
		$_POST['accessCode']=$this->config->get("bankaudi_accesscode");
		$_POST['merchTxnRef']="000000".$order_id;
		$_POST['merchant']=$this->config->get("bankaudi_merchant_id");
		$_POST['amount']=$order_info['total']*100;
		$_POST['returnURL']=$this->url->link('payment/bankaudi/callback&order_id='.$order_id, '', 'SSL');
		$_POST['orderInfo']="Order Id ".$order_id;

		// if the form is submitted undergo the below procedures
		if (isset($_POST['accessCode']))
		{
			ksort($_POST);
			$md5HashData = $SECURE_SECRET;

			foreach($_POST as $key => $value)
			{
					// create the md5 input and URL leaving out any fields that have no value
					if (strlen($value) > 0 && ($key == 'accessCode' || $key == 'merchTxnRef' || $key == 'merchant' || $key == 'orderInfo' || $key == 'amount' || $key == 'returnURL')) {
						//	print 'Key: '.$key.'  Value: '.$value."<br>";
							// this ensures the first paramter of the URL is preceded by the '?' char
							if ($appendAmp == 0)
							{
									$vpcURL .= urlencode($key) . '=' . urlencode($value);
									$appendAmp = 1;
							} else {
									$vpcURL .= '&' . urlencode($key) . "=" . urlencode($value);
							}
							$md5HashData .= $value;
					}
			}
			$this->log->write($md5HashData);
			$newHash .= $vpcURL."&vpc_SecureHash=" . strtoupper(md5($md5HashData));
		$url="https://gw1.audicards.com/TPGWeb/payment/prepayment.action?".$newHash;
		$this->log->write($url);
		$this->redirect($url);
				//exit;
		}
	}
	private function getResponseDescription($responseCode)
	{
			switch ($responseCode) {
					case "0" : $result = "Transaction Successful"; break;
					case "?" : $result = "Transaction status is unknown"; break;
					case "1" : $result = "Unknown Error"; break;
					case "2" : $result = "Bank Declined Transaction"; break;
					case "3" : $result = "No Reply from Bank"; break;
					case "4" : $result = "Expired Card"; break;
					case "5" : $result = "Insufficient funds"; break;
					case "6" : $result = "Error Communicating with Bank"; break;
					case "7" : $result = "Payment Server System Error"; break;
					case "8" : $result = "Transaction Type Not Supported"; break;
					case "9" : $result = "Bank declined transaction (Do not contact Bank)"; break;
					case "A" : $result = "Transaction Aborted"; break;
					case "C" : $result = "Transaction Cancelled"; break;
					case "D" : $result = "Deferred transaction has been received and is awaiting processing"; break;
					case "E" : $result = "Invalid Credit Card"; break;
					case "F" : $result = "3D Secure Authentication failed"; break;
					case "I" : $result = "Card Security Code verification failed"; break;
					case "G" : $result = "Invalid Merchant"; break;
					case "L" : $result = "Shopping Transaction Locked (Please try the transaction again later)"; break;
					case "N" : $result = "Cardholder is not enrolled in Authentication scheme"; break;
					case "P" : $result = "Transaction has been received by the Payment Adaptor and is being processed"; break;
					case "R" : $result = "Transaction was not processed - Reached limit of retry attempts allowed"; break;
					case "S" : $result = "Duplicate SessionID (OrderInfo)"; break;
					case "T" : $result = "Address Verification Failed"; break;
					case "U" : $result = "Card Security Code Failed"; break;
					case "V" : $result = "Address Verification and Card Security Code Failed"; break;
					case "X" : $result = "Credit Card Blocked"; break;
					case "Y" : $result = "Invalid URL"; break;
					case "B" : $result = "Transaction was not completed"; break;
					case "M" : $result = "Please enter all required fields"; break;
					case "J" : $result = "Transaction already in use"; break;
					case "BL" : $result = "Card Bin Limit Reached"; break;
					case "CL" : $result = "Card Limit Reached"; break;
					case "LM" : $result = "Merchant Amount Limit Reached"; break;
					case "Q" : $result = "IP Blocked"; break;
					case "R" : $result = "Transaction was not processed - Reached limit of retry attempts allowed"; break;
					case "Z" : $result = "Bin Blocked"; break;

					default  : $result = "Unable to be determined";
			}
			return $result;
	}
	private function null2unknown($data)
	{
			if ($data == "")
					return "No Value Returned";
			 else
					return $data;
	}
	protected function index() {
			$this->language->load('payment/bankaudi');
			$this->data['button_confirm'] = $this->language->get('button_confirm');
			$this->data['action'] = $this->url->link('payment/bankaudi/submit', '', 'SSL');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/bankaudi.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/bankaudi.tpl';
			} else {
				$this->template = 'default/template/payment/bankaudi.tpl';
			}

			$this->render();
		}

	public function callback() {
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('checkout/order');
		$this->language->load('checkout/success');
		$this->language->load('payment/bankaudi');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			if(isset($_GET["vpc_TxnResponseCode"])){


			$response = 	$txnResponseCode = $this->null2unknown(addslashes($_GET["vpc_TxnResponseCode"]));
			//the the fields passed from the url to be displayed
			$responses=array();
			if($txnResponseCode=="0")
			{
				$responses['amount'] = $this->null2unknown(addslashes($_GET["amount"])/100);
				$responses['receiptNo'] = $this->null2unknown(addslashes($_GET["vpc_ReceiptNo"]));
				$responses['transactionNo'] = $this->null2unknown(addslashes($_GET["vpc_TransactionNo"]));
			}
				$order_status_id = $this->config->get('bankaudi_canceled_reversal_status_id');
				$responses_str="";
				switch($txnResponseCode) {
					case 'P':
					//processing order
						$order_status_id = 2;
						break;

					case '0':
						$order_status_id=$this->config->get("bankaudi_completed_status_id");
						$responses_str=json_encode($responses);
						break;
						default:
						//cancelled order

							$order_status_id = 7;
							break;
				}
				$comment=$this->getResponseDescription($txnResponseCode)." ".strtoupper (str_replace(array("{","}",'"')," ",$responses_str));
				if (!$order_info['order_status_id']) {
					$this->model_checkout_order->confirm($order_id, $order_status_id,$comment,true);
				} else {
					$this->model_checkout_order->update($order_id, $order_status_id,$comment,true);
				}
				$this->data['heading_title'] = $this->language->get('heading_title');
				$this->data['text_message']="Gateway Response: ".$comment."<br>";
				if ($this->customer->isLogged()) {
					$this->data['text_message'] .= sprintf($this->language->get('text_customer_audi'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));
				} else {
					$this->data['text_message'] .= sprintf($this->language->get('text_guest_audi'), $this->url->link('information/contact'));
				}
				if (isset($this->session->data['order_id'])) {
						$this->cart->clear();

						unset($this->session->data['shipping_method']);
						unset($this->session->data['shipping_methods']);
						unset($this->session->data['payment_method']);
						unset($this->session->data['payment_methods']);
						unset($this->session->data['guest']);
						unset($this->session->data['comment']);
						unset($this->session->data['order_id']);
						unset($this->session->data['coupon']);
						unset($this->session->data['reward']);
						unset($this->session->data['voucher']);
						unset($this->session->data['vouchers']);
						unset($this->session->data['totals']);
					}


			}else{
				$comment="Transaction Incomplete.No Response";

				$this->data['heading_title'] = $this->language->get('heading_failed');
				$this->data['text_message']="Gateway Response: ".$comment."<br>";

			}

}else{
	$this->data['heading_title'] = $this->language->get('heading_invalid');
	$this->data['text_message']="";

}


			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'href'      => $this->url->link('common/home'),
				'text'      => $this->language->get('text_home'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'href'      => $this->url->link('checkout/cart'),
				'text'      => $this->language->get('text_basket'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
				'text'      => $this->language->get('text_checkout'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'href'      => $this->url->link('checkout/success'),
				'text'      => $this->language->get('text_success'),
				'separator' => $this->language->get('text_separator')
			);


			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/common/success.tpl';
			} else {
				$this->template = 'default/template/common/success.tpl';
			}

			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);

			$this->response->setOutput($this->render());
		}

}
?>
