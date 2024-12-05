<?php

use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Oceanpayment ApplePay Payment Gateway
 *
 * Provides a Oceanpayment ApplePay Payment Gateway, mainly for testing purposes.
 *
 * @class 		WC_Gateway_Oceanapplepay
 * @extends		WC_Payment_Gateway
 * @version		1.4
 * @package		WooCommerce/Classes/Payment
 * @author 		Oceanpayment
 */
class WC_Gateway_Oceanapplepay extends WC_Payment_Gateway {

    const SEND			= "[Sent to Oceanpayment]";
    const PUSH			= "[PUSH]";
    const BrowserReturn	= "[Browser Return]";


    protected $_precisionCurrency = array(
        'BIF','BYR','CLP','CVE','DJF','GNF','ISK','JPY','KMF','KRW',
        'PYG','RWF','UGX','UYI','VND','VUV','XAF','XOF','XPF'
    );


    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        $this->id                 = 'oceanapplepay';
        $this->icon               = apply_filters('woocommerce_oceanapplepay_icon', plugins_url( 'images/applepay.svg', __FILE__ ));
        $this->has_fields         = false;
        $this->method_title       = __( 'Oceanpayment ApplePay', 'oceanpayment-applepay-gateway' );
        $this->method_description = __( '', 'oceanpayment-applepay-gateway' );
        $this->supports           = [
            'products',
//            'tokenization',
//            'add_payment_method',
        ];

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title        = $this->get_option( 'title' );
        $this->description  = $this->get_option( 'description' );
        $this->instructions = $this->get_option( 'instructions', $this->description );
        $this->enabled      = $this->get_option( 'enabled' );

        $this->Title = $this->get_option('_oceanapplepay_title') ? $this->get_option('_oceanapplepay_title') : '';
        $this->Body = $this->get_option('_oceanapplepay_description') ? $this->get_option('_oceanapplepay_description') : '';

        // Actions
        add_action( 'woocommerce_api_wc_gateway_oceanapplepay', array( $this, 'check_ipn_response' ) );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'valid-oceanapplepay-standard-itn-request', array( $this, 'successful_request' ) );
        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
        add_action( 'woocommerce_thankyou_oceanapplepay', array( $this, 'thankyou_page' ) );
        add_action( 'woocommerce_api_return_' . $this->id, array( $this, 'return_payment' ) );
        add_action( 'woocommerce_api_notice_' . $this->id, array( $this, 'notice_payment' ) );

        // Customer Emails
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );


//        add_action( 'woocommerce_receipt_oceanapplepay', array( $this, 'oceanapplepay_receipt_page' ) );
    }

    
    
    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Oceanpayment ApplePay Payment', 'woocommerce' ),
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => __( 'Title', 'woocommerce' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                'default'     => __( 'ApplePay Payment', 'woocommerce' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'woocommerce' ),
                'type'        => 'textarea',
                'css'         => 'width: 400px;',
                'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
                'default'     => __( '', 'woocommerce' ),
                'desc_tip'    => true,
            ),
            'account' => array(
                'title'       => __( 'Account', 'woocommerce' ),
                'type'        => 'text',
                'description' => __( 'Oceanpayment\'s Account.', 'woocommerce' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'terminal' => array(
                'title'       => __( 'Terminal', 'woocommerce' ),
                'type'        => 'text',
                'description' => __( 'Oceanpayment\'s Terminal.', 'woocommerce' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'securecode' => array(
                'title'       => __( 'SecureCode', 'woocommerce' ),
                'type'        => 'text',
                'description' => __( 'Oceanpayment\'s SecureCode.', 'woocommerce' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'submiturl' => array(
                'title'       => __( 'Submiturl', 'woocommerce' ),
                'type'        => 'select',
                'description' => __( 'Note: In the test state all transactions are not deducted and cannot be shipped or services provided. The interface needs to be closed in time after the test is completed to avoid consumers from placing orders.', 'woocommerce' ),
                'desc_tip'    => true,
                'options'     => array(
                    'https://secure.oceanpayment.com/gateway/service/pay' => __( 'Production', 'woocommerce' ),
                    'https://test-secure.oceanpayment.com/gateway/service/pay'   => __( 'Sandbox', 'woocommerce' ),
                ),
            ),
            
            'mode' => array(
                'title'       => __( 'Pay page Mode', 'woocommerce' ),
                'type'        => 'select',
                'description' => __( 'Iframe or Redirect', 'woocommerce' ),
                'desc_tip'    => true,
                'options'     => array(
                    'redirect' => __( 'Redirect', 'woocommerce' ),
//                    'iframe'   => __( 'Iframe', 'woocommerce' ),
                ),
            ),
			'log' => array(
                'title'       => __( 'Write The Logs', 'woocommerce' ),
                'type'        => 'select',
                'description' => __( 'Whether to write logs', 'woocommerce' ),
                'desc_tip'    => true,
                'options'     => array(
                    'true'    => __( 'True', 'woocommerce' ),
                    'false'   => __( 'False', 'woocommerce' ),
                ),
            ),
       
        );
    }

    /**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin  Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && 'oceanapplepay' === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}

    /**
     * 跳转到支付url
     */
    public function process_payment( $order_id ) {
        $order = new WC_Order( $order_id );
        return array(
            'result' 	=> 'success',
            'redirect'	=> $order->get_checkout_payment_url( true )
        );


    }



    /**
     * 生成Form表单
     */
    function receipt_page( $order ) {
        echo $this->generate_applepay_form( $order );
        die;
    }




    /**
     * 生成 ApplePay form.
     */
    public function generate_applepay_form( $order_id ) {
        $order = wc_get_order( $order_id );

        //支付币种
        $order_currency    = $order->get_currency();
        //金额
        $order_amount      = $order->get_total();
        
        //账户号
        $account           = $this->settings['account'];
        //终端号
        $terminal          = $this->settings['terminal'];
        //securecode
        $securecode        = $this->settings['securecode'];
        //支付方式
        $methods           = 'ApplePay';
        //订单号
        $order_number      = $order_id;
        //返回地址
        $backUrl			= WC()->api_request_url( 'return_' . $this->id );
        //服务器响应地址
        $noticeUrl			= WC()->api_request_url( 'notice_' . $this->id );
        //备注
        $order_notes       = '';
        //账单人名
        if(!empty($order->get_billing_first_name())){
            $billing_firstName = substr($this->OceanHtmlSpecialChars($order->get_billing_first_name()),0,50);
        }elseif(!empty($order->get_billing_last_name())){
            $billing_firstName  = substr($this->OceanHtmlSpecialChars($order->get_billing_last_name()),0,50);
        }else{
            $billing_firstName  = 'N/A';
        }
        //账单人姓
        if(!empty($order->get_billing_last_name())){
            $billing_lastName  = substr($this->OceanHtmlSpecialChars($order->get_billing_last_name()),0,50);
        }elseif(!empty($order->get_billing_first_name())){
            $billing_lastName = substr($this->OceanHtmlSpecialChars($order->get_billing_first_name()),0,50);
        }else{
            $billing_lastName  = 'N/A';
        }
        //账单人email
        $billing_email     = !empty($order->get_billing_email()) ? $order->get_billing_email() : $order_number.'@'.wp_parse_url( home_url(), PHP_URL_HOST );
        //账单人电话
        $billing_phone     = str_replace( array( '(', '-', ' ', ')', '.' ), '', $order->get_billing_phone() );
        //账单人国家
        $billing_country   = !empty($order->get_billing_country()) ? $order->get_billing_country() : 'N/A';
        //账单人州(可不提交)
        $billing_state     = $this->get_applepay_state( $order->get_billing_country(), $order->get_billing_state() );
        //账单人城市
        $billing_city      = $order->get_billing_city();
        //账单人地址
        $billing_address   = $order->get_billing_address_1();
        //账单人邮编
        $billing_zip       = $order->get_billing_postcode();
        //产品名称
        $productName       = $this->get_product($order,'name');
        //产品数量
        $productNum        = $this->get_product($order,'num');
        //产品sku
        $productSku        = $this->get_product($order,'sku');        
        //收货人的名
        $ship_firstName	   = empty(substr($this->OceanHtmlSpecialChars($order->get_shipping_first_name()),0,50)) ? $billing_firstName : substr($this->OceanHtmlSpecialChars($order->get_shipping_first_name()),0,50);
        //收货人的姓
        $ship_lastName 	   = empty(substr($this->OceanHtmlSpecialChars($order->get_shipping_last_name()),0,50)) ? $billing_lastName : substr($this->OceanHtmlSpecialChars($order->get_shipping_last_name()),0,50);
        //收货人的电话
        $ship_phone 	   = empty(str_replace( array( '(', '-', ' ', ')', '.' ), '', $order->get_billing_phone())) ? $billing_phone : str_replace( array( '(', '-', ' ', ')', '.' ), '', $order->get_billing_phone());
        //收货人的国家
        $ship_country 	   = empty($order->get_shipping_country()) ? $billing_country : $order->get_shipping_country();
        //收货人的州（省、郡）
        $ship_state 	   = empty($this->get_applepay_state( $order->get_shipping_country(), $order->get_shipping_state())) ? $billing_state : $this->get_applepay_state( $order->get_shipping_country(), $order->get_shipping_state());
        //收货人的城市
        $ship_city 		   = empty($order->get_shipping_city()) ? $billing_city : $order->get_shipping_city();
        //收货人的详细地址
        $ship_addr 		   = empty($order->get_shipping_address_1()) ? $billing_address : $order->get_shipping_address_1();
        //收货人的邮编
        $ship_zip 		   = empty($order->get_shipping_postcode()) ? $billing_zip : $order->get_shipping_postcode();
        //收货人email
        $ship_email        = empty($order->get_billing_email()) ? $billing_email : $order->get_billing_email();


        //支付页面样式
        $pages			    = $this->isMobile() ? 1 : 0;
        //网店程序类型
        $isMobile			= $this->isMobile() ? 'Mobile' : 'PC';
        $cart_info			= 'Woocommerce|V1.2.0|'.$isMobile;
        //接口版本
        $cart_api          = 'V1.4';
        //校验源字符串
        $signsrc           = $account.$terminal.$backUrl.$order_number.$order_currency.$order_amount.$billing_firstName.$billing_lastName.$billing_email.$securecode;
        //sha256加密结果
        $signValue         = hash("sha256",$signsrc);




        //记录发送到oceanpayment的post log
        $oceanpayment_log_url = dirname( __FILE__ ).'/oceanpayment_log/';

        $filedate = date('Y-m-d');

        $postdate = date('Y-m-d H:i:s');

        $newfile  = fopen( $oceanpayment_log_url . $filedate . ".log", "a+" );

        $post_log = $postdate."[POST to Oceanpayment]\r\n" .
            "account = "           .$account . "\r\n".
            "terminal = "          .$terminal . "\r\n".
            "backUrl = "           .$backUrl . "\r\n".
            "order_number = "      .$order_number . "\r\n".
            "order_currency = "    .$order_currency . "\r\n".
            "order_amount = "      .$order_amount . "\r\n".
            "billing_firstName = " .$billing_firstName . "\r\n".
            "billing_lastName = "  .$billing_lastName . "\r\n".
            "billing_email = "     .$billing_email . "\r\n".
            "billing_phone = "     .$billing_phone . "\r\n".
            "billing_country = "   .$billing_country . "\r\n".
            "billing_state = "     .$billing_state . "\r\n".
            "billing_city = "      .$billing_city . "\r\n".
            "billing_address = "   .$billing_address . "\r\n".
            "billing_zip = "       .$billing_zip . "\r\n".
            "productName = "       .$productName . "\r\n".            
            "productNum = "        .$productNum . "\r\n".            
            "productSku = "        .$productSku . "\r\n".            
            "ship_firstName = "    .$ship_firstName . "\r\n".
            "ship_lastName = "     .$ship_lastName . "\r\n".
            "ship_phone = "        .$ship_phone . "\r\n".
            "ship_country = "      .$ship_country . "\r\n".
            "ship_state = "        .$ship_state . "\r\n".
            "ship_city = "         .$ship_city . "\r\n".
            "ship_addr = "         .$ship_addr . "\r\n".
            "ship_zip = "          .$ship_zip . "\r\n".
            "ship_email = "        .$ship_email . "\r\n".
            "methods = "           .$methods . "\r\n".
            "signValue = "         .$signValue . "\r\n".
            "cart_info = "         .$cart_info . "\r\n".
            "cart_api = "          .$cart_api . "\r\n".
            "order_notes = "       .$order_notes . "\r\n";

        $post_log = $post_log . "*************************************\r\n";

        $post_log = $post_log.file_get_contents( $oceanpayment_log_url . $filedate . ".log");

        $filename = fopen( $oceanpayment_log_url . $filedate . ".log", "r+" );

        fwrite($filename,$post_log);

        fclose($filename);

        fclose($newfile);



        $data_to_send  = "<div id='loading' style='position: relative;'>";
        $data_to_send .= "<div style='position: absolute;background:#FFF; padding: 20px; border: #000 1px solid; width: 320px;margin:130px auto 0;left: 0;right:0;' id='loading'>";
        $data_to_send .= "<img src='".plugins_url( 'images/opc-ajax-loader.gif', __FILE__ )."' />Loading...Please do not refresh the page";
        $data_to_send .= "</div>";
        $data_to_send .= "</div>";
        $data_to_send .= "<form  method='post' name='applepay_checkout' action='".$this->settings['submiturl']."'  >";
        $data_to_send .= "<input type='hidden' name='account' value='" . $account . "' />";
        $data_to_send .= "<input type='hidden' name='terminal' value='" . $terminal . "' />";
        $data_to_send .= "<input type='hidden' name='order_number' value='" . $order_number . "' />";
        $data_to_send .= "<input type='hidden' name='order_currency' value='" . $order_currency . "' />";
        $data_to_send .= "<input type='hidden' name='order_amount' value='" . $order_amount . "' />";
        $data_to_send .= "<input type='hidden' name='backUrl' value='" . $backUrl . "' />";
		$data_to_send .= "<input type='hidden' name='noticeUrl' value='" . $noticeUrl . "' />";
        $data_to_send .= "<input type='hidden' name='signValue' value='" . $signValue . "' />";
        $data_to_send .= "<input type='hidden' name='order_notes' value='" . $order_notes . "' />";
        $data_to_send .= "<input type='hidden' name='methods' value='" . $methods . "' />";
        $data_to_send .= "<input type='hidden' name='billing_firstName' value='" . $billing_firstName . "' />";
        $data_to_send .= "<input type='hidden' name='billing_lastName' value='" . $billing_lastName . "' />";
        $data_to_send .= "<input type='hidden' name='billing_email' value='" . $billing_email . "' />";
        $data_to_send .= "<input type='hidden' name='billing_phone' value='" . $billing_phone . "' />";
        $data_to_send .= "<input type='hidden' name='billing_country' value='" . $billing_country . "' />";
        $data_to_send .= "<input type='hidden' name='billing_state' value='" . $billing_state . "' />";
        $data_to_send .= "<input type='hidden' name='billing_city' value='" . $billing_city . "' />";
        $data_to_send .= "<input type='hidden' name='billing_address' value='" . $billing_address . "' />";
        $data_to_send .= "<input type='hidden' name='billing_zip' value='" . $billing_zip . "' />";
        $data_to_send .= "<input type='hidden' name='productName' value='" . $productName . "' />";
        $data_to_send .= "<input type='hidden' name='productNum' value='" . $productNum . "' />";
        $data_to_send .= "<input type='hidden' name='productSku' value='" . $productSku . "' />";        
        $data_to_send .= "<input type='hidden' name='ship_firstName' value='" . $ship_firstName . "' />";
        $data_to_send .= "<input type='hidden' name='ship_lastName' value='" . $ship_lastName . "' />";
        $data_to_send .= "<input type='hidden' name='ship_phone' value='" . $ship_phone . "' />";
        $data_to_send .= "<input type='hidden' name='ship_country' value='" . $ship_country . "' />";
        $data_to_send .= "<input type='hidden' name='ship_state' value='" . $ship_state . "' />";
        $data_to_send .= "<input type='hidden' name='ship_city' value='" . $ship_city . "' />";
        $data_to_send .= "<input type='hidden' name='ship_addr' value='" . $ship_addr . "' />";
        $data_to_send .= "<input type='hidden' name='ship_zip' value='" . $ship_zip . "' />";
        $data_to_send .= "<input type='hidden' name='ship_email' value='" . $ship_email . "' />";
        $data_to_send .= "<input type='hidden' name='cart_info' value='" . $cart_info . "' />";
        $data_to_send .= "<input type='hidden' name='cart_api' value='" . $cart_api . "' />";
        $data_to_send .= "<input type='hidden' name='pages' value='" . $pages . "' />";
        $data_to_send .= "</form>";

        $data_to_send .= '<script type="text/javascript">' . "\n";
        $data_to_send .= 'document.applepay_checkout.submit();' . "\n";
        $data_to_send .= '</script>' . "\n";




        return $data_to_send;
    }

    /**
     * Update Transaction Id @scott 0703
     *
     * @param \WC_Order $order
     * @param $payment_id
     * @return void
     */
    function update_transaction_id($order, $payment_id)
    {
        try{
            if ($order && !empty( $payment_id ) ) {
                $order->set_transaction_id($payment_id);
            }
        } catch (\Exception $e) {
            error_log('Oceanpay-CreditCard Fatal Error: ' . $e->getMessage());
        }
    }

    /**
     * Check Order Is Paid
     *
     * @param $order
     * @param $post_method
     * @return bool
     */
    protected function check_order_can_update($order, $post_method)
    {
        if (!$order) {
            return false;
        }
        !$post_method && $post_method = '';
        $orderMethod = strtolower($order->get_payment_method_title() ?? '');
        $post_method = strtolower($post_method);
        if (!empty($orderMethod)) {
            if (str_contains($post_method, 'credit') && !str_contains($orderMethod, 'credit')) {
                //order pay method had changed
                return false;
            }
            if (str_contains($post_method, 'applepay') && !str_contains($orderMethod, 'applepay')) {
                //order pay method had changed
                return false;
            }
        }
        if (in_array($order->get_status(), ['processing', 'completed', 'Paid'])) {
            //order had been paid,it could not be updated
            return false;
        }
        return true;
    }

    /**
     * 异步通知
     */
    function notice_payment( $order ) {

        //获取推送输入流XML
        $xml_str = file_get_contents("php://input");

        //判断返回的输入流是否为xml
        if($this->xml_parser($xml_str)){
            $xml = simplexml_load_string($xml_str);

            //把推送参数赋值到$return_info
            $return_info['response_type']		= (string)$xml->response_type;
            $return_info['account']			    = (string)$xml->account;
            $return_info['terminal']			= (string)$xml->terminal;
            $return_info['payment_id']			= (string)$xml->payment_id;
            $return_info['order_number']		= (string)$xml->order_number;
            $return_info['order_currency']		= (string)$xml->order_currency;
            $return_info['order_amount']		= (string)$xml->order_amount;
            $return_info['payment_status']		= (string)$xml->payment_status;
            $return_info['payment_details']	    = (string)$xml->payment_details;
            $return_info['signValue']			= (string)$xml->signValue;
            $return_info['order_notes']		    = (string)$xml->order_notes;
            $return_info['card_number']		    = (string)$xml->card_number;
            $return_info['card_type']			= (string)$xml->card_type;
            $return_info['card_country']		= (string)$xml->card_country;
            $return_info['payment_authType']	= (string)$xml->payment_authType;
            $return_info['payment_risk']		= (string)$xml->payment_risk;
            $return_info['methods']			    = (string)$xml->methods;
            $return_info['payment_country']	    = (string)$xml->payment_country;
            $return_info['payment_solutions']	= (string)$xml->payment_solutions;

            //用于支付结果页面显示响应代码
            $getErrorCode		= explode(':', $return_info['payment_details']);
            $errorCode			= $getErrorCode[0];


            //匹配终端号
            if($return_info['terminal'] == $this->settings['terminal']){
                $secureCode = $this->settings['securecode'];
            }else{
                $secureCode = '';
            }


            $local_signValue  = hash("sha256",$return_info['account'].$return_info['terminal'].$return_info['order_number'].$return_info['order_currency'].$return_info['order_amount'].$return_info['order_notes'].$return_info['card_number'].
                $return_info['payment_id'].$return_info['payment_authType'].$return_info['payment_status'].$return_info['payment_details'].$return_info['payment_risk'].$secureCode);


            $order = wc_get_order( $return_info['order_number'] );
            $order_id = $order->get_id();
//            if ( isset( $return_info['payment_id'] ) ) {
//                $order->set_transaction_id( $return_info['payment_id'] );
//            }
            if($this->settings['log'] == 'true'){
                $this->postLog($return_info, self::PUSH);
            }

            strpos($this->settings['submiturl'],'test') != false ? $testorder = 'TEST ORDER - ' : $testorder = '';
            if($return_info['response_type'] == 1){
                //加密校验
                if(strtoupper($local_signValue) == strtoupper($return_info['signValue'])){

                    //支付状态
                    if ($return_info['payment_status'] == 1) {
                        $this->update_transaction_id($order, $return_info['payment_id']);
                        //成功
                        $order->update_status( 'processing', __( $testorder.$return_info['payment_details'], 'oceanpayment-applepay-gateway' ) );
                        wc_reduce_stock_levels( $order_id );
                        WC()->cart->empty_cart();
                    } elseif ($return_info['payment_status'] == -1) {
                        //待处理
//                        if(empty($this->completed_orders()) || !in_array($return_info['order_number'], $this->completed_orders())){
                        if ($this->check_order_can_update($order, $return_info['methods'])) {
                            $this->update_transaction_id($order, $return_info['payment_id']);
                    		$order->update_status( 'on-hold', __( $testorder.$return_info['payment_details'], 'oceanpayment-applepay-gateway' ) );
                        }  else {
                            $order->add_order_note("oceanapplepay return-->:"  . $testorder. $return_info['payment_details'] . '--->but not update for had paid ' . $return_info['payment_status']);
                        }
                    } elseif ($return_info['payment_status'] == 0) {
                        //失败

                        //是否点击浏览器后退造成订单号重复 20061
                        if($errorCode == '20061'){
                          
                        }else{
//                        	if(empty($this->completed_orders()) || !in_array($return_info['order_number'], $this->completed_orders())){
                            if ($this->check_order_can_update($order, $return_info['methods'])) {
                                $this->update_transaction_id($order, $return_info['payment_id']);
                    			$order->update_status( 'failed', __( $testorder.$return_info['payment_details'], 'oceanpayment-applepay-gateway' ) );
                            }  else {
                                $order->add_order_note("oceanapplepay return-->:"  . $testorder. $return_info['payment_details'] . '--->but not update for had paid ' . $return_info['payment_status']);
                            }
                        }

                    }

                }else{
                    $order->update_status( 'failed', __( $testorder.$return_info['payment_details'], 'oceanpayment-applepay-gateway' ) );
                }


                echo "receive-ok";
            }
        }
        exit;

    }

    /**
     * 浏览器返回
     */
    function return_payment( $order ) {

        //返回账户
        $account          = $this->settings['account'];
        //返回终端号
        $terminal         = $this->settings['terminal'];
        //返回Oceanpayment 的支付唯一号
        $payment_id       = sanitize_text_field($_REQUEST['payment_id']);
        //返回网站订单号
        $order_number     = sanitize_text_field($_REQUEST['order_number']);
        //返回交易币种
        $order_currency   = sanitize_text_field($_REQUEST['order_currency']);
        //返回支付金额
        $order_amount     = sanitize_text_field($_REQUEST['order_amount']);
        //返回支付状态
        $payment_status   = sanitize_text_field($_REQUEST['payment_status']);
        //返回支付详情
        $payment_details  = sanitize_text_field($_REQUEST['payment_details']);

        //用于支付结果页面显示响应代码
        $getErrorCode		= explode(':', $payment_details);
        $errorCode			= $getErrorCode[0];

        //返回交易安全签名
        $back_signValue   = sanitize_text_field($_REQUEST['signValue']);
        //返回备注
        $order_notes      = sanitize_text_field($_REQUEST['order_notes']);
        //未通过的风控规则
        $payment_risk     = sanitize_text_field($_REQUEST['payment_risk']);
        //返回支付信用卡卡号
        $card_number      = sanitize_text_field($_REQUEST['card_number']);
        //返回交易类型
        $payment_authType = sanitize_text_field($_REQUEST['payment_authType']);
        //解决方案
        $payment_solutions = sanitize_text_field($_REQUEST['payment_solutions']);

        //匹配终端号
        if($terminal == $this->settings['terminal']){
            $secureCode = $this->settings['securecode'];
        }else{
            $secureCode = '';
        }


        //SHA256加密
        $local_signValue = hash("sha256",$account.$terminal.$order_number.$order_currency.$order_amount.$order_notes.$card_number.
            $payment_id.$payment_authType.$payment_status.$payment_details.$payment_risk.$secureCode);


        $order = wc_get_order( $order_number );
//        if ( isset( $payment_id ) ) {
//            $order->set_transaction_id( $payment_id );
//        }
        if($this->settings['log'] === 'true') {
            $this->postLog($_REQUEST, self::BrowserReturn);
        }
        //返回支付
        $methods = sanitize_text_field($_REQUEST['methods'] ?? 'ApplePay');

        strpos($this->settings['submiturl'],'test') != false ? $testorder = 'TEST ORDER - ' : $testorder = '';
        //加密校验
        if(strtoupper($local_signValue) == strtoupper($back_signValue)){
                      
            //支付状态
            if ($payment_status == 1) {
                $this->update_transaction_id($order, $payment_id);
                //成功
                $order->update_status( 'processing', __( $testorder.$payment_details, 'oceanpayment-applepay-gateway' ) );
                WC()->cart->empty_cart();
                $url = $this->get_return_url( $order );
                wc_add_notice( $testorder.$payment_details, 'success' );
            } elseif ($payment_status == -1) {
                //待处理               
//                if(empty($this->completed_orders()) || !in_array($_REQUEST['order_number'], $this->completed_orders())){
                if ($this->check_order_can_update($order, $methods)) {
                    $this->update_transaction_id($order, $payment_id);
                	$order->update_status( 'on-hold', __( $testorder.$payment_details, 'oceanpayment-applepay-gateway' ) );
        		}
                $url = $this->get_return_url( $order );
                wc_add_notice( $testorder.$payment_details, 'success' );
            } elseif ($payment_status == 0) {
                //失败

                //是否点击浏览器后退造成订单号重复 20061
                if($errorCode == '20061'){
                    $url = esc_url( wc_get_checkout_url() );
                }else{                   
//					if(empty($this->completed_orders()) || !in_array($_REQUEST['order_number'], $this->completed_orders())){
                    if ($this->check_order_can_update($order, $methods)) { //check update
                        $this->update_transaction_id($order, $payment_id);
                		$order->update_status( 'failed', __( $testorder.$payment_details, 'oceanpayment-applepay-gateway' ) );
        			}
                    $url = esc_url( wc_get_checkout_url() );
                    wc_add_notice( $testorder.$payment_details, 'error' );
                    wc_add_notice( $payment_solutions, 'error' );
                }
                $url = apply_filters('ocean_pay_get_fail_url', $url, ($_REQUEST['order_number'] ?? ''), $order);
            }

        }else{
            $order->update_status( 'failed', __( $testorder.$payment_details, 'oceanpayment-applepay-gateway' ) );
            $url = esc_url( wc_get_checkout_url() );
            wc_add_notice( $testorder.$payment_details, 'error' );
        }


        //页面跳转
        $this->getJslocationreplace($url);
        exit;

    }




    /**
     * thankyou_page
     */
    public function thankyou_page($order_id) {


    }
    
    /**
     * 是否存在相同订单号
     * @return unknown
     */
    function completed_orders(){
    
        global $wpdb;
    
        $query = $wpdb->get_results("
    
            SELECT pm.meta_value AS user_id, pm.post_id AS order_id
            FROM {$wpdb->prefix}postmeta AS pm
            LEFT JOIN {$wpdb->prefix}posts AS p
            ON pm.post_id = p.ID
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed','wc-Processing')
            AND pm.meta_key = '_customer_user'
            ORDER BY pm.meta_value ASC, pm.post_id DESC
            ");
    
        // We format the array by user ID
        $results = [];
        foreach($query as $result){
            $results[] = $result->order_id;
        }
    
        return $results;
    }


    /**
     * 获取产品信息
     * @param unknown $order
     * @param unknown $sort
     * @return multitype:NULL
     */
    public function get_product($order,$type){
        $product_array = array();      
        foreach ($order->get_items() as $item_key => $item ){
            $item_data = $item->get_data();
            $product = $item->get_product();
            if($type == 'num'){
                $item_data['quantity'] != '' ? $product_array[] = substr($item_data['quantity'], 0,50) : $product_array[] = 'N/A';
            }elseif($type == 'sku'){
                $product->get_sku() != '' ? $product_array[] = substr($product->get_sku(), 0,500) : $product_array[] = 'N/A';
            }elseif($type == 'name'){
                $product->get_name() != '' ? $product_array[] = substr($product->get_name(), 0,500) : $product_array[] = 'N/A';
            }         
        }
        return implode(';', $product_array);
    }


    /**
     * 获取州/省
     */
    public function get_applepay_state( $cc, $state ) {
        $iso_cn = ["北京"=>"BJ","天津"=>"TJ","河北"=>"HB","内蒙古"=>"NM","辽宁"=>"LN","黑龙江"=>"HL","上海"=>"SH","浙江"=>"ZJ","安徽"=>"AH","福建"=>"FJ","江西"=>"JX","山东"=>"SD","河南"=>"HA","湖北"=>"HB","湖南"=>"HN","广东"=>"GD","广西"=>"GX","海南"=>"HI","四川"=>"SC","贵州"=>"GZ","云南"=>"YN","西藏"=>"XZ","重庆"=>"CQ","陕西"=>"SN","甘肃"=>"GS","青海"=>"QH","宁夏"=>"NX","新疆"=>"XJ"];
        $states = WC()->countries->get_states( $cc );

        if('CN' === $cc){
            if ( isset( $iso_cn[$states[$state]] ) ) {
                return $iso_cn[$states[$state]];
            }
        }

        return $state;
    }

    /**
     * log
     */
    public function postLog($data, $logType){

        //记录发送到oceanpayment的post log
        $filedate = date('Y-m-d');
        $newfile  = fopen( dirname( __FILE__ )."/oceanpayment_log/" . $filedate . ".log", "a+" );
        $post_log = date('Y-m-d H:i:s').$logType."\r\n";
        foreach ($data as $k=>$v){
            $post_log .= $k . " = " . $v . "\r\n";
        }
        $post_log = $post_log . "*************************************\r\n";
        $post_log = $post_log.file_get_contents( dirname( __FILE__ )."/oceanpayment_log/" . $filedate . ".log");
        $filename = fopen( dirname( __FILE__ )."/oceanpayment_log/" . $filedate . ".log", "r+" );
        fwrite($filename,$post_log);
        fclose($filename);
        fclose($newfile);

    }

    /**
     * 格式化金额
     */
    function formatAmount($order_amount, $order_currency){

        if(in_array($order_currency, $this->_precisionCurrency)){
            $order_amount = round($order_amount, 0);
        }else{
            $order_amount = round($order_amount, 2);
        }

        return $order_amount;

    }

    /**
     * 检验是否移动端
     */
    function isMobile(){
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])){
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 判断手机发送的客户端标志
        if (isset ($_SERVER['HTTP_USER_AGENT'])){
            $clientkeywords = array (
                'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel',
                'lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm',
                'operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
                return true;
            }
        }
        // 判断协议
        if (isset ($_SERVER['HTTP_ACCEPT'])){
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
                return true;
            }
        }
        return false;
    }


    /**
     * 钱海支付Html特殊字符转义
     */
    function OceanHtmlSpecialChars($parameter){

        //去除前后空格
        $parameter = trim($parameter);

        //转义"双引号,<小于号,>大于号,'单引号
        $parameter = str_replace(array("<",">","'","\""),array("&lt;","&gt;","&#039;","&quot;"),$parameter);

        return $parameter;

    }


    /**
     *  通过JS跳转出iframe
     */
    public function getJslocationreplace($url)
    {
        echo '<script type="text/javascript">parent.location.replace("'.$url.'");</script>';

    }


    /**
     *  判断是否为xml
     */
    function xml_parser($str){
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$str,true)){
            xml_parser_free($xml_parser);
            return false;
        }else {
            return true;
        }
    }

}
