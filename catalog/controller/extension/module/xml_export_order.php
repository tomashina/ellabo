<?php
class ControllerExtensionModuleXMLExportOrder extends Controller {
	public function index(){
    
/*================================================================================*/
/*=============================== PROFI XML EXPORT ===============================*/
/*==================== CREATED BY DEAWid : ALL RIGHTS RESERVED ===================*/
/*================================================================================*/
/*================================ www.deawid.com ================================*/
/*================================================================================*/



$filter = array(
  'order_status'      => '-',    //order status ID ... for all orders set "-"
  'order_date_start'  => '',     //order date from (YYY-MM-DD HH:ii:ss)
  'order_date_stop'   => '',     //order date to (YYY-MM-DD HH:ii:ss)
  'currency_code'     => true,   //all prices with currency code (true/false)
  'order_store'       => '-',    //orders from store ID ... for all stores set "-", default store = 0
  'show_result'       => true,    //if you want show xml, or onl create xml file
);

$items = array(
'order_id'                    => true,
'invoice_no'                  => true,
'invoice_prefix'              => true,
'order_products'              => true,
'total'                       => true,
'currency_id'                 => true,
'currency_code'               => true,
'currency_value'              => true,
'comment'                     => true,
'order_status_id'             => true,
'store_name'                  => true,
'firstname'                   => true,
'lastname'                    => true,
'email'                       => true,
'telephone'                   => true,
'fax'                         => true,
'date_added'                  => true,
'date_modified'               => true,


'affiliate_id'                => true,
'commission'                  => true,
'language_id'                 => true,
'store_id'                    => true,
'ip'                          => true,
'store_url'                   => true,
'customer_id'                 => true,
'customer_group_id'           => true,
'forwarded_ip'                => true,
'user_agent'                  => true,
'accept_language'             => true,

'product_id'                  => true,
'product_name'                => true,
'product_model'               => true,
'product_quantity'            => true,
'product_price'               => true,
'product_total'               => true,
'product_tax'                 => true,
'product_reward'              => true,
'product_option'              => true,

'payment_firstname'           => true,
'payment_lastname'            => true,
'payment_company'             => true,
'payment_company_id'          => true,
'payment_address_1'           => true,
'payment_address_2'           => true,
'payment_city'                => true,
'payment_postcode'            => true,
'payment_country'             => true,
'payment_country_id'          => true,
'payment_zone'                => true,
'payment_zone_id'             => true,
'payment_address_format'      => true,
'payment_method'              => true,
'payment_code'                => true,

'shipping_firstname'          => true,
'shipping_lastname'           => true,
'shipping_company'            => true,
'shipping_address_1'          => true,
'shipping_address_2'          => true,
'shipping_city'               => true,
'shipping_postcode'           => true,
'shipping_country'            => true,
'shipping_country_id'         => true,
'shipping_zone'               => true,
'shipping_zone_id'            => true,
'shipping_address_format'     => true,
'shipping_method'             => true,
'shipping_code'               => true,
'custom_field'               => true,
);





$export_data = array();
$export_data['filter'] = $filter;
foreach($items as $item => $export){
  if($export == true){
    $export_data['xml_export_order_items'][] = $item;
  }
}




require_once('admin/model/extension/module/xml_export_order.php');


$exportOrdersToXML = new ModelExtensionModuleXMLExportOrder($this->registry);
$exportOrdersToXML->exportOrders($export_data);


if($export_data['filter']['show_result']){
  header("Content-type: text/xml");
  $xml = file_get_contents($exportOrdersToXML->XMLFile());
  echo $xml;
}else{
  echo 'DONE';
}






  }
}
