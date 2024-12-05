<?php
// 插件重写规则
function custom_design_media_rewrite_rule() {
    add_rewrite_rule(
        '^design_media/([^/]+)/?$',
        'index.php?design_id=$matches[1]',
        'top'
    );
}
add_action('init', 'custom_design_media_rewrite_rule');

// 注册查询变量，允许 `design_id` 参数
function custom_design_media_query_vars($vars) {
    $vars[] = 'design_id';
    return $vars;
}
add_filter('query_vars', 'custom_design_media_query_vars');

// 在插件激活和停用时刷新重写规则
function custom_design_media_flush_rewrite_rules() {
    custom_design_media_rewrite_rule();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'custom_design_media_flush_rewrite_rules');
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

// 使用 template_redirect 检查并加载文件
function custom_design_media_template_redirect() {
    $design_id = get_query_var('design_id');
    if ($design_id) {
        include plugin_dir_path(__FILE__) . 'frontend/design-media.php';
        exit;
    }
}
add_action('template_redirect', 'custom_design_media_template_redirect');
