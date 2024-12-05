<?php

function get_paypal_orders($paypalOrderId) {
    $client_id = 'Afrf5WzJT-XN6FDetXV7T-IoyV5hHTnQ_s0qn0bJoYNw7VmbsoQKouMe_HkZO1MBoqDU5Pvhlf-ay7kw';
    $secret = 'EOScGqcIaMDLhb6l_wMnYTflA9-NecrKNL67Anp6S_8l7VAjKmX_EHAZ_85Q5fQcBlEWvc29KWcNKwFI';

    // 获取access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paypal.com/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $client_id . ":" . $secret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $headers = array();
    $headers[] = "Accept: application/json";
  //  $headers[] = "Accept-Language: en_US";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        return false;
    }
    curl_close($ch);


    $json = json_decode($result);
    $access_token = $json->access_token;

    // 获取订单
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paypal.com/v2/checkout/orders/" . $paypalOrderId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $headers = array();
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Bearer " . $access_token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        return false;
    }
    curl_close($ch);
    return json_decode($result, true);
}

function outputMsg($msg)
{
   echo date('Y-m-d H:i:s') . ' [INFO] ' . $msg .  PHP_EOL;
}

$current_path = __FILE__;
// 获取脚本所在目录的路径
$script_directory = dirname($current_path);
outputMsg('---Cron START!---' . $script_directory);
// 加载 WordPress 环境
require($script_directory . '/wp-load.php');

// 检查 WooCommerce 是否已激活
if ( ! class_exists( 'WooCommerce' ) ) {
    exit( 'WooCommerce is not installed or activated.' );
}
// 获取当前日期和一个月前的日期
$current_date = current_time('Y-m-d');
$one_month_ago = date('Y-m-d', strtotime('-1 month', strtotime($current_date)));
// 获取最近一个月的 on-hold 状态的订单列表
$on_hold_orders = wc_get_orders( array(
    'status' => 'on-hold',
    'limit'  => -1, // 获取所有符合条件的订单
    'date_query' => array(
        'after' => $one_month_ago,
        'before' => $current_date,
        'inclusive' => true,
    ),
) );
// 检查是否获取到订单
if ( ! empty( $on_hold_orders ) ) {
    foreach ( $on_hold_orders as $order ) {
        // 获取订单ID、总金额和状态
        $order_id = $order->get_id();
        //获取paypal order id
        $paypal_order_id = $order->get_meta('_ppcp_paypal_order_id');
        if (empty($paypal_order_id)) {
            outputMsg('Not Exist Paypal Order Id for ' . $order_id . '!' );
            exit;
        }
        $order_total = $order->get_total();
        $order_status = $order->get_status();
        $arrPayPalRet = get_paypal_orders($paypal_order_id);

        $orderPayPalStatus = '';
        if (!empty( $arrPayPalRet) && isset($arrPayPalRet['purchase_units'][0]['payments']['captures'])) {
            $transactionId = $order->get_transaction_id() ?? '';
            foreach($arrPayPalRet['purchase_units'][0]['payments']['captures'] as $capture) {
                if ($capture['id'] == $transactionId) {
                    $orderPayPalStatus = $capture['status'] ?? '';
                }
            }
        }

        if ($orderPayPalStatus == 'COMPLETED') {
            $updateRecTime = $arrPayPalRet['update_time'] ?? '';
            if (!empty($updateRecTime)) {
                $payment_time = date('Y-m-d H:i:s', strtotime($updateRecTime));
                $order->update_meta_data( '_payment_time', $payment_time );
            }
            $order->add_order_note('crontab running and updated order status!');
            $order->update_status("processing");

             // 输出订单信息
            outputMsg('Order ID: ' . $order_id . ', Paypal order id: ' . $paypal_order_id . ' Success updated');
        } else {
            outputMsg('Order ID: ' . $order_id . ', Paypal order id: ' . $paypal_order_id . ' Failed:' . var_export($arrPayPalRet, true));
        }
    }
} else {
    outputMsg ('No on-hold orders found.');
}
outputMsg('---Cron END!---');

