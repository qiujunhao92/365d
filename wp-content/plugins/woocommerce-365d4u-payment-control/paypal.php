<?php

add_filter('woocommerce_payment_complete_order_status', 'custompaypal_set_status', 10, 3 );
function custompaypal_set_status($status, $order_id, $order)
{
    $arrArray = ['ppcp-gateway', 'ppcp-credit-card-gateway', 'ppcp-oxxo-gateway', 'ppcp-card-button-gateway', 'paypal'];
    $paymentMethod = $order->get_payment_method() ?? '';
    if ($status === 'completed') {
        if (in_array($paymentMethod, $arrArray) || strstr($paymentMethod, 'ppcp_')) {
            $status = 'processing';
            $order->add_order_note('Received paypal signal to update status.');
        }
    }
    return $status;
}

add_action('woocommerce_paypal_payments_woocommerce_order_created', 'cus_pay_pal_order', 10, 2);

/**
 * @param WC_Order $wc_order
 * @param \WooCommerce\PayPalCommerce\ApiClient\Entity\Order $paypal_order
 * @return void
 */
function cus_pay_pal_order($wc_order, $paypal_order){
     $payer = $paypal_order ? $paypal_order->payer(): null;
     if (empty($payer)) {
        return;
     }
     $payer =  $payer->to_array();
     if (isset($payer['name'])) {
         $email = $payer['email_address'] ?? '';
         $firstName = $payer['name']['given_name'] ?? '';
         $lastName = $payer['name']['surname'] ?? '';
         $phone = '';
         if (isset($payer['phone'])) {
             $phone = $payer['phone']['phone_number']['national_number'] ?? '';
         }
         $purchaseUnit = $paypal_order->purchase_units();
         $shipping =  $purchaseUnit ? $purchaseUnit[0]->shipping() : [];
         $shipping = $shipping ? $shipping->to_array(): [];

         $address  = $shipping['address'] ?? [];

         $country = $address['country'] ?? '';
         $state = $address['admin_area_1'] ?? '';
         $city = $address['admin_area_2'] ?? '';
         $area1 = $address['address_line_1'] ?? '';
         $area2 = $address['address_line_2'] ?? '';
         $zipcode = $address['postal_code'] ?? '';

         $wc_order->set_billing_email($email);
         $wc_order->set_shipping_first_name(sanitize_text_field($firstName));
         $wc_order->set_shipping_last_name(sanitize_text_field($lastName));
         $wc_order->set_shipping_address_1(sanitize_text_field($area1));
         $wc_order->set_shipping_address_2(sanitize_text_field($area2));
         $wc_order->set_shipping_company('');
         $wc_order->set_shipping_city(sanitize_text_field($city));
         $wc_order->set_shipping_postcode(sanitize_text_field($zipcode));
         $wc_order->set_shipping_country(sanitize_text_field($country));
         $wc_order->set_shipping_state(sanitize_text_field($state));
         $wc_order->set_shipping_phone(sanitize_text_field($phone));

         $wc_order->set_billing_first_name(sanitize_text_field($firstName));
         $wc_order->set_billing_last_name(sanitize_text_field($lastName));
         $wc_order->set_billing_address_1(sanitize_text_field($area1));
         $wc_order->set_billing_address_2(sanitize_text_field($area2));
         $wc_order->set_billing_company('');
         $wc_order->set_billing_city(sanitize_text_field($city));
         $wc_order->set_billing_postcode(sanitize_text_field($zipcode));
         $wc_order->set_billing_country(sanitize_text_field($country));
         $wc_order->set_billing_state(sanitize_text_field($state));
         $wc_order->set_billing_phone(sanitize_text_field($phone));
        // $wc_order->add_meta_data('_ppcp_order_purchaseunit', json_encode($purchaseUnit[0]), true);
         if (!$wc_order->get_meta('_ppcp_order_payer')) {
             $wc_order->add_meta_data('_ppcp_order_payer', json_encode($payer), true);
         }
         if (!$wc_order->get_meta('_ppcp_shipping')) {
             $wc_order->add_meta_data('_ppcp_shipping', json_encode($shipping), true);
         }
         $wc_order->add_order_note('shipping address had been updated');
         $wc_order->save();
     }
}