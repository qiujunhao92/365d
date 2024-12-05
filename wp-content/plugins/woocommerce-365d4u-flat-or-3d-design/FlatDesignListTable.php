<?php

if (!class_exists('Flat_Design_List_Table')) {
    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    class Flat_Design_List_Table extends WP_List_Table
    {

        function prepare_items()
        {
            $this->process_bulk_action();

            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();

            $this->_column_headers = array($columns, $hidden, $sortable);

            $this->items = $this->get_flat_design_data();

            $per_page = 10;
            $current_page = $this->get_pagenum();
            $total_items = count($this->items);

            $this->set_pagination_args(array(
                'total_items' => $total_items,
                'per_page' => $per_page
            ));

            $this->items = array_slice($this->items, (($current_page - 1) * $per_page), $per_page);
        }

        function process_bulk_action() {
            if ('delete' === $this->current_action()) {
                if (!empty($_POST['bulk-delete'])) {
                    $delete_ids = array_map('intval', $_POST['bulk-delete']);
                    foreach ($delete_ids as $id) {
                        wp_delete_post($id, true);
                    }
                    echo '<div id="message" class="updated notice is-dismissible"><p>' . count($delete_ids) . ' Flat Designs deleted.</p></div>';
                }
            }
        }

        protected function extra_tablenav($which)
        {
            if ($which == "top") {
                $status_filter = $_REQUEST['feedback_filter'] ?? '';
                ?>
                <div class="alignleft actions">
                    <input type="hidden" name="page" value="flat-design-list" />
                    <label for="search_filter" class="screen-reader-text" style="width: 350px;"><?php _e('Search', 'textdomain'); ?></label>
                    <input type="text" id="search_filter" name="search_filter" placeholder="Order ID or Name"
                           value="<?php echo esc_attr(isset($_REQUEST['search_filter']) ? $_REQUEST['search_filter'] : ''); ?>" />
                    <select name="feedback_filter">
                        <option value=""><?php _e('All Statuses', 'textdomain'); ?></option>
                        <option value="Pending" <?php selected($status_filter, 'Pending'); ?>>Pending</option>
                        <option value="Approved" <?php selected($status_filter, 'Approved'); ?>>Approved</option>
                        <option value="Rejected" <?php selected($status_filter, 'Rejected'); ?>>Rejected</option>
                    </select>
                    <input type="submit" id="post-query-submit" class="button" value="<?php _e('Filter', 'textdomain'); ?>">
                </div>
                <?php
            }
        }

        function get_columns()
        {
            return array(
                'cb'            => '<input type="checkbox" />',
                'title'         => 'Title',
                'flat_media'    => 'Flat Design Media',
                '3d_media'      => '3D Design Media',
                'production_media' => 'Production Media',
                'details'        => 'Details',
            );
        }

        function get_sortable_columns()
        {
            return array(
                'title' => array('title', true),
                'date'  => array('date', true),
            );
        }

        function column_cb($item)
        {
            return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->ID);
        }

        function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'title':
                    $postId = $item->ID;
                    $url_link = get_edit_post_link($postId);
                    if (!empty($_GET['order_filter'])) {
                        $url_link .= "&order_filter=" . $_GET['order_filter'];
                    }
                    $duplicate_url = add_query_arg(
                        array(
                            'action' => 'duplicate',
                            'post' => $postId,
                        ),
                        admin_url('admin.php?page=flat-design-list')
                    );
                    $encryptId  =  EncryptData365d4u::encrypt($postId);
                    $design_media_url = site_url('/design_media/' . $encryptId . '/');
                    $actions = array(
                        'edit' => sprintf('<a href="%s">Edit</a>', $url_link),
                        'duplicate' => sprintf('<a href="%s">Duplicate</a>', esc_url($duplicate_url)),
                        'view' => sprintf('<a href="%s" target="_blank">View</a>', $design_media_url),
                    );
                    $frontendUrl = site_url('/design_media/' . $encryptId . '/');
                    return sprintf('%1$s %2$s', $item->post_title, $this->row_actions($actions)) . '<br>' . 'View Url: ' . $frontendUrl;
                case 'flat_media':
                    return $this->get_flat_media_column($item->ID);
                case '3d_media':
                    return $this->get_3d_media_column($item->ID);
                case 'production_media':
                    return $this->get_production_media_column($item->ID);
                case 'details':
                    $orders = $this->get_orders_column($item->ID);
                    $date = $item->post_date;
                    $post_status = $item->post_status;
                    $feedback = $this->get_customer_feedback_column($item->ID);
                  //  $feedback = get_post_meta($item->ID, 'customer_feedback', true) ?: 'No Feedback';
                    return sprintf(
                        '<div>-----------<br><span style="color:red;">%s</span><br>-----------<div>%s<br>-----------<br><small><b>Created:</b>%s</small><br>-----------<br><b>Customer Feedback:</b><br> %s<br>-----------</div>',
                        $post_status,
                        $orders,
                        $date,
                       $feedback
                    );

                case 'customer_feedback':
                    return $this->get_customer_feedback_column($item->ID);
                case 'date':
                    return $item->post_date;
                default:
                    return print_r($item, true);
            }
        }
        function get_customer_feedback_column($post_id) {
            $feedback = get_post_meta($post_id, 'customer_feedback', true);
            $feedback = is_array($feedback) ? $feedback : ['status' => 'Pending', 'comment' => '' , 'date' => ''];
            $feedback_status = $feedback['status'] ?? '';
            $feedback_comment = $feedback['comment'] ?? '';
            $feedback_date = $feedback['date'] ?? '';
            return "<b>Status:</b> {$feedback_status} <br> <b>Comment: </b><br>{$feedback_comment}<b>FeedbackTime: </b><br>{$feedback_date}";
        }
        function get_flat_media_column($post_id)
        {
            $flat_images = get_post_meta($post_id, 'flat_design_images', true);
            $flat_videos = get_post_meta($post_id, 'flat_design_videos', true);
            return $this->format_media($flat_images, $flat_videos);
        }

        function get_3d_media_column($post_id)
        {
            $three_d_images = get_post_meta($post_id, '3d_design_images', true);
            $three_d_videos = get_post_meta($post_id, '3d_design_videos', true);
            return $this->format_media($three_d_images, $three_d_videos);
        }

        function get_production_media_column($post_id)
        {
            $prod_images = get_post_meta($post_id, 'production_images', true);
            $prod_videos = get_post_meta($post_id, 'production_videos', true);
            return $this->format_media($prod_images, $prod_videos);
        }

        private function format_media($images, $videos)
        {
            $output = '';

            if (!empty($images)) {
                foreach ($images as $image_id) {
                    $image_url = wp_get_attachment_image_src($image_id, 'thumbnail');
                    if ($image_url) {
                        $output .= '<img src="' . esc_url($image_url[0]) . '" width="100" height="100" style="margin-right: 10px;">';
                    }
                }
            }

            if (!empty($videos)) {
                foreach ($videos as $video_id) {
                    $video_url = wp_get_attachment_url($video_id);
                    if ($video_url) {
                        $output .= '<video width="150" height="100" controls><source src="' . esc_url($video_url) . '" type="video/mp4"></video>';
                    }
                }
            }

            return $output ?: 'No media available';
        }

        function get_orders_column($post_id)
        {
            $orders = get_post_meta($post_id, 'flat_design_orders', true);
            if (!empty($orders)) {
                $orders = is_string($orders) ? explode(',', $orders) : $orders;
                $order_links = array();
                foreach ($orders as $order_id) {
                    $order_id = trim($order_id, '#');
                    $order_links[] = sprintf('<a href="%s">#%s</a>', get_edit_post_link($order_id), $order_id);
                }
                return implode(', ', $order_links);
            }
            return 'No orders associated';
        }

        function get_flat_design_data()
        {
            global $wpdb;
            $query = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'flat_design'";

            // 获取状态过滤器参数（默认为 'all'）
            $status_filter = isset($_REQUEST['status_filter']) ? sanitize_text_field($_REQUEST['status_filter']) : 'publish';
            // 根据状态过滤条件动态构建查询
            if ($status_filter === 'all') {
                // 如果状态为 'all'，则包含所有状态
            } else {
                // 否则，筛选指定状态
                $query .= $wpdb->prepare(" AND post_status = %s", $status_filter);
            }
            $search_filter = isset($_REQUEST['search_filter']) ? sanitize_text_field($_REQUEST['search_filter']) : '';
            if (!empty($search_filter)) {
                if (is_numeric($search_filter)) {
                    $query .= $wpdb->prepare(
                        " AND ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'flat_design_orders' AND meta_value LIKE %s)",
                        '%' . $search_filter . '%'
                    );
                } else {
                    $decryptKey = EncryptData365d4u::decrypt($search_filter);
                    if (!empty($decryptKey)) {
                        $query .= ' AND (post_title LIKE "%' . $search_filter . '%" OR ID="' . $decryptKey . '")'; ;
                    } else {
                        $query .= $wpdb->prepare(" AND post_title LIKE %s", '%' . $search_filter . '%');
                    }
                }
            }
            $feedback_filter = isset($_REQUEST['feedback_filter']) ? sanitize_text_field($_REQUEST['feedback_filter']) : '';
            if (!empty($feedback_filter)) {
                $query .= $wpdb->prepare(
                    " AND ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'customer_feedback' AND meta_value LIKE %s)",
                    '%' . $feedback_filter . '%'
                );
            }

            if (!empty($_GET['order_filter'])) {
                $query .= $wpdb->prepare(
                    " AND ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'flat_design_orders' AND meta_value LIKE %s)",
                    '%#' . $_GET['order_filter'] . '#%'
                );
            }
            $query .= ' order by ID DESC';
            return $wpdb->get_results($query);
        }

        function get_bulk_actions()
        {
            return array(
                'delete' => 'Delete'
            );
        }

        private function get_view_link( string $slug, string $name, int $count, bool $current ): string {
            $base_url =  admin_url('admin.php?page=flat-design-list');
            $url      = esc_url( add_query_arg( 'status_filter', $slug, $base_url ) );
            $name     = esc_html( $name );
            $count    = number_format_i18n( $count );
            $class    = $current ? 'class="current"' : '';

            return "<a href='$url' $class>$name <span class='count'>($count)</span></a>";
        }
        public function get_views() {
            $view_links = array();
            $current     = $_REQUEST['status_filter'] ?? 'publish';
            global $wpdb;
            $res = $wpdb->get_results(
               "SELECT count(1) as total_cnt, sum(if(post_status='publish', 1, 0)) AS publish_cnt, sum(if(post_status='draft', 1, 0)) AS draft_cnt FROM wp_posts WHERE post_type = 'flat_design'",  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                 ARRAY_A
            );
            !$res && $res = [];

            $publishCnt = $res[0]['publish_cnt'] ?? 0;
            $draftCnt = $res[0]['draft_cnt'] ?? 0;
            $all_count =  $res[0]['total_cnt'] ?? 0;

            $view_links['public'] = $this->get_view_link( 'publish', __( 'Publish', 'woocommerce' ), $publishCnt, '' === $current || 'publish' === $current );
            $view_links['draft'] = $this->get_view_link( 'draft', __( 'Draft', 'woocommerce' ), $draftCnt, 'draft' === $current );
            $view_links['all'] = $this->get_view_link( 'all', __( 'All', 'woocommerce' ), $all_count,  'all' === $current );
            return $view_links;
        }

    }
}
