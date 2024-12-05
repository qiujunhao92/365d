<?php
if (!class_exists('Blog365dManager')) {
    class Blog365dManager
    {
        function custom_365d4u_changenavmenu($block_content, $parsed_block, $obj)
        {
            if ($obj->attributes && $obj->attributes['label'] && strtoupper($obj->attributes['label']) == 'BLOGS') {
                $categories = get_categories();
                $sCategoryLink = '';
                foreach ($categories as $category) {
                    $sCategoryLink .= '<li class=" wp-block-navigation-item wp-block-navigation-link">'
                        . '<a class="wp-block-navigation-item__content" href="'
                        . get_category_link($category->term_id) . '"><span class="wp-block-navigation-item__label">' . $category->name
                        . '</span></a></li>';
                }
                $block_content = str_replace('</ul></li>', $sCategoryLink . '</ul></li>', $block_content);
            }
            return $block_content;
        }

        function custom_365d4u_posts_where($where, $obj)
        {
            // AND (
            //  wp_term_relationships.term_taxonomy_id IN (77)
            //) AND ((wp_posts.post_type = 'post' AND (wp_posts.post_status = 'publish'
            //OR wp_posts.post_status = 'private')))
            if ($obj->query && isset($obj->query['category_name']) && $obj->query['category_name'] == 'allblogs') {
                $where = preg_replace('/wp_term_relationships.term_taxonomy_id IN \\(\d+\\)/ius', '1=1', $where);
            }
            return $where;
        }
    }
}
$blogManager = new Blog365dManager();
add_filter('render_block_core/navigation-submenu',  array($blogManager,'custom_365d4u_changenavmenu'), 10, 3);

add_filter('posts_where_request',  array($blogManager,'custom_365d4u_posts_where'), 10, 2);
