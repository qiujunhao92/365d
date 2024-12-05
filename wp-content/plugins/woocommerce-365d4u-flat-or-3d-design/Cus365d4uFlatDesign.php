<?php
if (!class_exists('Cus365d4uFlatDesign')) {
    class Cus365d4uFlatDesign {
        function create_flat_design_post_type() {
            register_post_type('flat_design',
                array(
                    'labels' => array(
                        'name' => __('Flat/3D Designs'),
                        'singular_name' => __('Flat Design')
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'show_ui' => true,
                    'show_in_menu' => false,
                    'supports' => array('title', 'editor', 'thumbnail'),
                    'capability_type' => 'post',
                )
            );
        }

        function flat_design_admin_menu() {
            add_submenu_page(
                'woocommerce',
                'Flat/3D Designs',
                'Flat/3D Designs',
                'manage_woocommerce',
                'flat-design-list',
                array($this, 'render_flat_design_list')
            );
        }

        private function getRejectedItems() {
            global $wpdb;
            $sql = <<<SQL
   SELECT count(1) as cnt FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'flat_design'
        AND p.post_status = 'publish'
        AND pm.meta_key = 'customer_feedback'
        AND pm.meta_value like '%Rejected%'
SQL;
           $rows =  $wpdb->get_results($sql, ARRAY_A);
           if (empty($rows)) {
               return 0;
           }
           return intval($rows[0]['cnt']);
        }

        function render_flat_design_list() {
            if ($_GET && $_GET['action'] == 'duplicate' && !empty($_GET['post'])) {
                $this->duplicate_flat_design($_GET['post']);
                wp_redirect(admin_url('admin.php?page=flat-design-list'));
                exit;
            }
            $flatDesignListTable = new Flat_Design_List_Table();
            $flatDesignListTable->prepare_items();

            $rejectedCnt = $this->getRejectedItems();

            echo '<div class="wrap">';
            echo '<h1 class="wp-heading-inline">Flat and 3D Designs</h1>';
            echo '<div id="modAddDiv">';
            if (!empty($_GET['order_filter'])) {
                $url = admin_url('post-new.php?post_type=flat_design&order_filter=' . $_GET['order_filter']);
                echo '<a href="' . $url . '" class="page-title-action">Add New Flat/3D Design For #' . $_GET['order_filter'] . '</a>';
            } else {
                echo '<a href="' . admin_url('post-new.php?post_type=flat_design') . '" class="page-title-action">Add New Flat/3D Design</a>';
            }
            if ($rejectedCnt) {
                $editUrl = admin_url('admin.php?page=flat-design-list&status_filter=publish&feedback_filter=Rejected');
                echo <<<DIV
  <span>
     <span style="color: red;">
      There are {$rejectedCnt} items that had been rejected by customers and need to duplicate for editing
     </span>
      <span> You can click <a href="{$editUrl}"><span style="font:16px;font-weight: bold;">Here</span></a> to edit it</span>
   </span>
DIV;
         }
            echo '</div>';
            $flatDesignListTable->views();
            echo '<form method="post">';
            $flatDesignListTable->display();
            echo '</form>';
            echo '</div>';
        }

        function add_flat_design_meta_boxes() {
            add_meta_box(
                'account_information',                 // Meta Box ID
                'Account and Financial Information',   // Title
                array($this, 'account_meta_box_callback'), // Callback Function
                'flat_design',                         // Post Type
                'normal',                              // Context
                'high'                                 // Priority
            );
            add_meta_box('flat_design_media', 'Flat Design Media', array($this, 'flat_design_media_callback'), 'flat_design', 'normal', 'high');
            add_meta_box('3d_design_media', '3D Design Media', array($this, 'design_3d_media_callback'), 'flat_design', 'normal', 'high');
            add_meta_box('production_design_media', 'Production Design Media', array($this, 'production_media_callback'), 'flat_design', 'normal', 'high');
            add_meta_box('flat_design_orders', 'Related Orders', array($this, 'flat_design_orders_callback'), 'flat_design', 'normal', 'default');
            add_meta_box('flat_design_feedback', 'Customer Feedback', array($this, 'customer_feedback_callback'), 'flat_design', 'side', 'default');
            add_meta_box('flat_design_copied_by', 'Copied By', array($this, 'flat_design_copied_by_callback'), 'flat_design', 'side', 'default');
        }

        function account_meta_box_callback($post) {
            // 获取当前字段值
            $related_account = get_post_meta($post->ID, 'related_user_account', true);
            $receivables = get_post_meta($post->ID, 'receivables', true);
            $payables = get_post_meta($post->ID, 'payables', true);
            // 内联样式包含媒体查询
            echo '<style>
        .account-meta-box-container { display: flex; flex-direction: column; }
        .account-meta-box-row { display: flex; justify-content: space-between; gap: 20px;}
        .account-meta-box-row label, .account-meta-box-row input { width: 33%; }

        @media (max-width: 768px) {
            .account-meta-box-row { flex-direction: column; align-items: stretch; }
            .account-meta-box-row label, .account-meta-box-row input { width: 100%; margin-bottom: 10px; }
        }
    </style>';

            // 输出字段
            echo '<div class="account-meta-box-container">';

            // 第一行显示所有的 labels
            echo '<div class="account-meta-box-row">';
            echo '<label for="related_user_account">User Related Account:</label>';
            echo '<label for="receivables">Receivables For This Product:</label>';
            echo '<label for="payables">Total Paid For This Product:</label>';
            echo '</div>';
            if (!empty($_GET['order_filter'])) {
                echo '<input type="hidden" id="order_filter" name="order_filter" value="' . esc_attr($_GET['order_filter']) . '" />';
            }

            // 第二行显示所有的 inputs
            echo '<div class="account-meta-box-row">';
            echo '<input type="text" id="related_user_account" name="related_user_account" value="' . esc_attr($related_account) . '" />';
            echo '<input type="number" id="receivables" name="receivables" value="' . esc_attr($receivables) . '" step="1" />';
            echo '<input type="number" id="payables" name="payables" value="' . esc_attr($payables) . '" step="1" />';
            echo '</div>';

            echo '</div>';

            echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const receivablesInput = document.getElementById("receivables");
            const payablesInput = document.getElementById("payables");

            function validatePayables() {
                const receivablesValue = parseFloat(receivablesInput.value);
                const payablesValue = parseFloat(payablesInput.value);

                if (!isNaN(receivablesValue) && !isNaN(payablesValue) && payablesValue > receivablesValue) {
                    alert("Payables cannot be greater than Receivables.");
                    payablesInput.value = receivablesValue;
                }
            }
            payablesInput.addEventListener("input", validatePayables);
        });
    </script>';
        }


        function flat_design_copied_by_callback($post) {
            $copied_by = get_post_meta($post->ID, 'copied_by', true) ?: 'N/A';
            echo '<p>' . esc_html($copied_by) . '</p>';
        }

        function duplicate_flat_design($post_id) {
            $post = get_post($post_id);
            if (!$post) {
                return;
            }
            $new_post = array(
                'post_type'     => $post->post_type,
                'post_title'    => $post->post_title . '(Copy)',
                'post_status'   => 'publish',  // 设置为 'publish' 自动发布
                'post_author'   => get_current_user_id(),
            );
            $new_post_id = wp_insert_post($new_post);

            $meta_keys = array('flat_design_images', 'flat_design_videos', '3d_design_images', '3d_design_videos', 'production_images', 'production_videos', 'flat_design_orders');
            foreach ($meta_keys as $meta_key) {
                $meta_value = get_post_meta($post_id, $meta_key, true);
                update_post_meta($new_post_id, $meta_key, $meta_value);
            }
            $related_value = get_post_meta($post_id, 'related_design', true);
            if(empty($related_value)) {
                update_post_meta($new_post_id, 'related_design', $post_id);
            } else {
                update_post_meta($new_post_id, 'related_design', $related_value);
            }
            update_post_meta($new_post_id, 'design_status', 'Enable');
            update_post_meta($post_id, 'design_status', 'Disable');
            update_post_meta($new_post_id, 'copied_by', wp_get_current_user()->display_name);

           // 更新原来文章状态为Draft
            $post_data = array(
                'ID'           => $post_id,
                'post_status'  => 'draft',
            );
            wp_update_post($post_data);
        }

        function flat_design_media_callback($post) {
            $this->display_media_fields($post, 'flat_design_images', 'flat_design_videos', 'Flat Design');
        }

        function design_3d_media_callback($post) {
            $this->display_media_fields($post, '3d_design_images', '3d_design_videos', '3D Design');
        }

        function production_media_callback($post) {
            $this->display_media_fields($post, 'production_images', 'production_videos', 'Production');
        }

        function display_media_fields($post, $image_meta_key, $video_meta_key, $label) {
            $images = get_post_meta($post->ID, $image_meta_key, true);
            $videos = get_post_meta($post->ID, $video_meta_key, true);

            echo "<h4>Upload {$label} Images</h4>";
            echo '<input type="button" id="upload_' . $image_meta_key . '_button" class="button" value="Upload Images" />';
            echo '<div id="' . $image_meta_key . '_gallery">';
            if (!empty($images)) {
                foreach ($images as $image_id) {
                    $image_url = wp_get_attachment_image_src($image_id, 'thumbnail');
                    if ($image_url) {
                        echo '<img src="' . esc_url($image_url[0]) . '" width="100" height="100" style="margin-right: 10px;">';
                    }
                }
            }
            echo '</div>';
            echo '<input type="hidden" id="' . $image_meta_key . '" name="' . $image_meta_key . '" value="' . esc_attr(implode(',', (array)$images)) . '" />';

            echo "<h4>Upload {$label} Videos</h4>";
            echo '<input type="button" id="upload_' . $video_meta_key . '_button" class="button" value="Upload Videos" />';
            echo '<div id="' . $video_meta_key . '_gallery">';
            if (!empty($videos)) {
                foreach ($videos as $video_id) {
                    $video_url = wp_get_attachment_url($video_id);
                    if ($video_url) {
                        echo '<video width="150" height="100" controls><source src="' . esc_url($video_url) . '" type="video/mp4"></video>';
                    }
                }
            }
            echo '</div>';
            echo '<input type="hidden" id="' . $video_meta_key . '" name="' . $video_meta_key . '" value="' . esc_attr(implode(',', (array)$videos)) . '" />';
        }

        function flat_design_orders_callback($post) {
            $related_orders = get_post_meta($post->ID, 'flat_design_orders', true);
            $related_orders = is_string($related_orders) ? explode(',', $related_orders) : [];

            echo '<select name="flat_design_orders[]" id="flat_design_orders_select" multiple="multiple" style="width:100%;">';
            foreach ($related_orders as $order_id) {
                $order_id = trim($order_id, '#');
                $order = wc_get_order($order_id);
                if ($order) {
                    echo '<option value="' . esc_attr($order_id) . '" selected="selected">Order #' . esc_html($order_id) . '</option>';
                }
            }
            echo '</select>';

            list($list_url, $title) = $this->showReturnList();
            echo '<a href="' . $list_url . '" class="button" style="margin-top: 20px;">' . $title . '</a>';
        }

        function customer_feedback_callback($post) {
            $feedback = get_post_meta($post->ID, 'customer_feedback', true);
            $feedback = is_array($feedback) ? $feedback : ['status' => 'Pending', 'comment' => '' , 'date' => ''];

            $options = ['Pending', 'Approved', 'Rejected'];
            echo '<label for="customer_feedback[status]">Customer Feedback:</label>';
            echo '<select name="customer_feedback[status]" id="customer_feedback_status" disabled>';
            $feedBackStatus = $feedback['status'] ?? 'Pending';
            foreach ($options as $option) {
                $selected = ($feedBackStatus === $option) ? 'selected' : '';
                echo "<option value=\"{$option}\" {$selected}>{$option}</option>";
            }
            echo '</select>';

            echo '<p><strong>Customer Comment:</strong></p>';
            echo '<textarea name="customer_feedback[comment]" readonly>' . esc_textarea($feedback['comment']) . '</textarea>';
            echo '<p><strong>Feedback Date:' . ($feedback['date'] ?? '') . '</strong></p>';
        }

        function showReturnList() {
            if (!empty($_GET['order_filter'])) {
                $list_url = admin_url('admin.php?page=flat-design-list&order_filter=' . $_GET['order_filter']);
                $title = 'Return to Flat/3D List For #' . esc_html($_GET['order_filter']);
            } else {
                $list_url = admin_url('admin.php?page=flat-design-list');
                $title = 'Return to Flat/3D Design List';
            }
            return [$list_url, $title];
        }

        function enqueue_select2_scripts() {
            wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
            wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);

            wp_add_inline_script('select2-js', '
                jQuery(document).ready(function($) {
                    $("#flat_design_orders_select").select2({
                        ajax: {
                            url: ajaxurl,
                            dataType: "json",
                            delay: 150,
                            data: function (params) {
                                return {
                                    term: params.term,
                                    action: "search_orders",
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 1,
                        placeholder: "Search for an order",
                        allowClear: true
                    });
                });
            ');
        }

        function flat_design_enqueue_media() {
            global $post_type;

            if ($post_type === 'flat_design') {
                list($list_url, $title) = $this->showReturnList();
                wp_add_inline_script('jquery', '
                    jQuery(document).ready(function($) {
                        $(".wrap > h1").after("<a href=\"' . $list_url .                        '\" class=\"page-title-action\">' . $title . '</a>");
                    });
                ');
            }
            wp_enqueue_media();
            wp_enqueue_script(
                'flat_design_script',
                plugin_dir_url(__FILE__) . 'static/flat-design.js',
                array('jquery'),
                time(),  // 使用当前时间戳作为版本号，确保不使用缓存
                true
            );
        }

        function save_flat_order_or_media($post_id) {
            $media_fields = [
                'flat_design_images', 'flat_design_videos',
                '3d_design_images', '3d_design_videos',
                'production_images', 'production_videos'
            ];

            foreach ($media_fields as $field) {
                if (isset($_POST[$field])) {
                    $media_ids = explode(',', sanitize_text_field($_POST[$field]));
                    update_post_meta($post_id, $field, $media_ids);
                }
            }

            if (isset($_POST['flat_design_orders'])) {
                $formatted_orders = array_map(function($order) {
                    return '#' . $order . '#';
                }, $_POST['flat_design_orders']);
                $formatted_orders_string = implode(',', $formatted_orders);
                update_post_meta($post_id, 'flat_design_orders', $formatted_orders_string);
            }

            if (isset($_POST['customer_feedback'])) {
                update_post_meta($post_id, 'customer_feedback', $_POST['customer_feedback']);
            }

            // 验证和保存额外字段数据
            if (isset($_POST['related_user_account'])) {
                update_post_meta($post_id, 'related_user_account', sanitize_text_field($_POST['related_user_account']));
            }
            if (isset($_POST['receivables'])) {
                update_post_meta($post_id, 'receivables', floatval($_POST['receivables']));
            }
            if (isset($_POST['payables'])) {
                update_post_meta($post_id, 'payables', floatval($_POST['payables']));
            }
        }

        function ajax_search_orders() {
            $search_term = sanitize_text_field($_GET['term']);
            $search_term = esc_attr($search_term) . '%';
            global $wpdb;
            $strSql = "SELECT p.id AS order_id FROM wp_wc_orders p WHERE p.id like '{$search_term}' and p.status in ('wc-processing', 'wc-completed') order by  p.id desc limit 20;";
            $orders = $wpdb->get_results($strSql, ARRAY_A);
            $results = [];
            foreach ($orders as $order) {
                $results[] = array(
                    'id' => $order->get_id(),
                    'text' => 'Order #' . $order->get_id(),
                );
            }
            wp_send_json($results);
        }

        function modify_post_labels($translated_text, $text, $domain) {
            global $post_type;

            if ($post_type === 'flat_design') {
                switch ($text) {
                    case 'Edit Post':
                        $translated_text = 'Edit Flat/3D Design';
                        break;
                    case 'Add New':
                    case 'Add New Post':
                        $translated_text = 'Add New Flat/3D Design';
                        break;
                }
            }

            return $translated_text;
        }

        function change_admin_title() {
            global $post_type, $title;

            if ($post_type === 'flat_design') {
                if ($title === 'Edit Post') {
                    $title = 'Edit Flat/3D Design';
                } elseif ($title === 'Add New Post') {
                    $title = 'Add New Flat/3D Design';
                }
            }
        }
        function change_design_link($post_link, $post, $leavename, $sample)
        {
            if ($post->post_type === 'flat_design') {
                $encryptId  =  EncryptData365d4u::encrypt($post->ID);
                return site_url('/design_media/' . $encryptId . '/');
            }
            return $post_link;
        }

        function cus365_redirect_post_location($location)
        {
            if (!empty($_POST['order_filter']) && $location) {
                $orderFilter = 'order_filter=' . $_POST['order_filter'];
                if (str_contains($location, '?')) {
                    $location .= '&' . $orderFilter;
                } else {
                    $location .= '?' . $orderFilter;
                }
            }
            return $location;
        }
    }

    $cus365dFlatDesign = new Cus365d4uFlatDesign();
    add_action('init', array($cus365dFlatDesign, 'create_flat_design_post_type'));
    add_action('admin_menu', array($cus365dFlatDesign, 'flat_design_admin_menu'));
    add_action('admin_enqueue_scripts', array($cus365dFlatDesign, 'flat_design_enqueue_media'));
    add_action('add_meta_boxes', array($cus365dFlatDesign, 'add_flat_design_meta_boxes'));
    add_action('save_post', array($cus365dFlatDesign, 'save_flat_order_or_media'));
    add_action('admin_enqueue_scripts', array($cus365dFlatDesign, 'enqueue_select2_scripts'));
    add_action('wp_ajax_search_orders', array($cus365dFlatDesign, 'ajax_search_orders'));
    add_filter('gettext', array($cus365dFlatDesign, 'modify_post_labels'), 10, 3);
    add_action('admin_head', array($cus365dFlatDesign, 'change_admin_title'));
    add_filter('post_type_link', array($cus365dFlatDesign, 'change_design_link'), 10, 4);

    add_filter( 'redirect_post_location', array($cus365dFlatDesign, 'cus365_redirect_post_location' ) );

   // 'post_type_link', $post_link, $post, $leavename, $sample
}
