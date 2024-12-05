<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Blocks\BlockTypes\OrderConfirmation\Summary;

if (!class_exists('Custom365d4uBlockSummary')) {
    class Custom365d4uBlockSummary
    {

        private  function getCustomPaymentDate()
        {
            $orderId = absint( get_query_var( 'order-received' ) );
            $payment_date = get_post_meta($orderId, 'payment_date', true);
            $sRetDate = '';
            if ($payment_date) {
                $sRetDate = date('M j, Y', strtotime($payment_date) + 8 * 3600);
            }
            return $sRetDate;
        }

        public function renderContent($block_content, $parsedBlock, $obj) {

            $paidDate = $this->getCustomPaymentDate();
            if (!empty($paidDate)) {
                $sSourceHtml = '/<li class="wc-block-order-confirmation-summary-list-item"><span class="wc-block-order-confirmation-summary-list-item__key">Date:<\/span>.*?<span(.*?)>.*?<\/span><\/li>/ius';
                $sTargetHtml = '<li class="wc-block-order-confirmation-summary-list-item"><span class="wc-block-order-confirmation-summary-list-item__key">Paid Date:</span><span$1>'. $paidDate  .'</span></li>';
            } else {
                $sSourceHtml = '/<li class="wc-block-order-confirmation-summary-list-item"><span class="wc-block-order-confirmation-summary-list-item__key">Date:<\/span>/ius';
                $sTargetHtml = '<li class="wc-block-order-confirmation-summary-list-item"><span class="wc-block-order-confirmation-summary-list-item__key">Create Date:</span>';
            }
            return preg_replace($sSourceHtml, $sTargetHtml, $block_content);
        }
    }
    $customSummary = new Custom365d4uBlockSummary();
    add_filter("render_block_woocommerce/order-confirmation-summary", array($customSummary, 'renderContent'), 10, 3);
}


