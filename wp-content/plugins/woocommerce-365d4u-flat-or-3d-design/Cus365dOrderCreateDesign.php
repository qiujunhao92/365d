<?php
if (!class_exists('Cus365dOrderCreateDesign')) {
    class Cus365dOrderCreateDesign {
        function __construct() {

        }

        function display_flat_design_meta_box($order) {
            $order_id = $order->get_id();

            // 获取与该订单关联的 Flat/3D Design
            $related_designs = get_posts(array(
                'post_type' => 'flat_design',
                'meta_key' => 'flat_design_orders',
                'meta_value' => $order_id,
                'numberposts' => 200
            ));

            echo '<div class="flat-design-box" style="margin-top:10px;">';
            $listUrl = admin_url("admin.php?page=flat-design-list&order_filter=" . $order_id);
            echo '<h3>' . __('Flat/3D Design')
                 .'&nbsp;<a href="' . $listUrl .'" target="_blank">(View All)</a>'
                 . '</h3>';
            $relatedDesignBegin = count($related_designs);
            if (!empty($related_designs)) {
                echo '<ul>';
                foreach ($related_designs as $design) {
                    echo '<li><a href="' . get_edit_post_link($design->ID) . '" target="_blank">' . esc_html($design->post_title) . '</a></li>';
                }
                echo '</ul>';
            } else {
                echo '<p>No Flat/3D Designs associated with this order.</p>';
            }

            // 添加创建按钮
            echo '<button id="add_flat_design_button" class="button">Add Flat/3D Designs</button>';

            echo '<input type="hidden" id="order_id" value="' . esc_attr($order_id) . '">';

            // 添加一个 JS 用于弹出框
            echo <<<HTML
  <script type="text/javascript">
            jQuery(document).ready(function($) {
                $("#add_flat_design_button").on("click", function(e) {
                    e.preventDefault();
                    var orderId = $("#order_id").val();
                    var begin = {$relatedDesignBegin};
                    var numberOfDesigns = prompt("Enter the number of Flat/3D Designs to create:");

                    if (numberOfDesigns && numberOfDesigns > 0) {
                        if (begin + numberOfDesigns > 20) {
                            alert("Only 20  " + response.data);
                            return;
                        }
                        // 创建新的 Flat/3D Design
                        $.post(ajaxurl, {
                            action: "create_flat_designs",
                            order_id: orderId,
                            number_of_designs: numberOfDesigns
                        }, function(response) {
                            if (response.success) {
                                alert("Flat/3D Designs created successfully.");
                                // 重定向到 Flat/3D Design 列表页，并根据订单 ID 进行过滤
                                window.location.href = "{$listUrl}";
                            } else {
                                alert("An error occurred: " + response.data);
                            }
                        });
                    }
                });
            });
            </script>
HTML;
              echo '</div>';
        }

        function create_flat_designs_for_order() {
            // 获取订单 ID 和设计数量
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
            $number_of_designs = isset($_POST['number_of_designs']) ? intval($_POST['number_of_designs']) : 1;

            if ($order_id && $number_of_designs > 0) {
                global $wpdb;
                $sql = <<<SQL
    SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'flat_design'
        AND p.post_status = 'publish'
        AND pm.meta_key = 'flat_design_orders'
        AND pm.meta_value LIKE %s
    
SQL;

                // 执行查询，获取 Flat Designs 的数量
                $flat_design_count = $wpdb->get_var($wpdb->prepare($sql, '%#' . $order_id . '#%'));
                for ($i = 1; $i <= $number_of_designs; $i++) {
                    // 创建新 Flat/3D Design
                    $design_title = $order_id . ' #Design ' . ($flat_design_count + $i);
                    $design_id = wp_insert_post(array(
                        'post_title' => $design_title,
                        'post_type' => 'flat_design',
                        'post_status' => 'publish',
                    ));

                    if ($design_id) {
                        // 将订单 ID 关联到新建的 Flat/3D Design
                        update_post_meta($design_id, 'flat_design_orders', '#' .  $order_id .'#');
                    }
                }

                wp_send_json_success();
            } else {
                wp_send_json_error('Invalid order ID or number of designs.');
            }
        }
    }

    $cus365dCreateDesign = new Cus365dOrderCreateDesign();
    // 使用 WooCommerce 的钩子将内容添加到订单页面中
    add_action('woocommerce_admin_order_data_after_order_details', array($cus365dCreateDesign, 'display_flat_design_meta_box'));
    // AJAX 创建 Flat/3D Design
    add_action('wp_ajax_create_flat_designs', array($cus365dCreateDesign, 'create_flat_designs_for_order'));
}
