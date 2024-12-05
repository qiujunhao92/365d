<?php
if (!class_exists('Custom365dBannerImages')) {
    class Custom365dBannerImages {

        // 初始化钩子
        public function __construct() {
            add_filter('woocommerce_get_settings_general', array($this, 'add_banner_settings_with_links'), 10, 2);
            add_action('woocommerce_admin_field_banner_uploader', array($this, 'render_banner_uploader_field'));
            add_action('woocommerce_update_option_banner_uploader', array($this, 'save_banner_uploader_field'));
            add_action('admin_enqueue_scripts', array($this, 'add_inline_media_uploader'));
        }

        // 添加自定义设置到 WooCommerce
        public function add_banner_settings_with_links($settings, $current_section) {
            if ($current_section === '') {
                $settings[] = array(
                    'title' => __('Banner Settings', 'custom365d-banner-images'),
                    'type'  => 'title',
                    'id'    => 'banner_settings'
                );

                $settings[] = array(
                    'title'    => __('Banner Images', 'custom365d-banner-images'),
                    'desc'     => __('Upload multiple images and customize their links and order.', 'custom365d-banner-images'),
                    'id'       => 'banner_images_with_links',
                    'type'     => 'banner_uploader',
                    'desc_tip' => true,
                );

                $settings[] = array(
                    'type' => 'sectionend',
                    'id'   => 'banner_settings'
                );
            }

            return $settings;
        }


        private function getBannerHtml($title, $banners, $option_name)
        {
              $html = '';
              foreach ($banners as $banner){
                  $html .= <<<BANNAER
             <div class="banner-item">
                    <input type="hidden" class="banner-image" name="{$option_name}[images][]" value="{$banner['image']}">
                    <img src="{$banner['image']}" alt="Banner" style="max-width: 150px; display: block; margin-bottom: 10px;">
                    <button type="button" class="upload-banner-button">Change Image</button>
                    <input type="text" class="banner-link" name="{$option_name}[links][]" value="{$banner['url']}" placeholder="Image Link">
                    <input type="text" class="banner-title" name="{$option_name}[titles][]" value="{$banner['title']}" placeholder="Title">
                    <textarea class="banner-description" name="{$option_name}[descriptions][]" placeholder="Description">{$banner['description']}</textarea>
                    <input type="number" class="banner-order" name="{$option_name}[order][]" value="{$banner['order']}" placeholder="Order">
                    <button type="button" class="remove-banner">Remove</button>
            </div>
BANNAER;

              }
             return  <<<HTML
   <div  class="banner-container {$option_name}">
        <h2>Top {$title} Banners</h2>
        <input type="hidden" name="clear_{$option_name}" value="0">
        <button type="button" class="bulk-upload-banners" option="{$option_name}" >Bulk Upload {$title} Images</button>
        <button type="button" class="clear-all-banners" style="margin-left: 10px;"  option="{$option_name}" >Clear All {$title} Banners</button>
        <div id="banner-repeater-{$option_name}" class="banner-repeater">
             {$html}        
        </div>
  </div>
HTML;
        }

        // 渲染自定义字段
        public function render_banner_uploader_field($value) {
            $option_name_pc = $value['id'] . '_pc'; // PC Banner 数据
            $option_name_mobile = $value['id'] . '_mobile'; // Mobile Banner 数据
            $pc_banners = get_option($option_name_pc, array());
            $mobile_banners = get_option($option_name_mobile, array());

            $pcHtml =$this->getBannerHtml('PC', $pc_banners, $option_name_pc);
            $mobileHtml =$this->getBannerHtml('Mobile', $mobile_banners, $option_name_mobile);

            $option_name_custom_cat = $value['id'] . '_custom_cat'; // PC Banner 数据
            $custom_cat_banners = get_option($option_name_custom_cat, array());
            $pc_custom_banner = $custom_cat_banners['pc'] ?? [];
            $mobile_custom_banner = $custom_cat_banners['mobile'] ?? [];

            $option_name_collection_cat = $value['id'] . '_collection_cat'; // PC Banner 数据
            $collection_cat_banners = get_option($option_name_collection_cat, array());
            $pc_collection_banner = $collection_cat_banners['pc'] ?? [];
            $mobile_collection_banner = $collection_cat_banners['mobile'] ?? [];

            echo  <<<STYLE
          <div class="banner_total_container">
              {$pcHtml}
              {$mobileHtml}
              <div class="banner-container banner_images_with_links_custom_cat">
                <h1>Custom Category Base Banners</h1>          
                <div id="banner-repeater-banner_images_with_links_custom_cat" class="banner-repeater">
                        <div class="banner-item"> 
                           <input type="hidden" class="banner-image" name="{$option_name_custom_cat}[pc][image]" value="{$pc_custom_banner['image']}"> 
                           <img src="{$pc_custom_banner['image']}" alt="Banner" style="max-width: 150px; display: block; margin-bottom: 10px;">
                           <button type="button" class="upload-banner-button">Change Image</button>
                           <label class="category-lbl-banner">Custom Banner For PC</label>
                           <input type="text" class="banner-link" name="{$option_name_custom_cat}[pc][link]" value="{$pc_custom_banner['link']}" placeholder="Image Link">
                           <input type="text" class="banner-title" name="{$option_name_custom_cat}[pc][title]" value="{$pc_custom_banner['title']}" placeholder="Title">
                           <textarea class="banner-description" name="{$option_name_custom_cat}[pc][description]" placeholder="Description">{$pc_custom_banner['description']}</textarea> 
                       </div>   
                        <div class="banner-item"> 
                           <input type="hidden" class="banner-image" name="{$option_name_custom_cat}[mobile][image]" value="{$mobile_custom_banner['image']}"> 
                           <img src="{$mobile_custom_banner['image']}" alt="Banner" style="max-width: 150px; display: block; margin-bottom: 10px;">
                           <button type="button" class="upload-banner-button">Change Image</button>
                           <label class="category-lbl-banner">Custom Banner For Mobile</label>
                           <input type="text" class="banner-link" name="{$option_name_custom_cat}[mobile][link]" value="{$mobile_custom_banner['link']}" placeholder="Image Link">
                           <input type="text" class="banner-title" name="{$option_name_custom_cat}[mobile][title]" value="{$mobile_custom_banner['title']}" placeholder="Title">
                           <textarea class="banner-description" name="{$option_name_custom_cat}[mobile][description]" placeholder="Description">{$mobile_custom_banner['description']}</textarea> 
                       </div>             
                </div>
          </div>
              <div class="banner-container banner_images_with_links_collection_cat">
                <h1>Collection Category Base Banners</h1>          
                <div id="banner-repeater-banner_images_with_links_custom_cat" class="banner-repeater">
                        <div class="banner-item"> 
                           <input type="hidden" class="banner-image" name="{$option_name_collection_cat}[pc][image]" value="{$pc_collection_banner['image']}"> 
                           <img src="{$pc_collection_banner['image']}" alt="Banner" style="max-width: 150px; display: block; margin-bottom: 10px;">
                           <button type="button" class="upload-banner-button">Change Image</button>
                           <label class="category-lbl-banner">Collection Banner For PC</label>
                           <input type="text" class="banner-link" name="{$option_name_collection_cat}[pc][link]" value="{$pc_collection_banner['link']}" placeholder="Image Link">
                           <input type="text" class="banner-title" name="{$option_name_collection_cat}[pc][title]" value="{$pc_collection_banner['title']}" placeholder="Title">
                           <textarea class="banner-description" name="{$option_name_collection_cat}[pc][description]" placeholder="Description">{$pc_collection_banner['description']}</textarea> 
                       </div>   
                        <div class="banner-item"> 
                           <input type="hidden" class="banner-image" name="{$option_name_collection_cat}[mobile][image]" value="{$mobile_custom_banner['image']}"> 
                           <img src="{$mobile_custom_banner['image']}" alt="Banner" style="max-width: 150px; display: block; margin-bottom: 10px;">
                           <button type="button" class="upload-banner-button">Change Image</button>
                           <label class="category-lbl-banner">Collection Banner For Mobile</label>
                           <input type="text" class="banner-link" name="{$option_name_collection_cat}[mobile][link]" value="{$mobile_collection_banner['link']}" placeholder="Image Link">
                           <input type="text" class="banner-title" name="{$option_name_collection_cat}[mobile][title]" value="{$mobile_collection_banner['title']}" placeholder="Title">
                           <textarea class="banner-description" name="{$option_name_collection_cat}[mobile][description]" placeholder="Description">{$mobile_collection_banner['description']}</textarea> 
                       </div>             
                </div>
          </div>
          </div>          
          <style>
               .category-lbl-banner{
                  font-size:26px;
                  font-weight: bold;
                  margin:0 20px;
               }
               .banner_images_with_links_custom_cat{
                  margin-top:160px;
               }
               .banner_total_container{ 
                padding-left: 20px;
                border: 1px solid gray; 
               }
                .banner-repeater .banner-item {
                    margin-bottom: 10px;
                    padding: 10px;
                    border: 1px solid #ddd;
                }
                .banner-repeater .banner-item img {
                    max-width: 150px;
                    display: block;
                    margin-bottom: 10px;
                }
                .banner-repeater .banner-item button {
                    margin-top: 5px;
                }
                .bulk-upload-banners {
                    margin-top: 10px;
                    display: inline-block;
                    padding: 5px 10px;
                    background-color: #0071a1;
                    color: #fff;
                    border: none;
                    cursor: pointer;
                }
                .banner-description{
                    vertical-align: bottom;
                }
            </style>
STYLE;

        }


        public function save_banner_uploader_field($option) {
            $option_name_pc = $option['id'] . '_pc';
            $option_name_mobile = $option['id'] . '_mobile';
            if (isset($_POST['clear_' . $option_name_pc]) && $_POST['clear_' . $option_name_pc] === '1') {
                update_option($option_name_pc, array());
            }
            if (isset($_POST['clear_' . $option_name_mobile]) && $_POST['clear_' . $option_name_mobile] === '1') {
                update_option($option_name_mobile, array());
            }
            if (isset($_POST[$option_name_pc])) {
                $this->save_banner_option($option_name_pc, $_POST[$option_name_pc]);
            }

            if (isset($_POST[$option_name_mobile])) {
                $this->save_banner_option($option_name_mobile, $_POST[$option_name_mobile]);
            }
            $option_name_custom_cat = $option['id'] . '_custom_cat'; // PC Banner 数据
            if (isset($_POST[$option_name_custom_cat])) {
                update_option($option_name_custom_cat, $_POST[$option_name_custom_cat]);
            }
            $option_name_collection_cat = $option['id'] . '_collection_cat'; // PC Banner 数据
            if (isset($_POST[$option_name_collection_cat])) {
                update_option($option_name_collection_cat, $_POST[$option_name_collection_cat]);
            }
            do_action('del_feature_cache', 'tab');
        }

        private function save_banner_option($option_name, $data) {
            $banners = array();
            $images = $data['images'];
            $links = $data['links'];
            $titles = $data['titles'];
            $descriptions = $data['descriptions'];
            $orders = $data['order'];

            foreach ($images as $key => $image) {
                if (!empty($image)) {
                    $banners[] = array(
                        'image' => sanitize_text_field($image),
                        'url' => esc_url($links[$key]),
                        'title' => sanitize_text_field($titles[$key]),
                        'description' => sanitize_textarea_field($descriptions[$key]),
                        'order' => intval($orders[$key]?? 0),
                    );
                }
            }
            update_option($option_name, $banners);
        }


        // 嵌入 JavaScript 动态处理逻辑
        public function add_inline_media_uploader($hook) {
            if ('woocommerce_page_wc-settings' !== $hook) {
                return;
            }

            wp_enqueue_media();
            wp_enqueue_script('jquery'); // 显式加载 jQuery
            $inline_script = <<<EDL
            <script>
               jQuery(document).ready(function ($) {                  
                   // 打开媒体上传工具
                    function openMediaUploader(callback) {
                        const mediaUploader = wp.media({
                            title: 'Select Banner Images',
                            button: {
                                text: 'Use these images'
                            },
                            multiple: true
                        });

                        mediaUploader.on('select', function () {
                            const attachments = mediaUploader.state().get('selection').toArray();
                            const images = attachments.map(attachment => attachment.toJSON());
                            callback(images);
                        });

                        mediaUploader.open();
                    }

                    // 批量清除所有 Banners
                    $('.clear-all-banners').on('click', function () {
                        const option_name = $(this).attr('option');
                        const bannerRepeater = $('#banner-repeater-' + option_name);
                        if (confirm('Are you sure you want to remove all banners?')) {
                              bannerRepeater.empty(); // 清空容器中的所有子元素 
                               // 设置隐藏字段标记清空操作
                              $('input[name="clear_' + option_name + '"]').val('1');
                        }
                    });

                    // 批量上传图片
                    $('.bulk-upload-banners').on('click', function () {
                        const option_name = $(this).attr('option');
                        const bannerRepeater = $('#banner-repeater-' + option_name);
                        openMediaUploader(function (images) {
                            images.forEach(image => {
                                
                                const newBanner = '<div class="banner-item">' +
                    '<input type="hidden" class="banner-image" name="'+option_name+'[images][]" value="' + image.url + '">' +
                    '<img src="' + image.url + '" alt="Banner" style="max-width: 150px; display: block; margin-bottom: 10px;">' +
                    '<button type="button" class="upload-banner-button">Change Image</button>' +
                    '<input type="text" class="banner-link" name="'+option_name+'[links][]" placeholder="Image Link">' +
                     '<input type="text" class="banner-title" name="'+option_name+'[titles][]" placeholder="Title">' +
                    '<textarea class="banner-description" name="'+option_name+'[descriptions][]" placeholder="Description"></textarea>' + 
                    '<input type="number" class="banner-order" name="'+option_name+'[order][]" placeholder="Order">' +
                    '<button type="button" class="remove-banner">Remove</button>' +
                '</div>';
                                bannerRepeater.append(newBanner);
                                   // 设置隐藏字段标记清空操作
                                $('input[name="clear_' + option_name + '"]').val('0');
                            });
                        });
                    });

                    // 修改单张图片
                    $('.banner_total_container').on('click', '.upload-banner-button', function () {
                        const button = $(this);
                        const inputField = button.siblings('.banner-image');
                        const previewImage = button.siblings('img');

                        openMediaUploader(function (images) {
                            const image = images[0];
                            inputField.val(image.url);
                            previewImage.attr('src', image.url);
                        });
                    });

                    // 删除 Banner 项目
                    $('.banner_total_container').on('click', '.remove-banner', function () {
                        $(this).closest('.banner-item').remove();
                    });
                });
            </script>
EDL;
            // 添加内联脚本
            wp_add_inline_script('jquery', $inline_script);
        }
    }

    // 实例化类
    new Custom365dBannerImages();
}
