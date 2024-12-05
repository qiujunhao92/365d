<?php
if (!class_exists('ExportOrderManager')) {
     class ExportOrderManager {

         protected $fieldsExport = [
             'order_number',
             'order_status',
             'order_date',
             'payment_date',
             'payment_method',
             'payment_method_title',
             'paypal_transaction_id',
             'ocean_transaction_id',
             'order_total',
             'order_source',
             'receiver_name',
             'billing_email',
             'receiver_phone',
             'receiver_address'
         ];

         public function __construct() {

         }

         /**
          * Get custom columns which not in table
          *
          * @return array
          */
         private function getCustomColumns() {
             $arrAddedColumns = [];
             $arrAddedColumns['payment_date'] = [
                 'segment' => 'common',
                 'key' => 'payment_date',
                 'label' => 'Pay Time',
                 'format' => 'string',
                 'colname' => 'Pay Time',
             ];
             $arrAddedColumns['order_source'] = [
                 'segment' => 'common',
                 'key' => 'order_source',
                 'label' => 'Source',
                 'format' => 'string',
                 'colname' => 'Source',
             ];
             $arrAddedColumns['payment_method'] = [
                 'segment' => 'common',
                 'key' => 'payment_method',
                 'label' => 'Payment method',
                 'format' => 'string',
                 'colname' => 'Payment method',
             ];
             $arrAddedColumns['paypal_transaction_id'] = [
                 'segment' => 'common',
                 'key' => 'paypal_transaction_id',
                 'label' => 'PayPal Transaction ID',
                 'format' => 'string',
                 'colname' => 'PayPal Transaction ID',
             ];
             $arrAddedColumns['ocean_transaction_id'] = [
                 'segment' => 'common',
                 'key' => 'ocean_transaction_id',
                 'label' => 'Ocean Payment ID',
                 'format' => 'string',
                 'colname' => 'Ocean Payment ID',
             ];
             $arrAddedColumns['receiver_name'] = [
                 'segment' => 'common',
                 'key' => 'receiver_name',
                 'label' => 'Shipping Name',
                 'format' => 'string',
                 'colname' => 'Shipping Name',
             ];
             $arrAddedColumns['receiver_phone'] = [
                 'segment' => 'common',
                 'key' => 'receiver_phone',
                 'label' => 'Phone',
                 'format' => 'string',
                 'colname' => 'Phone',
             ];
             $arrAddedColumns['receiver_address'] = [
                 'segment' => 'common',
                 'key' => 'receiver_address',
                 'label' => 'Address',
                 'format' => 'string',
                 'colname' => 'Address',
             ];
             return $arrAddedColumns;
         }
         function add_custom_fields_to_export($settings, $mode) {
             if (isset($settings['order_fields']) && is_array($settings['order_fields'])) {
                 $columns = $settings['order_fields'];
                 $arrMapColumns = array_column($columns, null, 'key');
                 $arrMapColumns = array_merge($arrMapColumns, $this->getCustomColumns());
                 $arrNewColumn = [];
                 foreach ($this->fieldsExport as $colKey){
                     if (isset($arrMapColumns[$colKey])) {
                         $arrNewColumn[] = $arrMapColumns[$colKey];
                     }
                 }
                 $settings['order_fields'] = $arrNewColumn;
             }

             return $settings;
         }

         function cus_can_add_field($bRet, $default) {
             if (!$default || !isset($default['key'])) {
                return false;
             }
             return $bRet;
         }


         function custom_365d_get_order_ids($sql)
         {
             $sql = str_replace('FROM wp_wc_orders AS orders', "FROM wp_wc_orders AS orders LEFT JOIN wp_postmeta AS meta0 ON ( orders.id = meta0.post_id  AND meta0.meta_key = 'payment_date')", $sql);
             $sql .= " ORDER BY CAST(ifnull(meta0.meta_value,'') AS CHAR) DESC,orders.ID DESC";
             return $sql;
         }


         function custom_365d_get_order_ids_order_by($sql)
         {
             $sql = preg_replace('/ORDER BY .* (ASC|DESC|asc|desc)/', '', $sql);
             return trim($sql);
         }

         function cus_add_export_style()
         {
             echo <<<STYLE
 <style>
   div#my-export-date-field label:nth-child(3), div#my-export-date-field label:nth-child(5) {
      display: none;
   }  
   #export-block-left div#my-sort
   {
       display: none;
   }
   #export-block-right div#my-order div {
    display: none;
    }
   div#export-block-right .my-block:not(:first-child) {
    display: none;
    }
    div#summary_report_by_products,
    div#summary_report_by_customers,
    #export_job_settings #preview-btn,
    #export_job_settings #save-only-btn,
    #export_job_settings #export-wo-pb-btn,
    #export_job_settings #reset-profile{
       display: none;
    }
    .nav-tab:not(.nav-tab-active) {
       display: none;
    }
 </style>
<script>
   document.getElementById('adjust-fields-btn').parentNode.style.display = 'none';
   document.getElementById('my-misc').parentNode.style.display = 'none';
   document.getElementById('my-quick-export-btn').parentNode.style.display = 'none'; 
   document.getElementById('summary_report_by_products').remove(); 
   document.getElementById('summary_report_by_customers').remove(); 
   
</script>
STYLE;

         }



         function custom_get_paypal_transaction_id($value, $order)
         {
             return $this->cusGetTransIdVal($order, $value, true);
         }

         function custom_get_ocean_transaction_id($value, $order)
         {
             return $this->cusGetTransIdVal($order, $value, false);
         }

         function cusGetTransIdVal($order, $value, $bIsPayPal)
         {
             if (!$order) {
                 return $value;
             }
             $transactionId = $order->get_transaction_id() ?? '';
             $method = strtolower($order->get_payment_method() ?? '');
             $bMethodIsPaypal = (strstr($method, 'ppcp') || strstr($method, 'paypal'));
             if ($bIsPayPal && $bMethodIsPaypal) {
                 return $transactionId;
             } elseif(!$bIsPayPal && !$bMethodIsPaypal) {
                 return $transactionId;
             }
             return  '';
         }
         /**
          * Get Format date
          *
          * @param $orderId
          * @return string
          */
         private  function formatCustomPaymentDate($orderId)
         {
             $payment_date = get_post_meta($orderId, 'payment_date', true);
             $sRetDate = '';
             if ($payment_date) {
                 $sRetDate = date('Y-m-d H:i:s', strtotime($payment_date) + 8 * 3600);
             }
             return $sRetDate;
         }


         function custom_get_payment_date($value, $order)
         {
             if (!$order) {
                 return $value;
             }
             $id = $order->get_id() ?? 0;
             return $this->formatCustomPaymentDate($id);
         }

         function cus_get_order_ids_where($where, $settings)
         {
             if ($settings['export_rule_field'] && $settings['export_rule_field'] == 'date_paid') {
                 foreach ($where as $k => $val) {
                     if (str_contains($val, 'wp_wc_order_operational_data AS order_date_paid WHERE')) {
                         unset($where[$k]);
                         $arrMeta = [];
                         if (!empty($settings['from_date'])) {
                             $arrMeta[] = "meta0.meta_value>='". date('Y-m-d H:i:s', strtotime($settings['from_date']) -8 *3600) . "'";
                         }
                         if (!empty($settings['to_date'])) {
                             $arrMeta[] = "meta0.meta_value<'".   date('Y-m-d H:i:s',strtotime($settings['to_date']) + 16 *60 * 60 ) . "'";
                         }
                         if (!empty($arrMeta)) {
                             $where[$k] = 'meta0.meta_value is not null AND ' . implode(' AND ' , $arrMeta);
                         }
                     }
                 }
             }
             if(!empty($settings['cuspaymethod'])) {
                 switch ($settings['cuspaymethod']){
                     case "ocean_all":
                         $where[] = 'orders.payment_method like "ocean%"';
                         break;
                     case "paypal":
                         $where[] = 'orders.payment_method like "ppcp%"';
                         break;
                     case "credit_card":
                         $where[] = 'orders.payment_method like "oceancredit%"';
                         break;
                     case "apple_pay":
                         $where[] = 'orders.payment_method like "oceanapple%"';
                         break;
                 }
             }
             return $where;
         }

         function custom_get_order_source($value, $order)
         {
             if (!$order) {
                 return $value;
             }
             return $order->get_meta('_wc_order_attribution_source_type') ?? '';
         }

         function custom_get_receiver_name($value, $order)
         {
             if (!$order) {
                 return $value;
             }

             $firstName = $order->get_shipping_first_name() ?? '';
             $lastName = $order->get_shipping_last_name() ?? '';
             return trim($firstName . ' ' . $lastName);
         }
         function custom_get_receiver_address($value, $order)
         {
             if (!$order) {
                 return $value;
             }
             $firstName = $order->get_shipping_first_name() ?? '';
             $lastName = $order->get_shipping_last_name() ?? '';
             $address1 = $order->get_shipping_address_1();
             $address2 = $order->get_shipping_address_2() ?? '';
             $city = $order->get_shipping_city();
             $postCode = $order->get_shipping_postcode();
             $shippingCountry = $order->get_shipping_country();
             $shippingState = $order->get_shipping_state() ?? '';
             $shippingAddress = "{$firstName} {$lastName}" . PHP_EOL . "{$address1}{$address2}" . PHP_EOL . "{$city},{$shippingState},{$shippingCountry}" . PHP_EOL . "{$postCode}";
             return trim($shippingAddress);
         }
         function custom_get_receiver_phone($value, $order)
         {
             if (!$order) {
                 return $value;
             }
             return $order->get_shipping_phone() ?? '';
         }

         function custom_get_payment_method($value, $order)
         {
             if (!$order) {
                 return $value;
             }
             return $order->get_payment_method() ?? '';
         }

         function cus_form_filter_by_order($settings)
         {
             $arrOption = [
                 '' => 'ALL',
                 'ocean_all' => 'Credit Card&amp;Apply Pay',
                 'paypal' => 'Paypal',
                 'credit_card' => 'Credit Card',
                 'apple_pay' => 'Apple Pay'
             ];
             $selectedOption = $_GET['method-filter'] ?? '';
             $sOption = '';
             foreach ($arrOption as $key=>$val) {
                 $sOption .= '<option value="' . $key .'">'. $val .'</option>';
             }
             echo <<<HTML
                 <div class="pay-method-line" style="display: block;padding: 15px 0;" >
                  <span class="wc-oe-header">Payment method:</span>
                    <select id="payment_method" name="settings[cuspaymethod]"  style="width: 100%; max-width: 60%;">
                         {$sOption}
                    </select>
                 </div>
                 
HTML;

         }

     }
    $exportManager = new ExportOrderManager();
    add_filter('woocommerce_order_get__receiver_name', array($exportManager, 'custom_get_receiver_name'), 10, 2);
    add_filter('woocommerce_order_get__receiver_address', array($exportManager, 'custom_get_receiver_address'), 10, 2);
    add_filter('woocommerce_order_get__receiver_phone', array($exportManager, 'custom_get_receiver_phone'), 10, 2);

    add_filter('woocommerce_order_get__ocean_transaction_id', array($exportManager,'custom_get_ocean_transaction_id'), 10, 2);
    add_filter('woocommerce_order_get__payment_date', array($exportManager, 'custom_get_payment_date'), 10, 2);
    add_filter('woocommerce_order_get__order_source', array($exportManager, 'custom_get_order_source'), 10, 2);
    add_filter('woocommerce_order_get__payment_method', array($exportManager, 'custom_get_payment_method'), 10, 2);
    add_filter('woocommerce_order_get__paypal_transaction_id',  array($exportManager, 'custom_get_paypal_transaction_id'), 10, 2);
    add_filter('woe_sql_get_order_ids_order_by', array($exportManager, 'custom_365d_get_order_ids_order_by'), 10, 1);
    add_filter('woe_sql_get_order_ids',  array($exportManager, 'custom_365d_get_order_ids'), 10, 1);
    add_filter('woe_before_apply_default_settings',  array($exportManager,'add_custom_fields_to_export'), 10, 2);
    add_filter('woe_sql_get_order_ids_where',  array($exportManager,'cus_get_order_ids_where'), 10, 2);
    add_filter('woe_can_add_field',  array($exportManager,'cus_can_add_field'), 10, 2);
    add_action('woe_settings_above_buttons',  array($exportManager,'cus_add_export_style'), 10, 1);
    add_action('woe_ui_form_filter_by_order',  array($exportManager,'cus_form_filter_by_order'), 10, 1);

}