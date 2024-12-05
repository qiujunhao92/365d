<?php
$plugin_dir = plugin_dir_path(__FILE__);
$plugin_dir = rtrim($plugin_dir, '\\/') . '/';
require  $plugin_dir . 'FeiShuSetting.php';
require  $plugin_dir . 'wpdiscuz.php';
require  $plugin_dir . 'sendEmail.php';
require  $plugin_dir . 'paysetting.php';
require  $plugin_dir . 'paypal.php';
require  $plugin_dir . 'shopmanager.php';
require  $plugin_dir . 'productlist.php';
require  $plugin_dir . 'blog.php';

require  $plugin_dir . 'ordermanager.php';
require  $plugin_dir . 'ExportOrderManager.php';
require $plugin_dir . 'Custom365d4uBlockSummary.php';
require $plugin_dir . 'CustomerInvoiceEmail.php';
require $plugin_dir . 'Cus365d4uProductStandard.php';
require $plugin_dir . 'Custom365dChatToolsFooter.php';
require $plugin_dir . 'Custom365dImageHelper.php';
require $plugin_dir . 'CustomThemeFeatureHelper.php';
require $plugin_dir . 'Custom365dBannerImages.php';
//
//add_action('init', function() {
//    flush_rewrite_rules();
//});
