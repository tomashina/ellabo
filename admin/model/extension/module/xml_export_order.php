<?php 
class ModelExtensionModuleXMLExportOrder extends Model {

  /**
   * Preserve field contents while making them safe for an XML 1.0 text node.
   */
  private function escapeXmlText($value) {
    $escaped = htmlspecialchars((string)$value, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
    $clean = preg_replace(
      '/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u',
      '',
      $escaped
    );

    return $clean === null ? '' : $clean;
  }

  /**
   * Validate and publish atomically so readers never receive a partial XML file.
   */
  private function writeXmlFile($filename, $xml) {
    $previous_errors = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $is_valid = $document->loadXML($xml, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous_errors);

    if (!$is_valid) {
      throw new RuntimeException('Generated order XML is not valid. Existing export was preserved.');
    }

    $temporary_file = tempnam(dirname($filename), '.xml-export-');
    if ($temporary_file === false) {
      throw new RuntimeException('Unable to create a temporary order XML file.');
    }

    try {
      $written = file_put_contents($temporary_file, $xml, LOCK_EX);
      if ($written !== strlen($xml)) {
        throw new RuntimeException('Unable to write the complete order XML export.');
      }

      $permissions = file_exists($filename) ? (fileperms($filename) & 0777) : 0644;
      @chmod($temporary_file, $permissions);

      if (!rename($temporary_file, $filename)) {
        throw new RuntimeException('Unable to publish the order XML export.');
      }

      $temporary_file = null;
    } finally {
      if ($temporary_file !== null && file_exists($temporary_file)) {
        @unlink($temporary_file);
      }
    }
  }

  public function XMLFolder(){
    return DIR_SYSTEM.'../xml/';
  }

  public function XMLFile(){
    return DIR_SYSTEM.'../xml/xml_export_order.xml';
  }

  public function exportOrders($export_items) {

    $xml  = '';
    $xml .= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<ORDERS>'."\n";
    $xml .= '<DATE_CREATED>'.date("Y-m-d H:i",time()).'</DATE_CREATED>'."\n";

    $filter = $export_items['filter'] ?? [];

    $price_with_code = false;
    if (isset($filter['currency_code']) && (int)$filter['currency_code'] === 1){
      $price_with_code = true;
    }

    // uvijek izvozi proizvode
    $export_items['xml_export_order_items'][] = 'products';

    // filter zadnjih 6 mjeseci
    $filter_sql   = [];
    $filter_sql[] = "date_added >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";

    $sql = "SELECT * FROM `" . DB_PREFIX . "order`";
    $sql .= " WHERE ".implode(" AND ",$filter_sql);

    $query = $this->db->query($sql);

    if ($query->rows) {
      foreach ($query->rows as $order) {

        // samo status 5 (završeno) kao u tvom kodu
        if ((int)$order['order_status_id'] !== 5) {
          continue;
        }

        // dohvat custom_field preko vašeg modela (kako već radiš)
        $ord = Agmedia\Models\Order\Order::where('order_id', $order['order_id'])->first();
        $json = $ord ? json_decode($ord->custom_field) : null;

        $name  = $json && isset($json->{'2'}) ? (string)$json->{'2'} : '';
        $name1 = $json && isset($json->{'1'}) ? (string)$json->{'1'} : '';
        $oib   = $json && isset($json->{'3'}) ? trim((string)$json->{'3'}) : '';

        // režim prema specifikaciji:
        // Fiskalizacija 2.0 => customer_group_id = 2 i payment_code = bank_transfer
        $is_b2b = ((int)$order['customer_group_id'] === 2 && $order['payment_code'] === 'bank_transfer');

        // priprema za B2C izračun TOTAL-a (iz već spremljenih web vrijednosti)
        $sum_lines_export = 0.0;

        // dostava iz order_total (shipping) - bez dodatnog oporezivanja u exportu
        $shipping_export = round((float)$this->getShippingValue($order['order_id']), 2);

        $xml .= '<ORDER>'."\n";

        if (isset($export_items['xml_export_order_items'])) {
          foreach ($export_items['xml_export_order_items'] as $item) {

            // customer_id fallback
            $customer_id_value = ($order['customer_id'] == '0') ? $order['email'] : $order['customer_id'];

            if ($item == 'order_id')          { $xml .= '<ORDER_ID>'.$this->escapeXmlText($order['order_id']).'</ORDER_ID>'."\n"; }
            if ($item == 'invoice_no')        { $xml .= '<INVOICE_NUMBER>'.$this->escapeXmlText($order['invoice_no']).'</INVOICE_NUMBER>'."\n"; }
            if ($item == 'invoice_prefix')    { $xml .= '<INVOICE_PREFIX>'.$this->escapeXmlText($order['invoice_prefix']).'</INVOICE_PREFIX>'."\n"; }

            // ORDER_TOTAL: za B2B uzmi order total, za B2C izračunat ćemo kasnije (nakon stavki)
            if ($item == 'total') {
              // privremeno; upisat ćemo stvarnu vrijednost kasnije
              $xml .= '<ORDER_TOTAL>'.number_format((float)$order['total'], 2, '.', '').'</ORDER_TOTAL>'."\n";
            }

            if ($item == 'currency_id')       { $xml .= '<CURRENCY_ID>'.$this->escapeXmlText($order['currency_id']).'</CURRENCY_ID>'."\n"; }
            if ($item == 'currency_code')     { $xml .= '<CURRENCY_CODE>'.$this->escapeXmlText($order['currency_code']).'</CURRENCY_CODE>'."\n"; }
            if ($item == 'currency_value')    { $xml .= '<CURRENCY_VALUE>'.$this->escapeXmlText($order['currency_value']).'</CURRENCY_VALUE>'."\n"; }
            if ($item == 'comment')           { $xml .= '<ORDER_COMMENT>'.$this->escapeXmlText($order['comment']).'</ORDER_COMMENT>'."\n"; }
            if ($item == 'order_status_id')   { $xml .= '<ORDER_STATUS_ID>'.$this->escapeXmlText($order['order_status_id']).'</ORDER_STATUS_ID>'."\n"; }
            if ($item == 'store_name')        { $xml .= '<STORE_NAME>'.$this->escapeXmlText($order['store_name']).'</STORE_NAME>'."\n"; }
            if ($item == 'firstname')         { $xml .= '<CUSTOMER_FIRSTNAME>'.$this->escapeXmlText($order['firstname']).'</CUSTOMER_FIRSTNAME>'."\n"; }
            if ($item == 'lastname')          { $xml .= '<CUSTOMER_LASTNAME>'.$this->escapeXmlText($order['lastname']).'</CUSTOMER_LASTNAME>'."\n"; }
            if ($item == 'email')             { $xml .= '<CUSTOMER_EMAIL>'.$this->escapeXmlText($order['email']).'</CUSTOMER_EMAIL>'."\n"; }
            if ($item == 'telephone')         { $xml .= '<CUSTOMER_TELEPHONE>'.$this->escapeXmlText($order['telephone']).'</CUSTOMER_TELEPHONE>'."\n"; }
            if ($item == 'fax')               { $xml .= '<CUSTOMER_FAX>'.$this->escapeXmlText($order['fax']).'</CUSTOMER_FAX>'."\n"; }
            if ($item == 'date_added')        { $xml .= '<ORDER_DATE_ADDED>'.$this->escapeXmlText($order['date_added']).'</ORDER_DATE_ADDED>'."\n"; }
            if ($item == 'date_modified')     { $xml .= '<ORDER_DATE_MODIFIED>'.$this->escapeXmlText($order['date_modified']).'</ORDER_DATE_MODIFIED>'."\n"; }
            if ($item == 'affiliate_id')      { $xml .= '<AFFILIATE_ID>'.$this->escapeXmlText($order['affiliate_id']).'</AFFILIATE_ID>'."\n"; }
            if ($item == 'commission')        { $xml .= '<COMMISSION>'.$this->escapeXmlText($order['commission']).'</COMMISSION>'."\n"; }
            if ($item == 'language_id')       { $xml .= '<ORDER_LANGUAGE_ID>'.$this->escapeXmlText($order['language_id']).'</ORDER_LANGUAGE_ID>'."\n"; }
            if ($item == 'store_id')          { $xml .= '<ORDER_STORE_ID>'.$this->escapeXmlText($order['store_id']).'</ORDER_STORE_ID>'."\n"; }
            if ($item == 'ip')                { $xml .= '<ORDER_IP>'.$this->escapeXmlText($order['ip']).'</ORDER_IP>'."\n"; }
            if ($item == 'store_url')         { $xml .= '<STORE_URL>'.$this->escapeXmlText($order['store_url']).'</STORE_URL>'."\n"; }
            if ($item == 'customer_id')       { $xml .= '<CUSTOMER_ID>'.$this->escapeXmlText($customer_id_value).'</CUSTOMER_ID>'."\n"; }
            if ($item == 'customer_group_id') { $xml .= '<CUSTOMER_GROUP_ID>'.$this->escapeXmlText($order['customer_group_id']).'</CUSTOMER_GROUP_ID>'."\n"; }
            if ($item == 'forwarded_ip')      { $xml .= '<FORWARDED_IP>'.$this->escapeXmlText($order['forwarded_ip']).'</FORWARDED_IP>'."\n"; }
            if ($item == 'accept_language')   { $xml .= '<ACCEPT_LANGUAGE>'.$this->escapeXmlText($order['accept_language']).'</ACCEPT_LANGUAGE>'."\n"; }

            if ($item == 'payment_firstname') { $xml .= '<PAYMENT_FIRSTNAME>'.$this->escapeXmlText($order['payment_firstname']).'</PAYMENT_FIRSTNAME>'."\n"; }
            if ($item == 'payment_lastname')  { $xml .= '<PAYMENT_LASTNAME>'.$this->escapeXmlText($order['payment_lastname']).'</PAYMENT_LASTNAME>'."\n"; }
            if ($item == 'payment_company')   { $xml .= '<PAYMENT_COMPANY>'.$this->escapeXmlText($order['payment_company']).'</PAYMENT_COMPANY>'."\n"; }
            if ($item == 'payment_address_1') { $xml .= '<PAYMENT_ADDRESS_1>'.$this->escapeXmlText($order['payment_address_1']).'</PAYMENT_ADDRESS_1>'."\n"; }
            if ($item == 'payment_address_2') { $xml .= '<PAYMENT_ADDRESS_2>'.$this->escapeXmlText($order['payment_address_2']).'</PAYMENT_ADDRESS_2>'."\n"; }
            if ($item == 'payment_city')      { $xml .= '<PAYMENT_CITY>'.$this->escapeXmlText($order['payment_city']).'</PAYMENT_CITY>'."\n"; }
            if ($item == 'payment_postcode')  { $xml .= '<PAYMENT_POSTCODE>'.$this->escapeXmlText($order['payment_postcode']).'</PAYMENT_POSTCODE>'."\n"; }
            if ($item == 'payment_country')   { $xml .= '<PAYMENT_COUNTRY>'.$this->escapeXmlText($order['payment_country']).'</PAYMENT_COUNTRY>'."\n"; }
            if ($item == 'payment_country_id'){ $xml .= '<PAYMENT_COUNTRY_ID>'.$this->escapeXmlText($order['payment_country_id']).'</PAYMENT_COUNTRY_ID>'."\n"; }
            if ($item == 'payment_zone')      { $xml .= '<PAYMENT_ZONE>'.$this->escapeXmlText($order['payment_zone']).'</PAYMENT_ZONE>'."\n"; }
            if ($item == 'payment_zone_id')   { $xml .= '<PAYMENT_ZONE_ID>'.$this->escapeXmlText($order['payment_zone_id']).'</PAYMENT_ZONE_ID>'."\n"; }
            if ($item == 'payment_address_format'){ $xml .= '<PAYMENT_ADDRESS_FORMAT>'.$this->escapeXmlText($order['payment_address_format']).'</PAYMENT_ADDRESS_FORMAT>'."\n"; }
            if ($item == 'payment_method')    { $xml .= '<PAYMENT_METHOD>'.$this->escapeXmlText($order['payment_method']).'</PAYMENT_METHOD>'."\n"; }
            if ($item == 'payment_code')      { $xml .= '<PAYMENT_CODE>'.$this->escapeXmlText($order['payment_code']).'</PAYMENT_CODE>'."\n"; }

            if ($item == 'shipping_firstname'){ $xml .= '<SHIPPING_FIRSTNAME>'.$this->escapeXmlText($order['shipping_firstname']).'</SHIPPING_FIRSTNAME>'."\n"; }
            if ($item == 'shipping_lastname') { $xml .= '<SHIPPING_LASTNAME>'.$this->escapeXmlText($order['shipping_lastname']).'</SHIPPING_LASTNAME>'."\n"; }
            if ($item == 'shipping_company')  { $xml .= '<SHIPPING_COMPANY>'.$this->escapeXmlText($order['shipping_company']).'</SHIPPING_COMPANY>'."\n"; }
            if ($item == 'shipping_address_1'){ $xml .= '<SHIPPING_ADDRESS_1>'.$this->escapeXmlText($order['shipping_address_1']).'</SHIPPING_ADDRESS_1>'."\n"; }
            if ($item == 'shipping_address_2'){ $xml .= '<SHIPPING_ADDRESS_2>'.$this->escapeXmlText($order['shipping_address_2']).'</SHIPPING_ADDRESS_2>'."\n"; }
            if ($item == 'shipping_city')     { $xml .= '<SHIPPING_CITY>'.$this->escapeXmlText($order['shipping_city']).'</SHIPPING_CITY>'."\n"; }
            if ($item == 'shipping_postcode') { $xml .= '<SHIPPING_POSTCODE>'.$this->escapeXmlText($order['shipping_postcode']).'</SHIPPING_POSTCODE>'."\n"; }
            if ($item == 'shipping_country')  { $xml .= '<SHIPPING_COUNTRY>'.$this->escapeXmlText($order['shipping_country']).'</SHIPPING_COUNTRY>'."\n"; }
            if ($item == 'shipping_country_id'){ $xml .= '<SHIPPING_COUNTRY_ID>'.$this->escapeXmlText($order['shipping_country_id']).'</SHIPPING_COUNTRY_ID>'."\n"; }
            if ($item == 'shipping_zone')     { $xml .= '<SHIPPING_ZONE>'.$this->escapeXmlText($order['shipping_zone']).'</SHIPPING_ZONE>'."\n"; }
            if ($item == 'shipping_zone_id')  { $xml .= '<SHIPPING_ZONE_ID>'.$this->escapeXmlText($order['shipping_zone_id']).'</SHIPPING_ZONE_ID>'."\n"; }
            if ($item == 'shipping_address_format'){ $xml .= '<SHIPPING_ADDRESS_FORMAT>'.$this->escapeXmlText($order['shipping_address_format']).'</SHIPPING_ADDRESS_FORMAT>'."\n"; }
            if ($item == 'shipping_method')   { $xml .= '<SHIPPING_METHOD>'.$this->escapeXmlText($order['shipping_method']).'</SHIPPING_METHOD>'."\n"; }
            if ($item == 'shipping_code')     { $xml .= '<SHIPPING_CODE>'.$this->escapeXmlText($order['shipping_code']).'</SHIPPING_CODE>'."\n"; }

            // Dostava: šaljemo vrijednost (broj), ne XML blok
            if ($item == 'total') {
    $xml .= '<SHIPPING_PRICE>
                <NAME>Dostava</NAME>
                <VALUE>' . number_format($shipping_export, 2, '.', '') . '</VALUE>
             </SHIPPING_PRICE>' . "\n";
}

            // Custom fields
            if ($item == 'custom_field') { 
              $xml .= '<OIB>'.$this->escapeXmlText($oib).'</OIB>'."\n";
              $xml .= '<COMPANY>'.$this->escapeXmlText($name.$name1).'</COMPANY>'."\n";
            }

            // Proizvodi
            if ($item == 'products') {
              $xml .= '<ORDER_PRODUCTS>'."\n";
              $product_query = $this->db->query("SELECT order_product_id FROM " . DB_PREFIX . "order_product WHERE order_id = '".(int)$order['order_id']."'");
              foreach ($product_query->rows as $p) {
                $product_xml = $this->getOrderProduct(
                  $p['order_product_id'],
                  $export_items['xml_export_order_items'],
                  $order['currency_id'],
                  $price_with_code,
                  $is_b2b
                );
                $xml .= '<PRODUCT>'.$product_xml.'</PRODUCT>'."\n";

                // za B2C total zbrajamo već spremljene line total vrijednosti
                if (!$is_b2b) {
                  $sum_lines_export += $this->getOrderProductLineTotalExport($p['order_product_id']);
                }
              }
              $xml .= '</ORDER_PRODUCTS>'."\n";
            }
          }
        }

        // --- Ispravi ORDER_TOTAL ako je B2C ---
        // U ovom trenutku $xml već sadrži <ORDER_TOTAL> iz $order['total'].
        // Za B2C ga moramo zamijeniti našim izračunom.
        if (!$is_b2b) {
          $order_total_export = round($sum_lines_export + $shipping_export, 2);

          // zamjena samo unutar ovog ORDER bloka:
          // (brz i praktičan trik: zamijeni zadnji ORDER_TOTAL koji smo upisali)
          $xml = preg_replace(
            '#<ORDER_TOTAL>[^<]*</ORDER_TOTAL>\s*$#m',
            '<ORDER_TOTAL>'.number_format($order_total_export, 2, '.', '').'</ORDER_TOTAL>',
            $xml,
            1
          );
        }

        $xml .= '</ORDER>'."\n";
      }
    }

    $xml .= '</ORDERS>'."\n";

    if (!file_exists($this->XMLFolder())) {
      mkdir($this->XMLFolder());
    }

    $xml_filename = $this->XMLFile();

    $this->writeXmlFile($xml_filename, $xml);

    return $xml_filename;
  }

  // --- vraća shipping vrijednost kao broj (neto iz order_total shipping) ---
  public function getShippingValue($order_id) {
    $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "order_total WHERE code = 'shipping' AND order_id = '" . (int)$order_id . "' ORDER BY sort_order LIMIT 1");
    if (!empty($query->row['value'])) {
      return (float)$query->row['value'];
    }
    return 0.0;
  }

  // helper: B2C line total iz već spremljenog order_product.total
  private function getOrderProductLineTotalExport($order_product_id) {
    $q = $this->db->query("SELECT total FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$order_product_id . "' LIMIT 1");
    if (!$q->row) return 0.0;

    return round((float)$q->row['total'], 2);
  }

  public function getOrderProductOptions($order_product_id,$order_id) {
    $xml   = '';
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_product_id = '".(int)$order_product_id."' AND order_id = '".(int)$order_id."'");
    if(isset($query->rows)){
      foreach($query->rows as $option){
        $xml .= '<OPTION>'."\n";
        $xml .= '<NAME>'.$this->escapeXmlText($option['name']).'</NAME>'."\n";
        $xml .= '<VALUE>'.$this->escapeXmlText($option['value']).'</VALUE>'."\n";
        $xml .= '</OPTION>'."\n";
      }
    }
    return $xml;
  }

  public function getOrderProduct($order_product_id,$export_items,$currency_id,$price_with_code,$is_b2b) {
    $xml     = '';
    $query   = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_product_id = '".(int)$order_product_id."' LIMIT 1");
    $product = $query->row;

    foreach($export_items as $item){

      if($item == 'product_name')     { $xml .= '<NAME>'.$this->escapeXmlText($product['name']).'</NAME>'."\n"; }
      if($item == 'product_id')       { $xml .= '<ID>'.$this->escapeXmlText($order_product_id).'</ID>'."\n"; }
      if($item == 'product_model')    { $xml .= '<MODEL>'.$this->escapeXmlText($product['model']).'</MODEL>'."\n"; }
      if($item == 'product_quantity') { $xml .= '<QUANTITY>'.$this->escapeXmlText($product['quantity']).'</QUANTITY>'."\n"; }

      // PRICE / TOTAL: ovisno o režimu
      if($item == 'product_price' || $item == 'product_total') {

        $unit_stored = (float)$product['price'];
        $qty         = (int)$product['quantity'];

        if ($is_b2b) {
          // 2.0: neto
          $unit = round($unit_stored, 2);
          $line = round((float)$product['total'], 2);
        } else {
          // 1.0: koristi vrijednosti spremljene na web narudžbi (bez dodatnog ×1.25)
          $unit = round($unit_stored, 2);
          $line = round((float)$product['total'], 2);

          // fallback za stare narudžbe gdje total nije postavljen
          if ($line <= 0 && $qty > 0) {
            $line = round($unit * $qty, 2);
          }
        }

        if($item == 'product_price') {
          $xml .= '<PRICE>'.number_format($unit, 2, '.', '').'</PRICE>'."\n";
        }

        if($item == 'product_total') {
          $xml .= '<TOTAL>'.number_format($line, 2, '.', '').'</TOTAL>'."\n";
        }
      }

      if($item == 'product_option') { 
        $xml .= '<OPTIONS>'.$this->getOrderProductOptions($product['order_product_id'],$product['order_id']).'</OPTIONS>'."\n";
      }
    }

    return $xml;
  }

  public function getOrderStatus() {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status ORDER by name ASC");
    return $query->rows ? $query->rows : array();
  }

  public function getStores() {
    $return_stores = array();
    $query         = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE `key` = 'config_name'");
    $default_store = $query->row;

    $return_stores[0]['store_id'] = 0;
    $return_stores[0]['name']     = $default_store['value'];

    $query  = $this->db->query("SELECT store_id, name FROM " . DB_PREFIX . "store ORDER by name ASC");
    $stores = $query->rows;

    if($stores){
      $i = 1;
      foreach($stores as $store){
        $return_stores[$i] = $store;
        $i++;
      }
    }
    return $return_stores;
  }

  public function getPrice($price,$currency_id,$with_currency) {
    $query    = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency WHERE `currency_id` = '".(int)$currency_id."'");
    $currency = $query->row;
    if($with_currency){
      return $currency['symbol_left'].$price.$currency['symbol_right'];
    } else {
      return $price;
    }
  }
}
?>
