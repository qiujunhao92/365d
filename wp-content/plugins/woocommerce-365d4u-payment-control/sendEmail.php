<?php
 // send email after pay
function send_custom_email_on_payment_complete( $order_id ) {
    if ( ! $order_id ) {
        return;
    }

    // 获取订单对象
    $order = wc_get_order( $order_id );
    $recipient_email = 'salted_fish@qq.com';
    $homeUrl = home_url() ?? '';
    try{
        if (str_contains($homeUrl, 'test') || str_contains($homeUrl, '365d4u.com')) {
            $recipient_email= 'zsc@365d4u.com'; //测试环境
        }
        $payment_time = current_time('mysql'); // 获取当前时间
        $payment_time = date('Y-m-d H:i:s', strtotime($payment_time) - 8*3600);
        $sourcePayTime =  $order->get_meta('_payment_time') ?? '';
        if (!empty($sourcePayTime)) {
            $payment_time = $sourcePayTime;
        }
        update_post_meta( $order_id, 'payment_date', $payment_time); // 更新自定义字段
        $sourceType = $order->get_meta('_wc_order_attribution_source_type') ?? '';

    } catch (\Exception $ex) {
        $order->add_order_note("error update:" . $ex->getMessage() );
        $order->save();
        $sourceType = 'unknown';
    }

    $subject = 'Order Pay Success#' . $order_id;
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $body = '<h1>Your Order ' . $order_id . ' had been paid, and changed to ' . $order->get_status(). '</h1><br><table>';
    if (!empty($order)) {
        $email = $order->get_billing_email() ?? '';
        $firstName = $order->get_shipping_first_name() ?? '';
        $lastName = $order->get_shipping_last_name() ?? '';
        $address1 = $order->get_shipping_address_1();
        $address2 = $order->get_shipping_address_2() ?? '';
        $city = $order->get_shipping_city();
        $postCode = $order->get_shipping_postcode();
        $shippingCountry = $order->get_shipping_country();
        $shippingState = $order->get_shipping_state() ?? '';
        $phone = $order->get_shipping_phone();

        $billing_firstName = $order->get_billing_first_name() ?? '';
        $billing_lastName = $order->get_billing_last_name() ?? '';
        $billing_address1 = $order->get_billing_address_1() ?? '';
        $billing_address2 = $order->get_billing_address_2() ?? '';
        $billing_city = $order->get_billing_city();
        $billing_postCode = $order->get_billing_postcode() ?? '';
        $billing_Country = $order->get_billing_country() ?? '';
        $billing_State = $order->get_billing_state();
        $billing_phone = $order->get_billing_phone();
        $total = $order->get_total() ?? 0;
        $sTotal =  wc_price($total, array( 'currency' => $order->get_currency() ) );
        $paymentMethod =  $order->get_payment_method() ?? '';

        if (str_contains($paymentMethod, 'ppcp-')) {
            $paymentMethod = 'paypal';
        }
        if (is_string($address2) && trim($address2)!='') {
            $address2 = '<br>' . $address2;
        }
        if (is_string($billing_address2) && trim($billing_address2)!='') {
            $billing_address2 = '<br>' . $billing_address2;
        }
        $shippingAddress = "{$firstName} {$lastName}<br>{$address1}{$address2}<br>{$city},{$shippingState},{$shippingCountry}<br>{$postCode}";
        $billingAddress = "{$billing_firstName} {$billing_lastName}<br>{$billing_address1}{$billing_address2}<br>{$billing_city},{$billing_State},{$billing_Country}<br>{$billing_postCode}";
        $body .= <<<HTML
<tr><td class="firstcol">Email Address:</td> <td class="secondcol">{$email} <br>{$firstName} {$lastName}</td></tr>
<tr><td class="firstcol">Order Amount:</td> <td class="secondcol">{$sTotal}</td></tr>
<tr><td class="firstcol">Payment Method:</td> <td class="secondcol">{$paymentMethod}</td></tr>
<tr><td class="firstcol">Shipping Address:</td> <td class="secondcol">{$shippingAddress}</td></tr>
<tr><td class="firstcol">Shipping Phone:</td> <td class="secondcol">{$phone}</td></tr>
<tr><td class="firstcol">Billing Address:</td> <td class="secondcol">{$billingAddress}</td></tr>
<tr><td class="firstcol">Billing Phone:</td> <td class="secondcol">{$billing_phone}</td></tr>
<tr><td class="firstcol">Source:</td> <td class="secondcol">{$sourceType}</td></tr>
<tr><td class="firstcol">Website:</td> <td class="secondcol">{$homeUrl}</td></tr>
HTML;
   }
   $data = get_transient('oceancreditcard_paysuccess');
   if (!empty($data) && isset($data[$order_id])) {
        foreach ($data[$order_id] as $key => $val) {
            if (is_array($val)) {
                $val = json_encode($val);
            }
            $body .= " <tr><td>{$key}</td> <td>{$val}</td></tr>";
        }
        delete_transient('oceancreditcard_paysuccess');
    }
    $body = $body . '</table>';
    $body .= <<<STYLE
<style>
        table,table tr th, table tr td { border:1px solid black; }
        table {  border-collapse: collapse;}   
        td.firstcol{ min-width:150px;}
        td.secondcol{ min-width:300px;}
 </style>
STYLE;

    try{
        // 发送邮件
        wp_mail( $recipient_email, $subject, $body, $headers );
        //send FeiShu http request
        sendHttpRequest($order);
    } catch (\Exception $ex) {
        $formatted_message = date('[Y-m-d H:i:s] ') . 'send email error:' . $ex->getMessage();
        // 将消息写入 debug.log 文件
        error_log($formatted_message);
    }
}

// 定义一个函数来写入日志
function write_to_log($message) {
    $log_file_dir =  WP_CONTENT_DIR . '/uploads/feiShuHttp';
    if (!is_dir($log_file_dir)) {
        @mkdir($log_file_dir, 0777, true);
    }
    $log_file_path = $log_file_dir . '/paidHttp-' . date('Ymd') . '.log';
    $date = date('Y-m-d H:i:s');
    $log_entry = "$date - $message\n";
    file_put_contents($log_file_path, $log_entry, FILE_APPEND);
}
/**
 * @param \WC_Order $order
 * @return void
 */
function sendHttpRequest($order)
{

    $feiShuSetting =new FeiShuSetting();
    list($enableFeiShu, $feiShuUrl, $feiShuAuth) = $feiShuSetting->getFeiShuUrlAuth();
    if ($enableFeiShu !== 'yes' || empty($feiShuUrl) || empty($feiShuAuth)) {
        return;
    }
    $orderId = $order->get_id();
    $email = $order->get_billing_email() ?? '';
    $firstName = $order->get_shipping_first_name() ?? '';
    $lastName = $order->get_shipping_last_name() ?? '';
    $address1 = $order->get_shipping_address_1();
    $address2 = $order->get_shipping_address_2() ?? '';
    $city = $order->get_shipping_city();
    $postCode = $order->get_shipping_postcode();
    $shippingCountry = $order->get_shipping_country();
    $shippingState = $order->get_shipping_state() ?? '';
    $phone = $order->get_shipping_phone();
    $shippingAddress = "{$firstName} {$lastName}" . PHP_EOL . "{$address1}{$address2}" . PHP_EOL . "{$city},{$shippingState},{$shippingCountry}" . PHP_EOL . "{$postCode}";
    $shippingName = trim($firstName . ' ' . $lastName);
    $total = $order->get_total() ?? 0;
    $payment_date = get_post_meta($order->get_id(), 'payment_date', true);
    $sRetDate = '';
    if ($payment_date) {
        $sRetDate = date('Y-m-d H:i:s', strtotime($payment_date) + 8 * 3600);
    }
    $postData = [
        "email" => $email,
        "paypal_transaction_id"=> cusMyGetTransIdVal($order, true),
        "ocean_transaction_id" => cusMyGetTransIdVal($order, false),
        "order_no"=>$order->get_id() . '',
        "pay_time" =>$sRetDate,
        "phone"  =>  $phone,
        "address" =>  $shippingAddress,
        "amount" => floatval($total),
        "method" => $order->get_payment_method() ?? '',
        "name" => $shippingName,
        "order_date"=> $order->get_date_created()->date('Y-m-d H:i:s')
    ];
    // Encode data to JSON
    $json_data = json_encode($postData);
    write_to_log('------------------------------------------------');
    write_to_log($orderId . '--> send Http request:' . $feiShuUrl);
    write_to_log($orderId .'-->  params:' . $json_data);
    // Send the POST request with Bearer Token and JSON body
    $response = wp_remote_post($feiShuUrl, array(
        'body'    => $json_data,
        'headers' => array(
            'Authorization' => 'Bearer ' . $feiShuAuth,
            'Content-Type'  => 'application/json',
        ),
    ));
    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        write_to_log($orderId . "--> Something went wrong: $error_message");
    } else {
        // Retrieve the response body
        $body = wp_remote_retrieve_body($response);
        // Process the response
        write_to_log($orderId . "--> Response: " . $body);
    }

}
function cusMyGetTransIdVal($order, $bIsPayPal)
{
    if (!$order) {
        return '';
    }
    $method = strtolower($order->get_payment_method() ?? '');
    $transactionId = $order->get_transaction_id() ?? '';
    $bMethodIsPaypal = (strstr($method, 'ppcp') || strstr($method, 'paypal'));
    if ($bIsPayPal && $bMethodIsPaypal) {
        return $transactionId;
    } elseif(!$bIsPayPal && !$bMethodIsPaypal) {
        return $transactionId;
    }
    return  '';
}

add_action( 'woocommerce_order_status_pending_to_processing', 'send_custom_email_on_payment_complete' );
add_action( 'woocommerce_order_status_pending_to_completed', 'send_custom_email_on_payment_complete' );
add_action( 'woocommerce_order_status_failed_to_completed', 'send_custom_email_on_payment_complete' );
add_action( 'woocommerce_order_status_failed_to_processing', 'send_custom_email_on_payment_complete' );
add_action( 'woocommerce_order_status_on-hold_to_processing', 'send_custom_email_on_payment_complete' );
add_action( 'woocommerce_order_status_on-hold_to_completed', 'send_custom_email_on_payment_complete' );
add_action( 'woocommerce_order_status_cancelled_to_completed', 'send_custom_email_on_payment_complete' );
add_action( 'woocommerce_order_status_cancelled_to_processing', 'send_custom_email_on_payment_complete' );
