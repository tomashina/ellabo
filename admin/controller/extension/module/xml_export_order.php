<?php
class ControllerExtensionModuleXMLExportOrder extends Controller {
	private $error = array(); 


	public function saveCronItems(){
		$json = array();
    if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
  		$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('xml_export_order', $this->request->post);
		  $json['success'] = true;
    }
 		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
  }


	 
	public function index(){
		$this->language->load('extension/module/xml_export_order');
    $this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module/xml_export_order');
		$this->load->model('setting/setting');
			
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_extension_module_xml_export_order->exportOrders($this->request->post);
      header('Content-disposition: attachment; filename="XML-ORDER-EXPORT.xml"');
      header('Content-type: "text/xml"; charset="utf8"');
      readfile(str_replace(DIR_SYSTEM,'',$this->model_extension_module_xml_export_order->XMLFile()));
      die();
		}
		
		
	//own items
    $data['cron_items'] = array();
		$cron_items = $this->model_setting_setting->getSetting('xml_export_order');
    if(isset($cron_items['xml_export_order_items'])){
      $data['cron_items'] = $cron_items['xml_export_order_items'];
    }
    
    
    
				
		$data['heading_title']            = $this->language->get('heading_title');
		$data['text_export']              = $this->language->get('text_export');
		$data['button_cancel']            = $this->language->get('button_cancel');
		$data['text_order_status']        = $this->language->get('text_order_status');
		$data['text_order_status_all']    = $this->language->get('text_order_status_all');
		$data['text_order_date']          = $this->language->get('text_order_date');
		$data['text_price_currency_code'] = $this->language->get('text_price_currency_code');
		$data['text_yes']                 = $this->language->get('text_yes');
		$data['text_no']                  = $this->language->get('text_no');
		$data['text_store']               = $this->language->get('text_store');
		$data['text_all_stores']          = $this->language->get('text_all_stores');
		$data['text_item_type']           = $this->language->get('text_item_type');
		$data['text_item_type_1']         = $this->language->get('text_item_type_1');
		$data['text_item_type_2']         = $this->language->get('text_item_type_2');
		$data['text_item_type_3']         = $this->language->get('text_item_type_3');
		$data['text_cron_items']          = $this->language->get('text_cron_items');
		$data['text_save_cron_items']     = $this->language->get('text_save_cron_items');
		$data['text_export_items']        = $this->language->get('text_export_items');
		$data['text_cron_link']           = $this->language->get('text_cron_link');
		$data['text_date_start']          = $this->language->get('text_date_start');
		$data['text_date_stop']           = $this->language->get('text_date_stop');
		$data['text_filter']              = $this->language->get('text_filter');
		$data['text_cron_items_saved']    = $this->language->get('text_cron_items_saved');



    $data['order_status']        = $this->model_extension_module_xml_export_order->getOrderStatus();
    $data['stores']              = $this->model_extension_module_xml_export_order->getStores();



$export_items = array(
'order_id',
'product_id',
'payment_firstname',
'shipping_firstname',

'invoice_no',
'product_name',
'payment_lastname',
'shipping_lastname',

'invoice_prefix',
'product_model',
'payment_company',
'shipping_company',

'accept_language',
'product_quantity',
'payment_address_1',
'shipping_address_1',

'total',
'product_price',
'payment_address_2',
'shipping_address_2',

'currency_id',
'product_total',
'payment_city',
'shipping_city',

'currency_code',
'product_tax',
'payment_postcode',
'shipping_postcode',

'currency_value',
'product_reward',
'payment_country',
'shipping_country',

'comment',
'product_option',
'payment_country_id',
'shipping_country_id',

'order_status_id',
'date_added',
'payment_zone',
'shipping_zone',

'firstname',
'date_modified',
'payment_zone_id',
'shipping_zone_id',

'lastname',
'commission',
'payment_method',
'shipping_method',

'email',
'customer_group_id',
'payment_code',
'shipping_code',

'telephone',
'affiliate_id',
'payment_address_format',
'shipping_address_format',

'fax',
'language_id',
'store_id',
'store_url',


'user_agent',
'customer_id',
'store_name',
'ip',
);




    
    $data['items'] = array();
    if($export_items){
      foreach($export_items as $item){
        $data['items'][$item] = $this->language->get('export_'.$item);
      }
    }


















  		$data['breadcrumbs'] = array();
   		$data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('text_home'),
  			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
    		'separator' => false
   		);
   		$data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('text_module'),
	    	'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL'),
    		'separator' => ' :: '
   		);
   		$data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('heading_title'),
	    	'href'      => $this->url->link('extension/module/xml_export_order', 'user_token=' . $this->session->data['user_token'], 'SSL'),
    		'separator' => ' :: '
   		);
		

		$data['action']          = str_replace('&amp;','&',$this->url->link('extension/module/xml_export_order', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		$data['link_cron_items'] = str_replace('&amp;','&',$this->url->link('extension/module/xml_export_order/saveCronItems', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		$data['cancel']          = str_replace('&amp;','&',$this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		$data['user_token']      = $this->session->data['user_token'];
		$data['year']            = date("Y");
		$data['cron_link']       = HTTP_CATALOG.'index.php?route=extension/module/xml_export_order';




		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/xml_export_order', $data));
		
	}
	
}
?>