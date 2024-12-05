<?php
if (!class_exists('Order365d4uManager')) {
    class Order365d4uManager
    {
        public function __construct() {
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


        // 修改订单列表中的订单状态显示,对应 wc_order_statuses
        function custom_wc_order_statuses($order_statuses)
        {
            if (isset($order_statuses['wc-processing'])) {
                $order_statuses['wc-processing'] = _x('Paid', 'Order status', 'woocommerce');
            }
            return $order_statuses;
        }


       // 修改订单详情页面中的订单状态显示   woocommerce_admin_order_preview_get_order_details
        function custom_order_preview_status($order)
        {
            if ($order['status'] === 'processing') {
                $order['status'] = 'paid';
            }
            return $order;
        }


        // 修改订单状态显示在订单详情页面中的文本 woocommerce_admin_order_data_after_order_details
        function custom_order_status_display($order)
        {
            if ($order->get_status() === 'processing') {
                echo '<script>
            jQuery(document).ready(function($) {
                $("mark.status-processing").text("Paid");
            });
        </script>';
            }
        }

        // 添加支付时间列到订单列表 woocommerce_shop_order_list_table_columns
        function add_payment_date_column($columns)
        {
            if (!is_array($columns)) {
                return [];
            }
            $new_columns = [];
            foreach ($columns as $name => $col) {
                $new_columns[$name] = $col;
                if ($name === 'order_status') {
                    $new_columns['payment_date'] = esc_html__('PayTime', 'woocommerce');
                    $new_columns['payment_method_title'] = esc_html__('Payment Method', 'woocommerce');
                    $new_columns['transaction_id'] = esc_html__('Transaction ID', 'woocommerce');
                }
            }

            return $new_columns;
        }


       // 显示支付时间数据 woocommerce_shop_order_list_table_custom_column
        function show_payment_date_column_content($column, $order)
        {
            if ($column == 'payment_date') {
                echo $this->formatCustomPaymentDate($order->get_id());
            } elseif($column == 'payment_method_title') {
                echo $order->get_payment_method_title() ?? '-';
            } elseif($column == 'transaction_id') {
                echo $order->get_transaction_id() ?? '-';
            }
        }

         //添加pay-time在详情页 woocommerce_admin_order_data_after_order_details
        function custom_365d4u_add_pay_time($order)
        {
            if (empty($order)) {
                return;
            }
            $sDate = $this->formatCustomPaymentDate($order->get_id());
            echo <<<HTML
<p class="form-field —pay_time form-field-wide" style="margin:20px 0 0 0;">
    <label>Pay Time: {$sDate}</label>
</p>
HTML;

            //获取paypal order id
            $paypal_order_id = $order->get_meta('_ppcp_paypal_order_id') ?? '';
            if (!empty($paypal_order_id)) {
                echo <<<HTML
<p class="form-field —paypal_order_id form-field-wide" style="margin:5px 0 0 0;">
    <label>Paypal Order Id For API: {$paypal_order_id}</label>
</p>
HTML;
            }
            $transactionId = $order->get_transaction_id() ?? '';
            if (!empty($transactionId)) {
                echo <<<HTML
<p class="form-field —paypal_order_id form-field-wide" style="margin:5px 0 0 0;">
    <label>Transaction ID/Payment ID: {$transactionId}</label>
</p>
HTML;
            }

        }

       //payment_date排序 manage_woocommerce_page_wc-orders_sortable_columns
        function make_payment_date_column_sortable($columns)
        {
            $columns['payment_date'] = 'payment_date';
            return $columns;
        }

        // payment_date排序列表中显示   woocommerce_order_list_table_prepare_items_query_args
        function custom_default_orderby_paid_date($query_vars)
        {
            $pageParam = $_GET['page'] ?? '';
            // 检查是否在后台订单列表页面
            if (is_admin() && 'wc-orders' === $pageParam && !isset($_GET['action'])) {
                if (!isset($_GET['orderby']) || $_GET['orderby'] == 'payment_date') {
                    $query_vars['meta_key'] = 'payment_date'; // 订单支付时间的元数据键名
                    $query_vars['orderby'] = 'meta_value';
                }
            }
            return $query_vars;
        }

        //修改订单sql  --woocommerce_orders_table_query_sql
        function custom_default_order_query_sql($sql, $obj, $args)
        {
            $pageParam = $_GET['page'] ?? '';
            $action = $_GET['action'] ?? '-1';
            if (is_admin() && 'wc-orders' === $pageParam && (empty($action) ||  $action === '-1')) {
                $filterMethod = $_GET['method-filter'] ?? '';
                if (!empty($filterMethod) && !str_contains($sql, 'payment_method')) {
                    $sourceWhere = 'WHERE 1=1 AND';
                    switch ($filterMethod){
                        case "ocean_all":
                            $sql = str_replace($sourceWhere, 'WHERE (payment_method like "ocean%") AND', $sql);
                            break;
                        case "paypal":
                            $sql = str_replace($sourceWhere, 'WHERE (payment_method like "ppcp%") AND', $sql);
                            break;
                        case "credit_card":
                            $sql = str_replace($sourceWhere, 'WHERE (payment_method like "oceancredit%") AND', $sql);
                            break;
                        case "apple_pay":
                            $sql = str_replace($sourceWhere, 'WHERE (payment_method like "oceanapple%") AND', $sql);
                            break;
                    }
                }
                $filterSource = $_GET['source-filter'] ?? '';
                if (!empty($filterSource) && !str_contains($sql, 'as src_f')) {
                    $sourceJoin = '/FROM\s+wp_wc_orders\s+WHERE/ius';
                    switch ($filterSource){
                        case "mobile_app":
                            $targetTable = "(select * from wp_wc_orders_meta where meta_key='_wc_order_attribution_source_type' and meta_value='mobile_app')";
                            break;
                        case "admin":
                            $targetTable = "(select * from wp_wc_orders_meta where meta_key='_wc_order_attribution_source_type' and meta_value='admin')";
                            break;
                        case "other":
                        default:
                            $targetTable = "(select * from wp_wc_orders_meta where meta_key='_wc_order_attribution_source_type' and meta_value not in ('mobile_app','admin'))";
                            break;
                    }

                    $sql = preg_replace($sourceJoin, 'FROM wp_wc_orders JOIN ' . $targetTable . ' as src_f on wp_wc_orders.id=src_f.order_id  WHERE', $sql);
                }

                if (!isset($_GET['orderby']) || $_GET['orderby'] == 'payment_date') {
                    if (strstr($sql, "meta0.meta_key = 'payment_date'")
                        && strstr($sql, " INNER JOIN wp_wc_orders_meta AS meta0 ON ( wp_wc_orders.id = meta0.order_id )")) {
                        $sql = str_replace(' INNER JOIN wp_wc_orders_meta AS meta0 ON ( wp_wc_orders.id = meta0.order_id )', " LEFT JOIN wp_postmeta AS meta0 ON ( wp_wc_orders.id = meta0.post_id  AND meta0.meta_key = 'payment_date')", $sql);
                        $sql = str_replace('GROUP BY wp_wc_orders.id', "", $sql);
                        if (isset($_GET['order']) && $_GET['order'] == 'asc') {
                            $sql = str_replace("ORDER BY CAST(meta0.meta_value AS CHAR) DESC", "ORDER BY CAST(ifnull(meta0.meta_value,'99999') AS CHAR) ASC, wp_wc_orders.id DESC", $sql);
                            $sql = str_replace("ORDER BY CAST(meta0.meta_value AS CHAR) ASC", "ORDER BY CAST(ifnull(meta0.meta_value,'99999') AS CHAR) ASC, wp_wc_orders.id DESC", $sql);
                        } else {
                            $sql = str_replace("ORDER BY CAST(meta0.meta_value AS CHAR) DESC", "ORDER BY CAST(ifnull(meta0.meta_value,'') AS CHAR) DESC, wp_wc_orders.id DESC", $sql);
                        }

                        $sql = str_replace('CAST(meta0.meta_value AS CHAR)', "CAST(ifnull(meta0.meta_value,'') AS CHAR)", $sql);
                        $sql = str_replace(" AND ((meta0.meta_key = 'payment_date'))", '', $sql);
                    }
                }

                if ($args && !empty($args['s']) && !str_contains($sql, '`wp_wc_orders`.transaction_id')) {
                   $filter = $args['search_filter'] ?? 'all';
                   if ($filter=='all') {
                       $sql =  preg_replace('/(`wp_wc_orders`.billing_email\s*LIKE.*?OR)/ius','$1 `wp_wc_orders`.transaction_id="'. $args['s'] .'" OR ', $sql);
                   } elseif ($filter=='order_id') {
                       $sql =  preg_replace('/(\\(\s*`wp_wc_orders`.id\s*=\s*\d+\s*\\))/ius', '$1 OR (`wp_wc_orders`.transaction_id="'. $args['s'] .'")', $sql);
                   }
                }
            }
            $sql = str_replace(' GROUP BY wp_wc_orders.id ORDER', ' ORDER', $sql);
            return $sql;
        }

       //默认count sql ---woocommerce_orders_table_query_count_sql
        function custom_default_order_query_count_sql($count_sql, $obj, $args, $fields, $join, $where, $groupby)
        {
            return $this->custom_default_order_query_sql($count_sql, $obj, $args);
        }

       // 更改订单创建时间格式 woocommerce_admin_order_date_format
        function custom_woocommerce_order_date_format($date_format)
        {
            // 定义你想要的日期格式，例如 'Y-m-d H:i:s' 对应 '2023-01-01 12:34:56'
            return 'Y-m-d H:i:s';
        }

        //1天内的时间格式 -- woocommerce_admin_order_date_within_1_day
        function custom_woocommerce_format_1_day($date, $orderCreateTime)
        {
            if (!$orderCreateTime) {
                return $date;
            }
            return $orderCreateTime->date_i18n('Y-m-d H:i:s');
        }


        //需要邮件验证 woocommerce_order_email_verification_required
        function custom_365d4_need_verify_email($email_verification_required, $order, $context)
        {
            return false;
        }

        function custom_365d4_need_verify_known_shoppers($bNeedVerified)
        {
            return false; //no need verify
        }

        //获取支付失败需要跳转的fail url  ocean_pay_get_fail_url
        function custom_365d4_getfail_url($checkout_url, $order_id, $order)
        {
            if (!empty($order_id) && empty($order)) {
                $order = wc_get_order($order_id);
            }
            if (!$order) {
                return $checkout_url;
            }
            $sourceType = $order->get_meta('_wc_order_attribution_source_type') ?? '';
            if ($sourceType != 'typein') {
                $checkout_url = $order->get_checkout_payment_url();
            }
            return $checkout_url;
        }

        //添加notice --woocommerce_add_error
        function custom_365d4u_add_error_notice($message)
        {
            $cancelMsg = __('50008:Transaction canceled by customer', 'woocommerce');
            if ($message && ($message === $cancelMsg || str_contains($message, 'Transaction canceled by customer'))) {
                return '';
            }
            return $message;
        }

        //显示comment  --the_comments
        function cus_365_reset_date($_comments, $query)
        {
            if (empty($_comments) || !is_array($_comments)) {
                return $_comments;
            }
            try {
                foreach ($_comments as $k => $_comment) {
                    if ($_comment->comment_date && is_string($_comment->comment_date)) {
                        if ($_comment->comment_date < '2024-05-29 13:30:00' && $_comment->comment_date > '2024-05-20 00:00:00') {
                            //2024-05-20 00:00:00 to 2024-05-29 13:30:00 had use UTC+0 area
                            $_comment->comment_date = date('Y-m-d H:i:s', strtotime($_comment->comment_date) + 8 * 3600);
                        }
                    }
                }
            } catch (\Exception $ex) {
                error_log('Error:' . $ex->getMessage() . PHP_EOL . $ex->getMessage());
            }
            return $_comments;
        }

        function custom_bulk_actions($action): string
        {
            return '';
        }
        function custom_add_order_filter()
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
                if ($selectedOption == $key) {
                    $sOption .= '<option value="' . $key .'" selected>'. $val .'</option>';
                } else {
                    $sOption .= '<option value="' . $key .'">'. $val .'</option>';
                }

            }

            echo <<<HTML
  <div style="
    display: inline-block;
"><label style="float: left;margin: 5px 5px 0 0;">Payment:</label>
    <select name="method-filter" id="order-payment-method-filter">
		 {$sOption}
	 </select>
</div> 
HTML;

             $arrSourceAction =[
                 '' => 'All',
                 'mobile_app' => 'Mobile App',
                 'admin' => 'Admin',
                 'other' => 'Other',
             ];
            $sourceOption = $_GET['source-filter'] ?? '';
            $pOption = '';
            foreach ($arrSourceAction as $key=>$val) {
                if ($sourceOption == $key) {
                    $pOption .= '<option value="' . $key .'" selected>'. $val .'</option>';
                } else {
                    $pOption .= '<option value="' . $key .'">'. $val .'</option>';
                }
            }
            echo <<<HTML
  <div style="
    display: inline-block;
"><label style="float: left;margin: 5px 5px 0 0;">Source:</label>
    <select name="source-filter" id="order-source-filter">
		 {$pOption}
	 </select>
</div> 
HTML;

       }

        function custom_hide_refund_item($bShow, $order)
        {
          //  var_dump( wp_get_current_user());exit;
            $currentUser = wp_get_current_user();
            if ($currentUser && $currentUser->ID !== 1) {
                return false;
            }
            return $bShow;
        }

        function custom_display_nav_bottom($order, $which)
        {
           if ($which !='bottom') {
                return;
           }
           echo <<<STYLE
<style>
.post-type-shop_order .tablenav .one-page .displaying-num, .woocommerce_page_wc-orders .tablenav .one-page .displaying-num
{
   display:block;
}
#orders-search-input-search-input{
   min-width:250px;
}
</style>
<script>
  jQuery(document).ready(function() {
        var next =  jQuery('select.wc-customer-search').next();
        if (next.length > 0) {
             jQuery('select.wc-customer-search').next().hide();
        }       
  })
</script>
STYLE;

         }
    }

    $orderManager = new Order365d4uManager();
    // 修改订单列表中的订单状态显示
    add_filter('wc_order_statuses',  array($orderManager, 'custom_wc_order_statuses'));

    // 修改订单详情页面中的订单状态显示
    add_filter('woocommerce_admin_order_preview_get_order_details',  array($orderManager, 'custom_order_preview_status'), 10, 1);

   // 修改订单状态显示在订单详情页面中的文本
    add_filter('woocommerce_admin_order_data_after_order_details',  array($orderManager, 'custom_order_status_display'));

   // 添加支付时间列到订单列表
    add_filter('woocommerce_shop_order_list_table_columns',  array($orderManager, 'add_payment_date_column'), 20);

   // 显示支付时间数据
    add_action('woocommerce_shop_order_list_table_custom_column',  array($orderManager, 'show_payment_date_column_content'), 10, 2);

   //添加pay-time在详情页
    add_action('woocommerce_admin_order_data_after_order_details',  array($orderManager, 'custom_365d4u_add_pay_time'), 9, 1);


   //payment_date 排序
    add_filter('manage_woocommerce_page_wc-orders_sortable_columns',  array($orderManager, 'make_payment_date_column_sortable'));

    add_filter('woocommerce_order_list_table_prepare_items_query_args',  array($orderManager, 'custom_default_orderby_paid_date'), 10, 1);


   //修改订单sql  --woocommerce_orders_table_query_sql
    add_filter('woocommerce_orders_table_query_sql',  array($orderManager, 'custom_default_order_query_sql'), 10, 3);

    //默认count sql ---woocommerce_orders_table_query_count_sql
    add_filter('woocommerce_orders_table_query_count_sql',  array($orderManager, 'custom_default_order_query_count_sql'), 10, 7);

   // 更改订单创建时间格式 woocommerce_admin_order_date_format
    add_filter('woocommerce_admin_order_date_format',  array($orderManager, 'custom_woocommerce_order_date_format'));

   //1天内的时间格式 -- woocommerce_admin_order_date_within_1_day
    add_filter('woocommerce_admin_order_date_within_1_day',  array($orderManager, 'custom_woocommerce_format_1_day'), 10, 2);

    //需要邮件验证 woocommerce_order_email_verification_required
    add_filter('woocommerce_order_email_verification_required',  array($orderManager, 'custom_365d4_need_verify_email'), 10, 3);
    //查看订单详情需要权限
    add_filter('woocommerce_order_received_verify_known_shoppers',  array($orderManager, 'custom_365d4_need_verify_known_shoppers'), 10, 1);

  //获取支付失败需要跳转的fail url  ocean_pay_get_fail_url
    add_filter('ocean_pay_get_fail_url',  array($orderManager, 'custom_365d4_getfail_url'), 10, 3);

  //添加notice --woocommerce_add_error
    add_filter('woocommerce_add_error',  array($orderManager, 'custom_365d4u_add_error_notice'), 10, 2);

   //显示comment  --the_comments
    add_filter( 'the_comments',  array($orderManager, 'cus_365_reset_date'), 10, 2 );

    add_filter('bulk_actions-woocommerce_page_wc-orders', array($orderManager, 'custom_bulk_actions'), 10, 1);

    add_action('woocommerce_order_list_table_restrict_manage_orders', array($orderManager, 'custom_add_order_filter'));

    add_action('woocommerce_order_list_table_extra_tablenav', array($orderManager, 'custom_display_nav_bottom'), 10, 2);

    add_action('woocommerce_admin_order_should_render_refunds', array($orderManager, 'custom_hide_refund_item'), 10, 2);

}