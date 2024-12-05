<?php
if (!class_exists('CustomThemeFeatureHelper')) {
    class CustomThemeFeatureHelper
    {
        function custom_header_script()
        {
            if (!is_admin()) {
                list($customCategoryMenu, $standardCategoryMenu) = $this->getBaseMenuData();
                $jsonCustomCategoryMenu = json_encode($customCategoryMenu);
                $jsonStandardCategoryMenu = json_encode($standardCategoryMenu);
                if (is_front_page()) {
                    $normalObjList = [];
                    $bigObj = null;
                    foreach($customCategoryMenu as $category) {
                        if ($category['sid'] == 1) {
                            $bigObj = [
                                'index' => 1,
                                'title' => $category['name'] ?? '',
                                'image' => $category['thumbnail_url'] ?? '',
                                'url' => $category['url'] ?? '',
                            ];
                        } elseif(!empty($category['thumbnail_url'])) {
                            $normalObjList[] = [
                                'index' => $category['sid'],
                                'title' => $category['name'] ?? '',
                                'image' => $category['thumbnail_url'],
                                'url' => $category['url'] ?? '',
                            ];
                        }
                    }

                    $mainHomeFeature = [
                        'banner' => $this->getHomeBanner(),
                        'custom' => [
                            'title' => 'CUSTOM',
                            'view_all' => 'VIEW ALL',
                            'more' => 'MORE',
                            'link' => '/product-category/custom-all/',
                        ],
                        'collection' => [
                            'title' => 'COLLECTION',
                            'view_all' => 'VIEW ALL',
                            'more' => 'MORE',
                            'link' => '/product-category/standard-all/',
                        ],
                        'cusMobile' => [
                             'big' => $bigObj,
                             'normal' => $normalObjList,
                        ]
                    ];
                    list($customProducts, $collectionProducts) = $this->getHomeBaseTabData();
                    $jsonCustomProducts = json_encode($customProducts);
                    $jsonCollectionProducts = json_encode($collectionProducts);
                    $jsonMainHomeFeature = json_encode($mainHomeFeature);
                    echo <<<SCRIPT
     <script type="text/javascript">        
           var homepageObj = {
               homePageCustomList : {$jsonCustomProducts},
               homePageCollectionList : {$jsonCollectionProducts},
               customMenu : {$jsonCustomCategoryMenu},
               collectionMenu : {$jsonStandardCategoryMenu},
               mainHomeFeature : {$jsonMainHomeFeature}
           }
           console.log(homepageObj);
     </script>
SCRIPT;
                } else {
                    $jsonSubCategoryList = '{}';
                    $jsonProdObj = '{}';
                    if (is_product_category()) {
                        echo <<<STYLE
<style type="text/css">    
     .custom-plp  .wp-block-woocommerce-product-price{
        display:none;
     }
      .custom-plp  .wc-block-components-product-button{
        display:none;
     }
     .standard-plp .wc-block-components-product-button{
         display:none;
     }
      .standard-plp .wp-block-post.product{
       padding:0;
     }
     .custom-plp .wp-block-post.product{
       padding:0;
     }
      .tax-product_cat .filterList{
         display:none;
     }
     .tax-product_cat .wp-block-query-pagination{
        padding-top: 30px !important;
     }
</style>
STYLE;
                        // 获取当前分类对象
                        $current_category = get_queried_object();
                        // 确保当前分类存在
                        $subCategoryList = null;
                        $categoryBanner = null;
                        if ( $current_category && !is_wp_error($current_category)) {
                            if ($current_category->slug == 'custom-all') {
                                $subCategoryList = $this->getChildCatList($customCategoryMenu, $current_category);
                                $categoryBanner = $this->getMainCategoryBanner('custom', $subCategoryList);
                            } elseif($current_category->slug == 'standard-all') {
                                $subCategoryList = $this->getChildCatList($customCategoryMenu, $current_category);
                                $categoryBanner = $this->getMainCategoryBanner('collection', $subCategoryList);
                            }
                        }
                        if ($categoryBanner) {
                            $jsonSubCategoryList =json_encode($categoryBanner);
                        }
                    } else if (is_singular('product')) {
                        global $product;
                        if ($product) {
                            $galleryImages =[];
                            $thumbnail_id = $product->get_image_id(); // 获取商品的缩略图 ID
                            $thumbnail_url = wp_get_attachment_url( $thumbnail_id );
                            if (!empty($thumbnail_url)) {
                                $galleryImages[] =   [
                                    'type' => 'image',
                                    'url' => $thumbnail_url
                                ];
                            }

                            // 获取商品的图库图片 ID 列表
                            $gallery_image_ids = $product->get_gallery_image_ids();
                            if ( $gallery_image_ids ) {
                                foreach ( $gallery_image_ids as $image_id ) {
                                    // 获取图片的 URL
                                    $image_url = wp_get_attachment_url( $image_id );
                                    if (!in_array($image_url, $galleryImages)) {
                                        $galleryImages[] = [
                                            'type' => 'image',
                                            'url' => $image_url
                                        ];
                                    }
                                }
                            }
                            $product_videos = get_post_meta($product->get_id(), '_product_videos', true);
                            $product_videos = $product_videos ? json_decode($product_videos, true) : [];
                            foreach ($product_videos as $video) {
                                if (empty($video['url'])) {
                                    continue;
                                }
                                if (!empty($video['thumbnail'])) {
                                    $video_thumbnail_url = $video['thumbnail']; // 获取视频的封面图 URL
                                } else {
                                    $video_thumbnail_url = 'https://img.365d4u.com/2024/12/video.png'; // 如果没有封面图，返回空值
                                }
                                $galleryImages[] = [
                                    'type' => 'video',
                                    'url' => $video['url'],
                                    'poster' => $video_thumbnail_url
                                ];
                            }
                            $prodObj = [
                                'title' => $product->get_title(),
                                'desc' => 'Our designers can customize any type as your idea.You can also choose to customize a variety of colors, any size and material.You can also choose to customize a variety of colors, any size and material.',
                                'images' => $galleryImages,
                                'button_text' => "LET'S Chat",
                                'button_link' => "https://www.instagram.com/custom365d/"
                            ];
                            $jsonProdObj = json_encode($prodObj);
                        }
                    }

                    echo <<<SCRIPT
               <script type="text/javascript">
                     var homepageObj = { 
                       customMenu : {$jsonCustomCategoryMenu},
                       collectionMenu : {$jsonStandardCategoryMenu},
                       subCategoryList : {$jsonSubCategoryList},
                       product : $jsonProdObj
                   }
               </script>
SCRIPT;
              }
            }

        }

        /**
         * Get Main Category Banner
         *
         * @param $cat
         * @param $subCategoryList
         * @return array
         */
        private function getMainCategoryBanner($cat, $subCategoryList): array
        {
            $option_name_cat = 'banner_images_with_links_' . $cat . '_cat';
            $cat_banners = get_option($option_name_cat, array());
            $pc_cat_banner = $cat_banners['pc'] ?? [];
            $mobile_cat_banner = $cat_banners['mobile'] ?? [];
            return  [
                'pc_subBanner' => [
                    [
                        'title' => $pc_cat_banner['title'] ?? '',
                        'desc' => $pc_cat_banner['description'] ?? '',
                        'image' => $pc_cat_banner['image'] ?? '',
                        'url' => $pc_cat_banner['link'] ?? '',
                    ]
                ],
                'mobile_subBanner' => [
                    [
                        'title' => $mobile_cat_banner['title'] ?? '',
                        'desc' => $mobile_cat_banner['description'] ?? '',
                        'image' => $mobile_cat_banner['image'] ?? '',
                        'url' => $mobile_cat_banner['link'] ?? '',
                    ]
                ],
                'categories' => $subCategoryList
            ];
        }

        private function getMenuCacheKey()
        {
            return 'diff_category_menus';
        }

        private function getTabCacheKey()
        {
            return 'diff_tab_prods_1';
        }

        private function getBaseMenuData()
        {
            $key =  $this->getMenuCacheKey();
            $cached_data = wp_cache_get($key, 'ahome');
            if (empty($cached_data)) {
                $customCategoryMenu = $this->get_categories('custom');
                $standardCategoryMenu = $this->get_categories('standard');
                if (!empty($customCategoryMenu) && !empty($standardCategoryMenu)) {
                    $cached_data = [
                        'custom' => $customCategoryMenu,
                        'standard' => $standardCategoryMenu
                    ];
                    wp_cache_set($key, $cached_data, 'ahome', 3600);
                }
            } else {
                $customCategoryMenu = $cached_data['custom'];
                $standardCategoryMenu = $cached_data['standard'];
            }
            return [$customCategoryMenu, $standardCategoryMenu];
        }

        protected function getHomeBaseTabData()
        {
            $key =  $this->getTabCacheKey();
            $cached_data = wp_cache_get($key, 'ahome');
            if (!$cached_data) {
                $customProducts = $this->getTabProductList('custom');
                $collectionProducts = $this->getTabProductList('collection');
                if(!empty($customProducts) && !empty($collectionProducts)){
                    $cached_data = [
                        'custom' => $customProducts,
                        'standard' => $collectionProducts
                    ];
                    wp_cache_set($key, $cached_data, 'ahome', 2* 3600);
                }
            } else {
                $customProducts = $cached_data['custom'];
                $collectionProducts = $cached_data['standard'];
            }
            return [$customProducts, $collectionProducts];
        }

        protected function delTabCache()
        {
            $tabCacheKey = $this->getTabCacheKey();
            wp_cache_delete($tabCacheKey, 'ahome');
        }
        protected function delCategoryMenuCache()
        {
            $tabCacheKey = $this->getMenuCacheKey();
            wp_cache_delete($tabCacheKey, 'ahome');
            $this->getBaseMenuData();
        }
        function customCatChange($term_id, $tt_id, $taxonomy) {
            if ($taxonomy === 'product_cat' && is_admin()) {
                $term = get_term($term_id);
                if (str_contains($term->slug, 'custom') || str_contains($term->slug, 'stand')) {
                   // error_log("分类 {$term_id} 修改。");
                    $this->delCategoryMenuCache();
                }
            }
        }
        protected function getChildCatList($allSubCategoryMenu, $current_category)
        {
            $subCategoryList = [];
            foreach ($allSubCategoryMenu as $category) {
                if ($category['slug'] != $current_category->slug) {
                    $args = array(
                        'category' => $category['slug'], // 替换为你的分类slug
                        'limit'    => 8, // 获取所有产品
                    );
                    $products = wc_get_products($args);
                    $childProducts = [];
                    foreach ($products as $product) {
                        $image_id = $product->get_image_id(); // 获取主图ID
                        $productTitle = $product->get_title();
                        if ($image_id) {
                            $image_url = wp_get_attachment_image_url($image_id,  'full'); // 获取图片URL
                        } else {
                            $image_url = '';
                        }
                        $link = $product->get_permalink();
                        $childProducts[] = [
                            'title' => $productTitle,
                            'image' => $image_url,
                            'url' => $link,
                        ];
                    }
                    $subCategoryList[] = [
                        'name' => $category['name'] ?? '',
                        'image' => $category['thumbnail_url'] ?? '',
                        'url' => $category['url'],
                        'products' => $childProducts,
                    ];
                }
            }
            return $subCategoryList;
        }

        function getHomeBanner()
        {
            $pcBanner = get_option('banner_images_with_links_pc', array());
            usort($pcBanner, function ($a, $b) {
                return $a['order'] <=> $b['order'];
            });
            $mobileBanner = get_option('banner_images_with_links_mobile', array());
            usort($mobileBanner, function ($a, $b) {
                return $a['order'] <=> $b['order'];
            });
            if ($pcBanner && !$mobileBanner) {
                $mobileBanner = $pcBanner;
            }
            return [
                'pcBanner' => $pcBanner,
                'mobileBanner' => $mobileBanner
            ];
        }


        function get_categories($slugId) {
            $arrCategory = [];
            $args = array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false
            );
            $categories = get_terms($args);

            // 过滤包含 "-" 的分类
            $filtered_categories = array_filter($categories, function($category) use ($slugId) {
                return str_contains($category->slug, '-') && str_contains($category->slug, $slugId); // 包含 "-"
            });
            $keyIdArr = ['all', 'pendant', 'chain', 'ring', 'bracelet', 'earring', 'watch', 'box'];
            if (!empty($filtered_categories)) {
                foreach ($filtered_categories as $category) {
                    $category_link = get_term_link($category); // 获取分类链接
                    $endSlg = trim(strstr($category->slug, '-'), '-');
                    $searchId = array_search($endSlg, $keyIdArr);
                    $searchId = $searchId===false ? 1000 : $searchId;
                    $displayCatName = in_array($category->name, ['CUSTOM', 'COLLECTION']) ? 'ALL' : $category->name;
                    $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                    $thumbnail_url = '';
                    if ($thumbnail_id) {
                        $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'full');
                    }
                    $arrCategory[] = [
                        'sid' => $searchId,
                        'slug' => $category->slug,
                        'name' => $displayCatName,
                        'url' => $category_link,
                        'thumbnail_url' => $thumbnail_url
                    ];
                }
            }

            $idSort = array_column($arrCategory, 'sid');
            array_multisort($idSort, SORT_ASC, $arrCategory);
            return $arrCategory;
        }

        function get_global_attribute_values($taxonomy): array
        {
            if (!taxonomy_exists($taxonomy)) {
                return []; // 如果 taxonomy 不存在，返回空数组
            }
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false, // 包括未使用的值
            ]);

            $attribute_values = [];
            foreach ($terms as $term) {
                $attribute_values[$term->slug] = [
                    'id' => $term->term_id,            // term ID
                    'name' => $term->name,            // term 名称
                    'slug' => $term->slug,            // term 别名（slug）
                    'description' => $term->description, // term 描述
                ];
            }
            return $attribute_values;
        }

        function getTabProductList($tabKey)
        {
            $globalColorList = $this->get_global_attribute_values('pa_color');
            $arrRet = [];
            $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'yikes_woo_products_tabs',
                        'value' => '"'. $tabKey . '"',
                        'compare' => 'LIKE'
                    )
                )
            );
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    // 获取产品信息
                    $product_id = get_the_ID();
                    $product = wc_get_product($product_id);
                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'full')[0] ?? '';
                    $gallery_image_ids = $product->get_gallery_image_ids();
                    $second_image_url = $image;
                    if (isset($gallery_image_ids[1])) { // 检查是否有第二张图片
                        $second_image_id = $gallery_image_ids[1];
                        $second_image_url = wp_get_attachment_image_src($second_image_id, 'full')[0] ?? '';
                    }
                    $product_type = $product->get_type(); // 获取产品类型
                    // 处理变体产品
                    $colorImages = [];
                    if ($product_type === 'variable') {
                        $variation_ids = $product->get_children(); // 获取所有变体的 ID
                        foreach ($variation_ids as $variation_id) {
                            $variation = wc_get_product($variation_id);
                            if ($variation) {
                                $variation_image =  wp_get_attachment_image_src($variation->get_image_id(), 'full')[0] ?? '';
                                // 获取变体属性
                                foreach ($variation->get_attributes() as $attribute_name => $attribute_value) {
                                    // 获取属性的完整名称和 ID
                                    $taxonomy = str_replace('attribute_', '', $attribute_name); // 去掉 "attribute_" 前缀
                                    if ($taxonomy == 'pa_color') {
                                        if (isset($globalColorList[$attribute_value]) && !empty($variation_image)){
                                            $colorImages[$attribute_value] = [
                                                'name' => $globalColorList[$attribute_value]['name'],
                                                'slug' => $attribute_value,
                                                'image' => $variation_image,
                                                'price' => $variation->get_price(),
                                                'color' => trim($globalColorList[$attribute_value]['description'])
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $arrRet[] = [
                        'currentImage' => $image,
                        'image' => $image,
                        'image1' => $second_image_url,
                        'product_type' => $product_type,
                        'title' => $product->get_title(),
                        'url' => $product->get_permalink(),
                        'price' => $product->get_price(),
                        'color_images' => array_values($colorImages)
                    ];
                }
                wp_reset_postdata(); // 重置查询
            }
            return $arrRet;
        }

        function custom_add_type_filter($arrList)
        {
            $arrList['tab_filter'] =  array( $this, 'render_products_feature_filter' );
            return $arrList;
        }

        /**
         * out put feature product
         * @return void
         */
        function render_products_feature_filter()
        {
            $yikes_custom_tab_data = get_option( 'yikes_woo_reusable_products_tabs', array() );
            $current_tab_filter = isset( $_REQUEST['tab_filter'] ) ? wc_clean( wp_unslash( $_REQUEST['tab_filter'] ) ) : ''; // WPCS: input var ok, sanitization ok.

            $output = '<select name="tab_filter" id="tab_filter"><option value="">' . esc_html__( '[Home Page Tab]', 'woocommerce' ) . '</option>';

            foreach ( $yikes_custom_tab_data as $key => $tab_data ) {

                // Set variables before using them.
                $tab_title   =  $tab_data['tab_title'] ?? '';
                $tab_name  = $tab_data['tab_name']  ?? '';
                $output .= '<option ' . selected( $tab_name, $current_tab_filter, false ) . ' value="' . esc_attr( $tab_name ) . '">' . esc_html( $tab_title ) . '</option>';
            }

            $output .= '</select>';
            echo $output;  // WPCS: XSS ok.
        }

        function back_request($query_vars)
        {
            $postType = $query_vars['post_type'] ?? '';
            if (is_admin() && $postType == 'product') {
                foreach ($query_vars as $key => $value) {
                    if ($value == '') {
                        unset($query_vars[$key]);
                    } elseif (in_array($key, ['action', 'action2']) && $value == -1) {
                        unset($query_vars[$key]);
                    }
                }
            }
            return $query_vars;
        }

        function apply_custom_filter($query) {
            if (!is_admin() || !$query->is_main_query() || 'product' !== $query->get('post_type')) {
                return;
            }
            $tabFilter = $_GET['tab_filter'] ?? '';
            $yikes_custom_tab_data = get_option( 'yikes_woo_reusable_products_tabs', array() );
            $yikes_custom_tab_data =  array_column($yikes_custom_tab_data, 'tab_title', 'tab_name');
            $tabFilter = $yikes_custom_tab_data[$tabFilter] ?? '';
            if (!empty($tabFilter)) {
                $query->set('meta_query', array(
                    array(
                        'key' => 'yikes_woo_products_tabs',
                        'value'   => '"' . $tabFilter . '"',
                        'compare' => 'LIKE',
                    )
                ));
            }
        }

        function populate_custom_product_column($column, $post_id) {
            if ('cus_tab' === $column) {
                // 获取自定义数据，例如产品的某个meta字段
                $custom_data = get_post_meta($post_id, 'yikes_woo_products_tabs', true);
                $html = '';
                if (is_array($custom_data)) {
                    foreach ($custom_data as $custom_data_key => $custom_data_value) {
                        if ($custom_data_key >0) {
                            $html .= "<br>-----------------<br>";
                        }
                        $html .=  'Tab ' . ($custom_data_key+1) . ': ' . $custom_data_value['title'];

                    }
                }
                echo $html; // 输出自定义数据
            }
        }
        function add_product_tab_columns($columns) {
            $arrNewKey = array_keys($columns);
            $arrNewColumn = [];
            foreach ($arrNewKey as $key) {
                if ($key == 'featured') {
                    $arrNewColumn['cus_tab'] = __('HomePage Tabs'); // 列标题
                } elseif($columns[$key]!='Tags') {
                    $arrNewColumn[$key] = $columns[$key];
                }
            }
            return $arrNewColumn;
        }

        function enqueue_scripts_and_styles()
        {
            if (is_admin()) {
                $screen = get_current_screen();

                // 判断是否是编辑产品页面
                if ('product' === $screen->post_type && 'post' === $screen->base) {
                    // 这里是编辑产品页面的代码
                    echo <<<STYLE
       <style>
          #add_another_tab._yikes_wc_add_tabs{
            display: none;
         }
      </style>
STYLE;

                }
            }
        }

        private function updateProductTabMeta($product_id, $tab_title, $tab_content)
        {
            $tab_id = urldecode( sanitize_title( $tab_title) );
            $custom_data = get_post_meta($product_id, 'yikes_woo_products_tabs') ?? [];
            if (is_array($custom_data)) {
                $arrCol =  array_column($custom_data, 'id');
                if (in_array($tab_id, $arrCol)) {
                     return false; //exist
                }
            }
            // push the data to the array
            $custom_data[] = array( 'title' => $tab_title, 'id' => $tab_id, 'content' =>  $tab_content );
            update_post_meta($product_id, 'yikes_woo_products_tabs', $custom_data);
            return true;
        }

        function edit_product_bulk_action($actions)
        {
            if (is_admin()){
                if (isset($_GET['post_type']) && $_GET['post_type'] === 'product') {
                    $yikes_custom_tab_data = get_option( 'yikes_woo_reusable_products_tabs', array() );
                    foreach ($yikes_custom_tab_data as $tab){
                        $actions['set_tab_' . $tab['tab_name']] = __('Set Products To Tab "' . $tab['tab_title'] . '"');
                    }
                    $actions['clear_all_tabs'] = __('Clear Tabs');
                }
            }
           return $actions;
        }

        function handle_custom_bulk_action($redirect_to, $action, $post_ids) {
            if (strstr($action , 'set_tab_')) {
                $yikes_custom_tab_data = get_option( 'yikes_woo_reusable_products_tabs', array() );
                $yikes_custom_tab_data = array_column($yikes_custom_tab_data, null, 'tab_name');
                $curSelTabName = str_replace('set_tab_', '', $action);
                $curSelTab =   $yikes_custom_tab_data[$curSelTabName] ?? null;
                if ($curSelTab) {
                    foreach ($post_ids as $post_id) {
                        $this->updateProductTabMeta($post_id, $curSelTab['tab_title'], $curSelTab['tab_content']);
                    }
                    $redirect_to = add_query_arg('bulk_set_custom_meta', count($post_ids), $redirect_to);
                    $this->delTabCache();
                }  else {
                    $redirect_to = add_query_arg('bulk_set_custom_meta', 0, $redirect_to);
                }
            } else if($action=='clear_all_tabs') {
                foreach ($post_ids as $post_id) {
                    // 删除所有自定义过滤器，这里假设过滤器存储在一个特定的 meta_key 中
                    delete_post_meta($post_id, 'yikes_woo_products_tabs'); // 替换为你需要删除的 meta_key
                }
                $this->delTabCache();
                $redirect_to = add_query_arg('bulk_set_custom_meta', count($post_ids), $redirect_to);
            }
            return $redirect_to;
        }
        function custom_bulk_action_admin_notice() {
            if (!empty($_REQUEST['bulk_set_custom_meta'])) {
                $count = intval($_REQUEST['bulk_set_custom_meta']);
                echo '<div class="notice notice-success is-dismissible">';
                echo sprintf(__('Success to update %s products!'), $count);
                echo '</div>';
            }
        }

        function custom_body_class_for_plp( $classes ) {
            // 检查是否为产品分类页面或商店页面
            if ( is_product_category() || is_shop() ) {
                $current_category = get_queried_object();
                // 确保当前分类存在
                if ( $current_category && !is_wp_error($current_category) ) {
                    if (strstr($current_category->slug,'custom')) {
                        // 添加自定义类
                        $classes[] = 'custom-plp';
                    } elseif(strstr($current_category->slug,'stand')) {
                        $classes[] = 'standard-plp';
                    }
                }
            }
            if (!is_admin() && is_singular('page')) {
                if (!is_product_category()  && ! is_front_page() && ! is_post_type_archive('product') && ! is_product() && ! is_checkout() && ! is_cart() ) {
                    $classes[] = 'custom-linked-page';
                }
            }
            return $classes;
        }

        function del_cache_when_change($new_tab, $status)
        {
            $this->delTabCache();
        }

        function custom_del_feature_cache($cache_key)
        {
            switch ($cache_key){
                case 'tab':
                    $this->delTabCache();
                    break;
                case 'menu':
                    $this->delCategoryMenuCache();;
                    break;
                case 'all':
                default:
                    $this->delTabCache();
                    $this->delCategoryMenuCache();
                    break;
            }
        }
        function product_category_updated($category_id)
        {
            $this->delCategoryMenuCache();
        }

        function on_product_meta_save()
        {
            $this->custom_del_feature_cache('');
        }
    }
    $objFeature = new CustomThemeFeatureHelper();
    add_action('wp_head', array($objFeature, 'custom_header_script'));

    add_filter('woocommerce_products_admin_list_table_filters', array($objFeature, 'custom_add_type_filter'), 11, 1);

    add_filter('request', array($objFeature, 'back_request'), 9, 1);

    // 处理过滤请求
    add_action('pre_get_posts', array($objFeature, 'apply_custom_filter'), 10, 1);

    add_action( 'admin_enqueue_scripts', array( $objFeature, 'enqueue_scripts_and_styles' ));

    // 添加自定义列
    add_filter('manage_edit-product_columns', array( $objFeature, 'add_product_tab_columns'));

    // 填充自定义列的数据
    add_action('manage_product_posts_custom_column',  array( $objFeature, 'populate_custom_product_column'), 10, 2);

    add_filter('bulk_actions-edit-product', array( $objFeature,  'edit_product_bulk_action'), 10, 1);
    add_filter('handle_bulk_actions-edit-product', array( $objFeature, 'handle_custom_bulk_action'), 10, 3);

    add_filter('yikes-woo-handle-tab-save', array( $objFeature, 'del_cache_when_change'), 10, 2);

    add_action('admin_notices', array( $objFeature, 'custom_bulk_action_admin_notice'));
//    add_action('wp_ajax_user_registration_user_form_submit', array($objFeature, 'ajax_user_registration_user_form_submit'));


    add_filter( 'body_class', array( $objFeature, 'custom_body_class_for_plp' ));

    add_filter( 'deleted_term', array( $objFeature, 'customCatChange'), 10, 3);
    add_filter( 'created_term', array( $objFeature, 'customCatChange'), 10, 3);
    add_filter( 'edited_term', array( $objFeature, 'customCatChange'), 10, 3);

    add_action('del_feature_cache', array( $objFeature, 'custom_del_feature_cache'), 10, 1);

    add_action( 'created_product_cat', array($objFeature, 'product_category_updated'));
    add_action( 'edited_product_cat', array($objFeature, 'product_category_updated' ) );
    add_action( 'delete_product_cat', array($objFeature, 'product_category_updated' ));
    add_action('woocommerce_process_product_meta', array($objFeature, 'on_product_meta_save'), 10, 1);


}

