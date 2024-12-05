<?php
// 添加设置
add_filter('woocommerce_get_settings_checkout', 'custom_pay_control_settings', 10, 1);

function custom_pay_control_settings($settings) {
    $settings[] = array(
        'title' => __('Custom Ocean Creditcard payment Control', 'woocommerce'),
        'type' => 'title',
        'id' => 'custom_pay_control_settings',
    );

    $settings[] = array(
        'title' => __('Enable Ocean Creditcard Payment Control', 'woocommerce'),
        'desc' => __('Enable to control Ocean Credit Payment based on order total.', 'woocommerce'),
        'id' => 'custom_pay_control_enabled',
        'type' => 'checkbox',
        'default' => 'no',
    );

    $settings[] = array(
        'title' => __('Order Total Limit', 'woocommerce'),
        'desc' => __('Orders exceeding this total will have Ocean payments disabled.', 'woocommerce'),
        'id' => 'custom_pay_control_order_total',
        'type' => 'text',
        'default' => '500',
    );
    $feiShuSetting = new FeiShuSetting();
    $settings = $feiShuSetting->addSetting($settings);

    $settings[] = array(
        'type' => 'sectionend',
        'id' => 'custom_pay_control_settings',
    );
    return $settings;
}

// 验证设置
add_action('woocommerce_update_options_checkout', 'custom_pay_control_update_settings');

function custom_pay_control_update_settings() {
    $enabled = isset($_POST['custom_pay_control_enabled']) ? 'yes' : 'no';
    $order_total = isset($_POST['custom_pay_control_order_total']) ? wc_format_decimal($_POST['custom_pay_control_order_total']) : '500';
    update_option('custom_pay_control_enabled', $enabled);
    update_option('custom_pay_control_order_total', $order_total);

    $feiShuSetting = new FeiShuSetting();
    $feiShuSetting->updateFeiShuOptions();
}

// add custom field to order
add_action('woocommerce_admin_order_data_after_order_details', 'add_no_limit_ocean_fields');
function add_no_limit_ocean_fields($order) {
//    woocommerce_wp_checkbox(array(
//        'id' => '_need_ship_address',
//        'label' => __('Shipping address required', 'woocommerce'),
//        'wrapper_class' => 'form-field-wide',
//        'value' =>  $order->get_meta('_need_ship_address'),
//    ));
    woocommerce_wp_checkbox(array(
        'id' => '_no_ocean_limit',
        'label' => __('No Amount Limit For Ocean Credit Card Payment', 'woocommerce'),
        'wrapper_class' => 'form-field-wide',
        'value' => $order->get_meta('_no_ocean_limit'),
    ));
    $hideField = <<<STYLE
        form[name='order'] #order_custom{
          display: none;
        }
        form[name='order']  #woocommerce-order-downloads{
          display: none;
        }
        form[name='order'] .add-coupon{
           display: none;
        }           
STYLE;

    if ( $order->needs_payment() ) {
        $url =  esc_url( $order->get_checkout_payment_url());
        $payPage = esc_html__( 'Customer payment page &rarr;', 'woocommerce' );
        echo  <<<HTML
<p class="form-field —copy_link_field form-field-wide" style="margin:10px 0;">
    <a href="{$url}">{$payPage}</a>
    <textarea id="paylink" readonly>{$url}</textarea>
</p>
 <style>
       .wc-order-status a {
            display: none;
        }    
        {$hideField}   
</style> 
HTML;
    } else {
        echo <<<HTML
  <style>
      {$hideField}   
 </style>
HTML;
    }
}

// 保存自定义字段
add_action('woocommerce_process_shop_order_meta', 'save_no_limit_ocean_fields', 10, 2);
function save_no_limit_ocean_fields($post_id, $post) {
    $order = wc_get_order( $post_id );
    if (empty($order)) {
        return false;
    }
    $order->update_meta_data( '_no_ocean_limit', sanitize_text_field($_POST['_no_ocean_limit'] ?? '' ) );
//    $order->update_meta_data( '_need_ship_address', sanitize_text_field( $_POST['_need_ship_address']));
    $order->save();
    return $post_id;
}


//hide ocean when checkout for large order
add_filter( 'woocommerce_available_payment_gateways', 'hide_ocean_for_large_order', 10, 1);
function hide_ocean_for_large_order($available_gateways ) {
    $currentUrl = home_url($_SERVER['REQUEST_URI']);
    if (!$currentUrl) {
        return $available_gateways;
    }
    $currentUrl = strtok($currentUrl, '?');
    $arrExplode = explode('checkout/order-pay/',$currentUrl);
    $enabled = get_option('custom_pay_control_enabled');
    $order_total_limit = get_option('custom_pay_control_order_total');
    if (count($arrExplode) <= 1) {
        if (strstr($currentUrl, '/checkout/')) {
            $order_total = WC()->cart->total;
            if ($enabled === 'yes' && $order_total >= $order_total_limit) {
                unset($available_gateways['oceancreditcardonepage'] );
                unset($available_gateways['oceancreditcard'] );
                unset($available_gateways['oceanapplepay']);
            }
        }
    } else {
        $orderId = explode('/', $arrExplode[1])[0];
        $order = wc_get_order($orderId);
        if (empty($order)) {
            return $available_gateways;
        }
        $no_ocean_limit = $order->get_meta( '_no_ocean_limit') ?? '';
        // 获取订单总金额
        $order_total = $order->get_total();
        if ($enabled === 'yes' && $order_total >= $order_total_limit && $no_ocean_limit != 'yes') {
            unset($available_gateways['oceancreditcardonepage'] );
            unset($available_gateways['oceancreditcard'] );
            unset($available_gateways['oceanapplepay']);
        }
    }
    return $available_gateways;
}