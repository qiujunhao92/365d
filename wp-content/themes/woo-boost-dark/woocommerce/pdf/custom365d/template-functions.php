<?php
/**
 * Use this file for all your template filters and actions.
 * Requires PDF Invoices & Packing Slips for WooCommerce 1.4.13 or higher
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function cus_wcpdf_met_action($meta_box_actions, $order_id)
{
    unset($meta_box_actions['packing-slip']);
    return $meta_box_actions;
}

add_filter('wpo_wcpdf_meta_box_actions', 'cus_wcpdf_met_action', 10, 2);
