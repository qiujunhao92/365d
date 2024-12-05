<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Custom_Comments_List_Table extends WP_List_Table {
    function __construct() {
        parent::__construct([
            'singular' => 'comment',
            'plural'   => 'comments',
            'ajax'     => false
        ]);
    }

    function get_columns() {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'id' => 'id',
            'page'  => 'Page',
            'source'  => 'Source',
            'email'  => 'Email',
            'comment' => 'Comment',
            'state'    => 'State',
            'date'    => 'Date',
            'actions' => 'Actions'
        ];
        return $columns;
    }

    function column_actions($item) {
        $hide_url = admin_url('admin-post.php?action=hide_gal_comment&id=' . $item->id);
        $show_url = admin_url('admin-post.php?action=show_gal_comment&id=' . $item->id);
        $delete_url = admin_url('admin-post.php?action=delete_gal_comment&id=' . $item->id);

        $actions = [
            'hide' => sprintf('<a href="%s">Hidden</a>', $hide_url),
            'show' => sprintf('<a href="%s">Publish</a>', $show_url),
            'delete' => sprintf('<a href="%s">Delete</a>', $delete_url)
        ];
        $itemState = $this->column_state($item);
        if ($itemState == 'Publish') {
            unset($actions['show']);
        } else {
            unset($actions['hide']);
        }
        return $this->row_actions($actions);
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="comment[]" value="%s" />', $item->id);
    }
    function column_id($item) {
        return $item->id;
    }

    function column_source($item) {
        if ($item->source_url) {
            $lowerSource = strtolower($item->source_url);
            if (!strstr($lowerSource, '.mp4') && !strstr($lowerSource, '.mov')) {
                return "<a target='_blank' href='{$item->source_url}'><img alt='" . $item->source_url . "' src='" . $item->source_url . "' style='width:100px;height:100px' /></a>";
            } else {
                return "<a target='_blank' href='{$item->source_url}'><video controls src='" . $item->source_url . "' style='width:100px;height:100px'></video></a>";
            }
        }
        return $item->source_url;
    }
    function column_page($item) {
        return $item->page_url;
    }

    function column_email($item) {
        return $item->email;
    }

    function column_state($item) {
        $arr = ['Publish', 'Hidden'];
        return $arr[$item->auth_type] ?? $arr[0] ;
    }


    function column_comment($item) {
        return $item->comment;
    }

    function column_date($item) {
        return $item->created;
    }

    function get_bulk_actions() {
        $actions = [
            'delete'  => 'Delete'
        ];
        return $actions;
    }

    function extra_tablenav($which) {
        if ($which == "top") {
            global $wpdb;
            $cntSql = "select count(1) as total_cnt,SUM(IF(auth_type = '0', 1, 0)) as publishCnt,SUM(IF(auth_type = '1', 1, 0)) as hideCnt from `{$wpdb -> prefix}galleryvotes` where type=1";
            $row = $wpdb->get_row($cntSql, ARRAY_A);
            $totalCnt = $row['total_cnt'] ?? 0;
            $publishCnt = $row['publishCnt'] ?? 0;
            $hideCnt = $row['hideCnt'] ?? 0;
            $status = isset($_REQUEST['auth_type']) ? $_REQUEST['auth_type'] : '';
            $all = '<a  class="cusGalLbl" href="/wp-admin/admin.php?page=gallery-comments">All(' . $totalCnt . ')</a>';
            $publish = '<a href="/wp-admin/admin.php?page=gallery-comments&auth_type=0"  class="cusGalLbl cuslblpub">Published(' . $publishCnt . ')</a>';
            $hidden ='<a href="/wp-admin/admin.php?page=gallery-comments&auth_type=1"  class="cusGalLbl cuslblhidden">Hidden(' . $hideCnt . ')</a>';
            switch ($status) {
                case '0':
                    $publish = "<span class='cusGalLbl cuslblpub'>Published(" . $publishCnt . ")</span>";
                    break;
                case '1':
                    $hidden = "<span  class='cusGalLbl cuslblhidden'>Hidden(" . $hideCnt . ")</span>";
                    break;
               default:
                    $all = "<span  class='cusGalLbl'>All(" . $totalCnt . ")</span>";
                    break;
            }
            echo '<div class="alignleft actions">'. $all . $publish . $hidden . '</div>';
            echo <<<STYLE
  <style>
      .cuslblpub, .cuslblhidden {
         margin-left:10px;
      }
  </style>
STYLE;

        }
    }

    function bulkExec($wpdb)
    {
        if ('delete' === $this->current_action()) {
            $comment_ids = $_POST['comment'] ?? [];

            if (!empty($comment_ids)) {
               $sql = "DELETE FROM `{$wpdb -> prefix}galleryvotes` WHERE id in (" . implode(',', $comment_ids) . ")";
                $wpdb->query($sql);
            }
        }
    }

    function prepare_items() {
        global $wpdb;
        $per_page = 10;
        $current_page = $this->get_pagenum();

        $offset = ($current_page - 1) * $per_page;

        $this->bulkExec($wpdb);

        $where = ' where type=1';
        if (isset($_REQUEST['auth_type']) && $_REQUEST['auth_type'] !== '') {
            $where .= ' and auth_type=' . $_REQUEST['auth_type'];
        }
        $sql = "select * from `{$wpdb -> prefix}galleryvotes`{$where} order by `id` DESC limit {$offset},{$per_page}";

        $this->items = $wpdb->get_results($sql);

        $cntSql = "select count(1) as cnt from `{$wpdb -> prefix}galleryvotes`{$where}";
        $total_items = $wpdb->get_var($cntSql);

        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);
    }

    function display() {
        $this->prepare_items();
        echo '<form method="post">';
        echo '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '" />';
        parent::display();
        echo '</form>';
    }
}

add_action('admin_menu', 'custom_comments_menu');

function custom_comments_menu() {
    add_submenu_page(
         'edit-comments.php',
        'Gallery Comments',       // 页面标题
        'Gallery Comments',       // 菜单标题
        'manage_options',        // 权限
        'gallery-comments',       // 菜单别名
        'gallery_comments_page',  // 回调函数
             6                         // 位置
    );
}

function gallery_comments_page() {
    echo '<div class="wrap">';
    echo '<h1>Custom Comments</h1>';

    $comments_table = new Custom_Comments_List_Table();
    $comments_table->display();
    echo '</div>';
}

function load_custom_wp_admin_style($hook) {
    if ($hook != 'toplevel_page_custom-comments') {
        return;
    }
    wp_enqueue_style('wp-admin');
}
add_action('admin_enqueue_scripts', 'load_custom_wp_admin_style');

add_action('admin_post_hide_gal_comment', 'cus_hide_gal_comment');
add_action('admin_post_show_gal_comment', 'cus_show_gal_comment');
add_action('admin_post_delete_gal_comment', 'cus_del_gal_comment');

function cus_hide_gal_comment() {
    if (isset($_GET['id'])) {
        global $wpdb;
        $comment_id = intval($_GET['id']);
        if ( ! $wpdb->update( "{$wpdb -> prefix}galleryvotes", array( 'auth_type' => 1 ), array( 'id' => $comment_id ) ) ) {
             return false;
        }
    }
    wp_redirect(admin_url('admin.php?page=gallery-comments'));
    exit;
}
function cus_show_gal_comment() {
    if (isset($_GET['id'])) {
        global $wpdb;
        $comment_id = intval($_GET['id']);
        if ( ! $wpdb->update("{$wpdb -> prefix}galleryvotes", array( 'auth_type' => 0 ), array( 'id' => $comment_id ) ) ) {
            return false;
        }
    }
    wp_redirect(admin_url('admin.php?page=gallery-comments'));
    exit;
}
function cus_del_gal_comment() {
    if (isset($_GET['id'])) {
        global $wpdb;
        $comment_id = intval($_GET['id']);
        if ( ! $wpdb->delete( "{$wpdb -> prefix}galleryvotes", array( 'id' => $comment_id ) ) ) {
            return false;
        }
    }
    wp_redirect(admin_url('admin.php?page=gallery-comments'));
    exit;
}

function custom_mime_types($mimes) {
    $mimes['mp4'] = 'video/mp4';
    $mimes['mov'] = 'video/quicktime';
    $mimes['avi'] = 'video/x-msvideo';
    return $mimes;
}
add_filter('upload_mimes', 'custom_mime_types');

function custom_gallery_shortcode($attr) {
    $post = get_post();
    static $instance = 0;
    $instance++;

    if (!empty($attr['ids'])) {
        if (empty($attr['orderby'])) {
            $attr['orderby'] = 'post__in';
        }
        $attr['include'] = $attr['ids'];
    }

    $output = apply_filters('post_gallery', '', $attr);
    if ($output != '') {
        return $output;
    }

    if (isset($attr['orderby'])) {
        $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
        if (!$attr['orderby']) {
            unset($attr['orderby']);
        }
    }

    $html5 = current_theme_supports('html5', 'gallery');
    extract(shortcode_atts(array(
        'order'      => 'ASC',
        'orderby'    => 'menu_order ID',
        'id'         => $post ? $post->ID : 0,
        'itemtag'    => $html5 ? 'figure' : 'dl',
        'icontag'    => $html5 ? 'div'    : 'dt',
        'captiontag' => $html5 ? 'figcaption' : 'dd',
        'columns'    => 3,
        'size'       => 'thumbnail',
        'include'    => '',
        'exclude'    => '',
        'link'       => ''
    ), $attr, 'gallery'));

    $id = intval($id);
    if ('RAND' == $order) {
        $orderby = 'none';
    }

    if (!empty($include)) {
        $_attachments = get_posts(array(
            'include'        => $include,
            'post_status'    => 'inherit',
            'post_type'      => 'attachment',
            'post_mime_type' => 'image,video',
            'order'          => $order,
            'orderby'        => $orderby
        ));

        $attachments = array();
        foreach ($_attachments as $key => $val) {
            $attachments[$val->ID] = $_attachments[$key];
        }
    } elseif (!empty($exclude)) {
        $attachments = get_children(array(
            'post_parent'    => $id,
            'exclude'        => $exclude,
            'post_status'    => 'inherit',
            'post_type'      => 'attachment',
            'post_mime_type' => 'image,video',
            'order'          => $order,
            'orderby'        => $orderby
        ));
    } else {
        $attachments = get_children(array(
            'post_parent'    => $id,
            'post_status'    => 'inherit',
            'post_type'      => 'attachment',
            'post_mime_type' => 'image,video',
            'order'          => $order,
            'orderby'        => $orderby
        ));
    }

    if (empty($attachments)) {
        return '';
    }

    if (is_feed()) {
        $output = "\n";
        foreach ($attachments as $att_id => $attachment) {
            $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
        }
        return $output;
    }

    $itemtag = tag_escape($itemtag);
    $captiontag = tag_escape($captiontag);
    $icontag = tag_escape($icontag);
    $valid_tags = wp_kses_allowed_html('post');
    if (!isset($valid_tags[$itemtag])) {
        $itemtag = 'dl';
    }
    if (!isset($valid_tags[$captiontag])) {
        $captiontag = 'dd';
    }
    if (!isset($valid_tags[$icontag])) {
        $icontag = 'dt';
    }

    $columns = intval($columns);
    $itemwidth = $columns > 0 ? floor(100 / $columns) : 100;
    $float = is_rtl() ? 'right' : 'left';

    $selector = "gallery-{$instance}";

    $gallery_style = '';
    $size_class = sanitize_html_class($size);
    $gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

    $output = apply_filters('gallery_style', $gallery_style . $gallery_div);

    $i = 0;
    foreach ($attachments as $id => $attachment) {
        $attr = (trim($attachment->post_excerpt)) ? array('aria-describedby' => "$selector-$id") : '';
        if ($attachment->post_mime_type == 'video') {
            $output .= "<{$itemtag} class='gallery-item'>";
            $output .= "
                <{$icontag} class='gallery-icon'>
                    " . wp_video_shortcode(array('src' => wp_get_attachment_url($id))) . "
                </{$icontag}>";
            if ($captiontag && trim($attachment->post_excerpt)) {
                $output .= "
                    <{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
                    " . wptexturize($attachment->post_excerpt) . "
                    </{$captiontag}>";
            }
            $output .= "</{$itemtag}>";
        } else {
            $output .= "<{$itemtag} class='gallery-item'>";
            $output .= "
                <{$icontag} class='gallery-icon'>
                    " . wp_get_attachment_link($id, $size, false, false, false, $attr) . "
                </{$icontag}>";
            if ($captiontag && trim($attachment->post_excerpt)) {
                $output .= "
                    <{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
                    " . wptexturize($attachment->post_excerpt) . "
                    </{$captiontag}>";
            }
            $output .= "</{$itemtag}>";
        }
        if ($columns > 0 && ++$i % $columns == 0) {
            $output .= '<br style="clear: both" />';
        }
    }

    $output .= "<br style='clear: both;' /></div>\n";

    return $output;
}
remove_shortcode('gallery');
add_shortcode('gallery', 'custom_gallery_shortcode');