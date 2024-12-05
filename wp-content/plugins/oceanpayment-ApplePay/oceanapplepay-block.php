<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Oceanapplepay_Gateway_Blocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'oceanapplepay';
    public function initialize() {
        $this->settings = get_option( 'woocommerce_oceanapplepay_settings', [] );
        $this->gateway = new WC_Gateway_Oceanapplepay();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'oceanapplepay-blocks-integration',
            plugin_dir_url(__FILE__) . 'js/oceanapplepay-block.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
        if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'oceanapplepay-blocks-integration');
            
        }


        return [ 'oceanapplepay-blocks-integration' ];
    }


    public function get_payment_method_data() {
        $icons[] = array(
            'id'  => 'applepay_icon',
            'alt' => 'ApplePay',
            'src' => WC_HTTPS::force_https_url( plugins_url('images/applepay.svg' , __FILE__ ) )
        );
        return [
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
            'icons'=>$icons
        ];
    }

}
?>