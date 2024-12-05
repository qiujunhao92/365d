<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Oceancreditcardonepage_Gateway_Blocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'oceancreditcardonepage';// your payment gateway name

    public function initialize() {
        $this->settings = get_option( 'woocommerce_oceancreditcardonepage_settings', [] );
        $this->gateway = new WC_Gateway_Oceancreditcardonepage();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'oceancreditcardonepage-blocks-integration',
            plugin_dir_url(__FILE__) . 'js/oceancreditcardonepage-block.js',
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
            wp_set_script_translations( 'oceancreditcardonepage-blocks-integration');
            
        }


        wp_register_script( 'opjquery',  plugins_url('js/opjquery.js', __FILE__ ) , '', '', true );
        wp_enqueue_script('opjquery');
        wp_register_script( 'onepage-carddata', 'https://secure.oceanpayment.com/pages/js/onepage-carddata.js' , '', '', true );
        wp_enqueue_script('onepage-carddata');


        return [ 'oceancreditcardonepage-blocks-integration' ];
    }




    public function get_payment_method_data() {
        $logo = $this->settings['logo'];
        $icons = [];
        if(!empty($logo)){
            foreach ( $logo as $vo ) {
                $icons[] = array(
                    'id'  => $vo.'_icon',
                    'alt' => $vo,
                    'src' => WC_HTTPS::force_https_url( plugins_url('images/'.$vo.'.png' , __FILE__ ) )
                );
            }
        }


        return [
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
            'icons' => $icons,
            'submiturl' => $this->settings['submiturl'],
            'cssurl' => $this->settings['cssurl'],
            'language' => $this->settings['language'],
            'public_key' => $this->settings['public_key'],
            'SSL' => $this->settings['SSL'],
            'HTTP_HOST' => $_SERVER['HTTP_HOST'],

        ];
    }

}
?>