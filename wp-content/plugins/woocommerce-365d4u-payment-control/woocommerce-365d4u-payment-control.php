<?php

/*
Plugin Name: 365d4u Payment Control
Description: This plugin allows you to control Ocean payments based on order total.
*/
$plugin_dir = plugin_dir_path(__FILE__);
$plugin_dir = rtrim($plugin_dir, '\\/') . '/';
require  $plugin_dir . 'requirefiles.php';

function add_custom_beforepayform($order, $order_button_text, $available_gateways)
{
    $currentUrl = home_url($_SERVER['REQUEST_URI']);
    $currentUrl = strtok($currentUrl, '?');
    $arrExplode = explode('checkout/order-pay/',$currentUrl);
    if (count($arrExplode) <=1 || empty($order)) {
        return;
    }
    echo '<div class="cus-pay-page-ship woocommerce-order">';
    echo '<h4>' . __('Order Number : ', 'woocommerce') . $order->get_id() . '</h4>';
    echo '<div>';
}

add_action('before_woocommerce_pay_form', 'add_custom_beforepayform', 10, 3);

function cusDisplayFields($arrFieldKeys, $checkout, $arrAllFields, $arrAllValue){
    $allEmpty = true;
    foreach($arrAllFields as $field => $value) {
        if (!empty($value)) {
            $allEmpty = false;
        }
    }
    foreach ($arrFieldKeys as $key => $arrVal) {
        if (is_array($arrVal)) {
            echo "<div class='woocommerce-splitBlock'>";
            foreach ($arrVal as $val) {
                $field =  $arrAllFields[$val];
                $curVal = ($allEmpty ?  $checkout->get_value($val) : $arrAllValue[$val]);
                woocommerce_form_field($val, $field, $curVal);
            }
            echo "</div>";
        } else {
            $field =  $arrAllFields[$key];
            $curVal = ($allEmpty ?  $checkout->get_value($key) : $arrAllValue[$key]);
            woocommerce_form_field($key, $field, $curVal);
        }
    }
    return $allEmpty;
}
// 添加配送地址和账单地址表单到订单支付页面
function add_custom_address_forms_on_order_pay_page() {
    if (is_wc_endpoint_url('order-pay')) {
        global $tempAddrValidField; // 引入全局变量;
        $order_id = get_query_var('order-pay');
        $order = wc_get_order($order_id);
        if ($order) {
            $transparentStyle = <<<Style2
    .woocommerce form .cus-pay-page-ship .form-row .required{
        color:transparent;
     }
Style2;
         //   $need_ship_address = $order->get_meta( '_need_ship_address') ?? '';
            $need_ship_address = 'yes';
            if ($need_ship_address == 'yes') {
                $transparentStyle = '';
            }
            // 获取 WooCommerce 结账对象实例
            $checkout = WC()->checkout();
            echo '<div class="cus-pay-page-ship woocommerce-shipping-fields">';
            echo '<input name="update_ship_pay_form" type="hidden" value="' . $order_id .'">';
            echo '<h3>' . __('Shipping Address', 'woocommerce') . '</h3>';
            $arrBillingFields = $checkout->get_checkout_fields('billing');
            $arrShippingFields = cusGetShippingFields($checkout);
            $arrShippingValue = [
                'shipping_first_name' =>   $order->get_shipping_first_name() ?? '',
                'shipping_last_name'=> $order->get_shipping_last_name() ?? '',
                'shipping_address_1'=> $order->get_shipping_address_1() ?? '',
                'shipping_address_2'=>  $order->get_shipping_address_2() ?? '',
                'shipping_country' =>  $order->get_shipping_country() ?? '',
                'shipping_city' => $order->get_shipping_city() ?? '',
                'shipping_state' => $order->get_shipping_state() ?? '',
                'shipping_postcode' => $order->get_shipping_postcode() ?? '',
                'shipping_phone' => $order->get_shipping_phone() ?? '',
            ];
            $arrBillingValue = [
                'billing_first_name' =>   $order->get_billing_first_name() ?? '',
                'billing_last_name'=> $order->get_billing_last_name() ?? '',
                'billing_address_1'=> $order->get_billing_address_1() ?? '',
                'billing_address_2'=>  $order->get_billing_address_2() ?? '',
                'billing_country' =>  $order->get_billing_country() ?? '',
                'billing_city' => $order->get_billing_city() ?? '',
                'billing_state' => $order->get_billing_state() ?? '',
                'billing_postcode' => $order->get_billing_postcode() ?? '',
                'billing_phone' => $order->get_billing_phone() ?? '',
            ];
            $allEqual = true;
            foreach($arrShippingValue as $field => $value) {
                $billField = str_replace('shipping_', 'billing_', $field);
                $billVal = $arrBillingValue[$billField] ?? '';
                if (empty($value)) {
                    $value = '';
                }
                if ($field == "shipping_country" && empty($billVal)) {
                    continue;
                }
                if ($billVal != $value) {
                    $allEqual = false;
                }
            }
            $email =  $order->get_billing_email();
            woocommerce_form_field('billing_email', $arrBillingFields['billing_email'], $email);
            $arrShippingFieldKeys = [
                'shipping_first_name' => 'shipping_first_name',
                'shipping_last_name'=> 'shipping_last_name',
                'shipping_address_1'=> 'shipping_address_1',
                'shipping_address_2'=> 'shipping_address_2',
                'shipping_country' => 'shipping_country',
                'shipping_city_state' => ['shipping_state', 'shipping_city'],
                'shipping_postcode_phone' => ['shipping_postcode', 'shipping_phone']
            ];
            cusDisplayFields($arrShippingFieldKeys, $checkout,$arrShippingFields, $arrShippingValue);
            echo '</div>';
            echo '<div class="check_same">';
            echo "<input id='same_with_shipping' type='checkbox' name='same_with_shipping'" . ($allEqual ? ' checked="checked"' : '') . " onclick='onCheckChange();'/>";
            echo  '<label for="same_with_shipping">Use Shipping Address as Billing Address</label>';
            echo '</div>';

            echo '<div class="cus-pay-page-ship billing_data_div"' .  ($allEqual ? ' style="display:none"' : '') . '>';
            echo '<div class="cus_billing_field woocommerce-shipping-fields">'; 
            echo '<h3>' . __('Billing Address', 'woocommerce') . '</h3>';
            $arrBillingFieldKeys = [
                'billing_first_name' => 'billing_first_name',
                'billing_last_name'=> 'billing_last_name',
                'billing_address_1'=> 'billing_address_1',
                'billing_address_2'=> 'billing_address_2',
                'billing_country' => 'billing_country',
                'billing_city_state' => ['billing_state', 'billing_city'],
                'billing_postcode_phone' => ['billing_postcode', 'billing_phone']
            ];
            cusDisplayFields($arrBillingFieldKeys, $checkout, $arrBillingFields, $arrBillingValue);
            echo '</div>';
            echo '</div>';

            $validScript = '';
            $fieldName = '';
            if (!empty($tempAddrValidField)) {
                foreach ($tempAddrValidField as $field => $msg) {
                    $validScript .= 'validField("' . $field . '", "' .$msg . '");' .PHP_EOL;
                    if (empty($fieldName)) {
                       $fieldName = $field . '_field';
                    }
                }
            }
            if (!empty($validScript)) {
                $validScript = <<<VALIDHTML
      $(document).ready(function(){
           $validScript
           var windowWidth = $(window).width();
           var offsetTop = $('#{$fieldName}').offset().top;
           var noticeHeight = $('.woocommerce-notices-wrapper').height();
           
            if (windowWidth < 768) {
               if (offsetTop > noticeHeight) {
                offsetTop = offsetTop - noticeHeight;
              }
           }
           $(document).scrollTop(offsetTop);
      });
VALIDHTML;

            }
            
            $styleHtml = <<<HTMLSTYLE
<style>
     table.innerTable span.cusfeeName,span.woocommerce-Price-amount.amount.cusFeePriceVal,.cusFeePriceVal bdi,span.cusFeeQty{
        font-weight: 200;
     }       
     {$transparentStyle}
     .redborderStyle {
        border:red 1px solid;
     }
     .select2-results__options .select2-results__option{
         color:#1d1d1d9d;
     } 
     .woocommerce-page .cus-pay-page-ship .select2-container .select2-selection { 
           height: 2.4em;
           padding: 0.2em 0.9em;
     } 
     .cus-pay-page-ship .select2-container--default .select2-selection--single .select2-selection__arrow b{
         margin-top:-10px;
     }
     .cus-pay-page-ship .woocommerce-splitBlock {
        display: flex;
        width: 100%;
     } 
     .cus-pay-page-ship .woocommerce-splitBlock p:first-child {
        width: 48%;
     }
     .cus-pay-page-ship .woocommerce-splitBlock p:last-child {
        width: 46%;
        margin-left: auto;
     }
     .cus-pay-page-ship .woocommerce-splitBlock .cusvalship p.discus{
         margin-left: 0;
         width: 100%;
     }
    .cus-pay-page-ship #billing_country, .cus-pay-page-ship #billing_state{
        height:38px;
     }
     .woocommerce-page .select2-container .select2-search__field, .woocommerce-page  .select2-container .select2-selection {
       height: 2.2em;
     }    
     @media screen and (max-width: 767px) {
	    .cus-pay-page-ship .woocommerce-splitBlock {
            display: block; 
         } 
       .cus-pay-page-ship .woocommerce-splitBlock p:first-child {
           width: 100%;
        }
        .cus-pay-page-ship .woocommerce-splitBlock p:last-child {
           width: 100%; 
        }
	}
	.has-error input,.has-error span.select2-selection.select2-selection--single {
	    border: red 1px solid; 
	}
	.has-error #billing_state,.has-error #billing_country {
	    border: red 1px solid; 
	}	  
</style>
<script>
    var $ = jQuery.noConflict();
    function onCheckChange() {
        if($('#same_with_shipping').is(':checked')) {
            $('.billing_data_div').css('display', 'none');
        } else {
             $('.billing_data_div').css('display', 'block');
        }
    }
    function validField(fieldId, displaMsg)
    {
        var inputParent = $('#' + fieldId).parent();
        if (!inputParent.parent().hasClass('has-error')) {
            inputParent.append('<div class="wc-block-components-validation-error cusvalship" role="alert"><p class="discus">' + displaMsg + '</p></div>');
            inputParent.parent().addClass('has-error');
        }
        $('#' + fieldId).focus(function(){
             var thisParent = $(this).parent();
             if (thisParent.parent().hasClass('has-error')) {
                 thisParent.parent().removeClass('has-error');
                 thisParent.find('.cusvalship').remove();
             }
        });
    }
    {$validScript}
</script>
HTMLSTYLE;
            echo $styleHtml;
        }
    }
}

add_action('woocommerce_pay_order_before_payment', 'add_custom_address_forms_on_order_pay_page');

function checkField($arrFields, $bCheckEmail) {
    $arrDisplayName = [
        'email'=>  '“Email” required!',
        'first_name'=>  '“{0}First Name” required!',
        'last_name'=>  '“{0}Last Name” required!',
        'country'=>  '“{0}Country” required!',
        'address_1'=>  '“{0}Address” required!',
        'city'=>  '“{0}City” required',
        'state'=>  '“{0}State” required!',
        'phone'=>  '“{0}Phone” required!',
        'postcode'=>  '“{0}ZipCode” required!'
    ];
    $arrValidRet = [];
    foreach($arrFields as $key => $field) {
        if (!$field['required']) {
            continue;
        }
        if (!$bCheckEmail && $key == 'billing_email') {
            continue;
        }
        if ( isset($_POST[$key]) && empty($_POST[$key]) ) {
            $checkKey = str_replace(array("billing_", "shipping_"), array("", ""), $key);
            $displayMsg = $arrDisplayName[$checkKey] ?? ucfirst($key) . 'needed';
            $addName = (str_contains($key, 'shipping_') ? 'Shipping Address - ' : 'Billing Address - ');
            $displayMsg = str_replace('{0}', $addName, $displayMsg);
            wc_add_notice( __($displayMsg, 'woocommerce'), 'error' );
            $arrValidRet[$key] = __($displayMsg, 'woocommerce');
        }
    }
    return $arrValidRet;
}

function cusGetShippingFields($checkout)
{
    $arrShippingFields = $checkout->get_checkout_fields('shipping');
    $showPhone =  ('hidden' !== get_option( 'woocommerce_checkout_phone_field', 'required' ));
    if ( !isset($arrShippingFields['shipping_phone']) ) {
        $arrShippingFields['shipping_phone'] = array(
            'label'        => __( 'Phone', 'woocommerce' ),
            'required'     => true,
            'type'         => 'tel',
            'class'        => array( 'form-row-wide' ),
            'validate'     => array( 'phone' ),
            'autocomplete' => 'tel',
            'priority'     => 100,
        );
    }
    $arrShippingFields['shipping_phone']['required'] = true;
    return $arrShippingFields;
}

function check_and_save_order($order) {
    $order_id = $order->get_id();
    $checkout = WC()->checkout();
    global $tempAddrValidField;
    $tempAddrValidField = [];
    $arrArray = ['ppcp-gateway', 'ppcp-credit-card-gateway', 'ppcp-oxxo-gateway', 'ppcp-card-button-gateway', 'paypal'];
    $paymentMethod = $_POST['payment_method']?? '';
    $order->add_order_note('payment:' . $paymentMethod);
    $order->save();
    if (in_array($paymentMethod, $arrArray) || strstr($paymentMethod, 'ppcp_')) {
        //no need to update address for paypal
        return;
    }
    if (isset($_POST['update_ship_pay_form']) && $order_id == $_POST['update_ship_pay_form'] ) {
        $arrShippingFields =  cusGetShippingFields($checkout);
        $arrBillingFields = $checkout->get_checkout_fields('billing');
       // $need_ship_address = $order->get_meta( '_need_ship_address') ?? '';
        $need_ship_address = 'yes';
        $sameBilling = (!empty($_POST['same_with_shipping']) && $_POST['same_with_shipping'] == 'on');
        if ($need_ship_address == 'yes') {
            if (filter_var($_POST['billing_email'], FILTER_VALIDATE_EMAIL)) {
                $order->set_billing_email(sanitize_text_field($_POST['billing_email']));
            } else {
                $strErrEmailMsg =  __('Email - please enter a valid email!', 'woocommerce');
                $tempAddrValidField = ['billing_email' =>  $strErrEmailMsg];
                wc_add_notice( $strErrEmailMsg, 'error' );
            }
            $arrShipValid =  checkField($arrShippingFields, false);
            $tempAddrValidField = array_merge($tempAddrValidField, $arrShipValid);
            if (!$sameBilling) {
                $arrBillValid = checkField($arrBillingFields, false);
                $tempAddrValidField = array_merge($tempAddrValidField, $arrBillValid);
            }
        }
        try{
            $order->set_shipping_first_name(sanitize_text_field($_POST['shipping_first_name']));
            $order->set_shipping_last_name(sanitize_text_field($_POST['shipping_last_name']));
            $order->set_shipping_address_1(sanitize_text_field($_POST['shipping_address_1']));
            $order->set_shipping_address_2(sanitize_text_field($_POST['shipping_address_2']));
            $order->set_shipping_company('');
            $order->set_shipping_city(sanitize_text_field($_POST['shipping_city']));
            $order->set_shipping_postcode(sanitize_text_field($_POST['shipping_postcode']));
            $order->set_shipping_country(sanitize_text_field($_POST['shipping_country']));
            $order->set_shipping_state(sanitize_text_field($_POST['shipping_state']));
            $order->set_shipping_phone(sanitize_text_field($_POST['shipping_phone']));
            if ($sameBilling) {
                //same set billing name
                $order->set_billing_first_name(sanitize_text_field($_POST['shipping_first_name']));
                $order->set_billing_last_name(sanitize_text_field($_POST['shipping_last_name']));
                $order->set_billing_address_1(sanitize_text_field($_POST['shipping_address_1']));
                $order->set_billing_address_2(sanitize_text_field($_POST['shipping_address_2']));
                $order->set_billing_company('');
                $order->set_billing_city(sanitize_text_field($_POST['shipping_city']));
                $order->set_billing_postcode(sanitize_text_field($_POST['shipping_postcode']));
                $order->set_billing_country(sanitize_text_field($_POST['shipping_country']));
                $order->set_billing_state(sanitize_text_field($_POST['shipping_state']));
                $order->set_billing_phone(sanitize_text_field($_POST['shipping_phone']));
            } else {
                $order->set_billing_first_name(sanitize_text_field($_POST['billing_first_name']));
                $order->set_billing_last_name(sanitize_text_field($_POST['billing_last_name']));
                $order->set_billing_address_1(sanitize_text_field($_POST['billing_address_1']));
                $order->set_billing_address_2(sanitize_text_field($_POST['billing_address_2']));
                $order->set_billing_company('');
                $order->set_billing_city(sanitize_text_field($_POST['billing_city']));
                $order->set_billing_postcode(sanitize_text_field($_POST['billing_postcode']));
                $order->set_billing_country(sanitize_text_field($_POST['billing_country']));
                $order->set_billing_state(sanitize_text_field($_POST['billing_state']));
                $order->set_billing_phone(sanitize_text_field($_POST['billing_phone']));
            }
            $note_msg = ' customer had submitted the shipping address:' .  json_encode($_POST);
            // 添加订单备注
            $order->add_order_note($note_msg);
            // 保存更改
            $order->save();
        } catch (\Exception $ex) {
            wc_add_notice( __('Could not save order!--' . $ex->getMessage(), 'woocommerce'), 'error' );
        }
    }
}

add_action('woocommerce_before_pay_action', 'check_and_save_order', 10, 1);

function cus_hide_custom_detail($orderId) {
    $currentUrl = home_url($_SERVER['REQUEST_URI']);
    if (!str_contains($currentUrl, 'checkout/order-pay/')) {
        return;
    }

    echo <<<STYLE
     <style>
        .woocommerce ul.order_details{
          display:none;
        }
     </style>
STYLE;

}

add_action('woocommerce_receipt_oceancreditcard', 'cus_hide_custom_detail', 10, 1);


function cus_reset_fee_style($total_rows, $obj, $tax_display) {
    $currentUrl = home_url($_SERVER['REQUEST_URI']);
    if (!str_contains($currentUrl, 'checkout/order-pay/') && !str_contains($currentUrl, 'checkout/order-received')) {
        return $total_rows;
    }
    unset($total_rows['cart_subtotal']);
    foreach ($total_rows as $key => $item) {
        if (str_contains($key, 'fee')) {
            //unset($total_rows[$key]);
            $feeName = trim($item['label'], ':');
            if (str_contains($currentUrl, 'checkout/order-received')) {
                $total_rows[$key]['label'] = "<span class='cusfeeName'>{$feeName}</span>";
                $total_rows[$key]['value'] = str_replace('woocommerce-Price-amount amount', 'woocommerce-Price-amount amount cusFeePriceVal', $item['value']);
            } else {
                $total_rows[$key]['label'] = <<<HTML
<table class="innerTable" style="width: 100%;"><tbody><tr><td style="padding: 0;width:65%;"><span class="cusfeeName">{$feeName}</span></td><td><span  class="cusFeeQty">1</span></td></tr></tbody></table>
HTML;
                $total_rows[$key]['value'] = str_replace('woocommerce-Price-amount amount', 'woocommerce-Price-amount amount cusFeePriceVal', $item['value']);
            }


        }
    }
    return $total_rows;
}
add_filter('woocommerce_get_order_item_totals', 'cus_reset_fee_style', 10, 3);


function cus_escape_label_transfer($escText, $sourceText)
{
    $currentUrl = home_url($_SERVER['REQUEST_URI']);
    if (!str_contains($currentUrl, 'checkout/order-pay/') && !str_contains($currentUrl, 'checkout/order-received')) {
        return $escText;
    }
    if (!strstr($sourceText, 'cusfeeName')) {
        return $escText;
    }
    return $sourceText;
}

add_filter('esc_html', 'cus_escape_label_transfer', 10, 2);

function cus_set_fee_span_style($order)
{
    echo <<<HTML
   <style>
       span.cusfeeName,span.woocommerce-Price-amount.amount.cusFeePriceVal,span.woocommerce-Price-amount.amount.cusFeePriceVal bdi,span.cusFeeQty{
          font-weight: 200;
       }       
   </style>
HTML;

}

add_action('woocommerce_after_order_details', 'cus_set_fee_span_style', 10, 1);


function custom_woocommerce_locate_template( $template, $template_name, $args, $template_path, $default_path )
{
    if (!str_contains($template, 'woocommerce/templates/block-notices/error.php')) {
        return $template;
    }
    $currentUrl = home_url($_SERVER['REQUEST_URI']);
    if (!str_contains($currentUrl, 'checkout/order-pay/')) {
        return $template;
    }
    return str_replace('woocommerce/templates/block-notices/error.php', 'woocommerce-365d4u-payment-control/woocommerce/block-notices/error.php', $template);
}
add_filter( 'wc_get_template', 'custom_woocommerce_locate_template', 20, 5 );

// 修改结账页面的SEO标题和描述
function custom_checkout_seo() {
    if (is_checkout()) {
        echo '<meta name="description" content="Complete your purchase securely on our checkout page.">';
        echo '<meta name="robots" content="noindex, nofollow">';
    } else  if (is_page('pay')) {
        echo '<meta name="description" content="Secure payment page for completing your order.">';
        echo '<meta name="robots" content="noindex, nofollow">';
    }
}
add_action('wp_head', 'custom_checkout_seo');

function change_seo_title($title) {
    $currentUrl = home_url($_SERVER['REQUEST_URI']);
    if (str_contains($currentUrl, 'checkout/order-pay/') || is_page('pay')) {
        return 'Pay it now- custom365d';
    }
    if (is_checkout()) {
        return 'Checkout - custom365d';
    }
    return $title;
}
add_filter('pre_get_document_title', 'change_seo_title', 20);


function cus365_get_policy_text($text, $type)
{
    $currentUrl = home_url($_SERVER['REQUEST_URI']);
    if (!str_contains($currentUrl, 'checkout/order-pay/') || is_page('pay')) {
        return $text;
    }
     return 'By proceeding with your purchase you agree to our Terms and Conditions and <a href="https://www.custom365d.com/privacy-policy/" target="_blank">Privacy Policy</a>';
}
add_filter('woocommerce_get_privacy_policy_text', 'cus365_get_policy_text', 10, 2);











