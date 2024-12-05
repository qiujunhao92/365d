<?php

if (!class_exists('CustomerInvoiceEmail')) {
     class CustomerInvoiceEmail
     {
         /**
          * @param $order_id
          * @param $order
          * @return void
          */
         public function sendNewOrderEmail($order_id, $order)
         {
             $recipient_email = $order->customer_note;
             $recipient_email && $recipient_email = trim($recipient_email);
             if (empty($recipient_email)|| !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
                 return;
             }
             $sourceEmail = get_post_meta($order_id, '_send_invoice_email') ?? '';
             if (!empty($sourceEmail) && $sourceEmail === $recipient_email) {
                 error_log('Invoice Email-->Email had been sent to ' . $sourceEmail .  'before! #' . $order_id);
                 return;
             }
             $plugin_dir = plugin_dir_path(__FILE__);
             $plugin_dir = rtrim($plugin_dir, '\\/') . '/';
             $templateData =  file_get_contents($plugin_dir . 'template/new_order.html');
             $url =  esc_url( $order->get_checkout_payment_url());
             $allFees = $order->get_fees();
             if (!$allFees || count($allFees)==0) {
                 error_log('Invoice Email--> Empty Fees #' . $order_id);
                 return;
             }
             $fee = array_values($allFees)[0];
             $feePrice =  wc_price((float)$fee->get_total(), array( 'currency' => $order->get_currency()));
             $arrTemplateVar =[
                 '{{ORDER_ID}}' => $order_id,
                 '{{PAY_URL}}' => $url,
                 '{{PRICE}}' => $feePrice,
                 '{{PRODUCT}}' => $fee->get_name(),
                 '{{SITE_URL}}' => 'https://www.custom365d.com'
             ];
             $templateData = str_replace(array_keys($arrTemplateVar), array_values($arrTemplateVar), $templateData);
             try{
                 update_post_meta($order_id, '_send_invoice_email', $recipient_email);
                 $subject = "Invoice #" . $order_id;
                 $headers = array('Content-Type: text/html; charset=UTF-8');
                 // 发送邮件
                 wp_mail( $recipient_email, $subject, $templateData, $headers );
                // $sb_admin_email 	= get_option('admin_email') ?? '';
                 $sb_admin_email 	= 'salted_fish@foxmail.com';
                 if (!empty($sb_admin_email)) {
                     $adminSubject = 'We sent your invoice #' . $order_id . ' for $' . number_format((float)$fee->get_total(),2) . ' USD';
                     $adminBody =  <<<HTML
<div>
  We sent your invoice #{$order_id} to {$recipient_email}  for {$feePrice},the content like below
  <br>
  ----------------------------------------------------------------------------------------
  <br>
  {$templateData}
</div> 
HTML;

                     wp_mail( $sb_admin_email, $adminSubject, $adminBody, $headers );
                 }

             } catch (\Exception $ex) {
                 $formatted_message = date('[Y-m-d H:i:s] #') . $order_id . ' send email error:' . $ex->getMessage();
                 // 将消息写入 debug.log 文件
                 error_log($formatted_message);
             }
         }


         public function CheckAndSendEmailOnCustomerNoteUpdate($post_id, $post) {
             // 获取订单对象
             $order = wc_get_order($post_id);
             if (empty($order)) {
                 return;
             }
             $this->sendNewOrderEmail($post_id, $order);
         }
     }
     $customInvoiceEmail = new CustomerInvoiceEmail();
     add_action("woocommerce_new_order", array($customInvoiceEmail, 'sendNewOrderEmail'), 10, 2);
     add_action('woocommerce_update_order',  array($customInvoiceEmail, 'sendNewOrderEmail'), 10, 2);

}