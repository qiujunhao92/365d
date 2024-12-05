<?php
//products each page
add_filter( 'loop_shop_per_page', function($cols){
    return 16;
}, 20 );

function custom_woocommerce_gallery_columns() {
    echo <<<HTML
 <style>
        @media (min-width: 900px) {
            .woocommerce ul.products-block-post-template {
                display: flex;
                flex-wrap: wrap;               
            }

            .woocommerce ul.products-block-post-template li.product {
                flex: 0 0 22%; /* 每行显示4个产品 */
                max-width: 22%;
                margin: 0.5%;
                box-sizing: border-box;
            }
        }
        @media (max-width: 899px) {
            .woocommerce ul.products-block-post-template {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
            }

            .woocommerce ul.products-block-post-template li.product {
                flex: 0 0 46%;
                max-width: 46%;
                margin: 0.5%;
                box-sizing: border-box;
            }
        }

        @media (max-width: 767px) {
            .woocommerce ul.products-block-post-template li.product {
                flex: 0 0 100%; /* 每行显示1个产品 */
                max-width: 100%;
                margin: 0;
                box-sizing: border-box;
            }
        }
    </style>
HTML;
}
add_action('wp_head', 'custom_woocommerce_gallery_columns');

add_filter( 'woocommerce_related_products', 'exclude_related_products', 10, 3 );
function exclude_related_products( $related_posts, $product_id, $args ) {
    // 在这里添加要排除的产品ID
    $exclude_ids = array( 10155 ); // 替换为你要排除的商品ID
    // 过滤相关产品
    return array_diff( $related_posts, $exclude_ids );
}
add_filter( 'woocommerce_blocks_product_grid_item_html', 'custom_product_grid_item_html' , 10, 3);
function custom_product_grid_item_html($html, $data,  $product )
{
    // 在这里添加要排除的产品ID
    $exclude_ids = array(10155); // 替换为你要排除的商品ID
    // 过滤交叉销售的产品
    if (in_array($product->get_id(), $exclude_ids)) {
        return '';
    }

    return $html;
}

// 添加多视频设置框到右侧 "Product Gallery" 下方
function add_product_videos_below_gallery() {
    add_meta_box(
        'product_videos_meta',            // HTML ID
        __('Product Videos', 'custom'),   // 标题
        'render_product_videos_metabox',  // 渲染回调函数
        'product',                        // 显示在产品编辑页面
        'normal',                           // 显示在右侧栏
        'high'                             // 在 "Product Gallery" 下方显示
    );
}
add_action('add_meta_boxes', 'add_product_videos_below_gallery');

// 渲染多视频设置框
function render_product_videos_metabox($post) {
    // 获取当前产品的视频数据
    $product_videos = get_post_meta($post->ID, '_product_videos', true);
    $product_videos = $product_videos ? json_decode($product_videos, true) : [];
    ?>
    <div id="product-videos-container">
        <?php foreach ($product_videos as $index => $video): ?>
            <div class="video-item" style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd;">
                <!-- 第一行：视频播放器 -->
                <div style="margin-bottom: 10px;">
                    <video controls style="width: 100%; max-width: 300px;">
                        <source src="<?php echo esc_url($video['url']); ?>" type="video/mp4">
                        <?php _e('Your browser does not support the video tag.', 'custom'); ?>
                    </video>
                </div>
                <!-- 第二行：控件 -->
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="product_videos[<?php echo $index; ?>][url]" value="<?php echo esc_url($video['url']); ?>" style="flex: 2;" readonly>
                    <input type="text" name="product_videos[<?php echo $index; ?>][title]" value="<?php echo esc_attr($video['title'] ?? ''); ?>" placeholder="Video Title" style="flex: 2;">
                    <button type="button" class="replace-video button button-secondary"><?php _e('Replace', 'custom'); ?></button>
                    <button type="button" class="remove-video button button-secondary"><?php _e('Remove', 'custom'); ?></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="margin-top: 20px;">
        <button type="button" id="add-video-button" class="button button-primary"><?php _e('Add Videos', 'custom'); ?></button>
        <button type="button" id="clear-videos-button" class="button button-secondary" style="margin-left: 10px;"><?php _e('Clear All Videos', 'custom'); ?></button>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            let videoIndex = <?php echo count($product_videos); ?>;

            // 批量添加视频
            $('#add-video-button').on('click', function () {
                const frame = wp.media({
                    title: '<?php _e('Select or Upload Videos', 'custom'); ?>',
                    button: {
                        text: '<?php _e('Use these videos', 'custom'); ?>'
                    },
                    library: { type: 'video' },
                    multiple: true
                });

                frame.on('select', function () {
                    const attachments = frame.state().get('selection').toArray();
                    attachments.forEach(function (attachment) {
                        const video = attachment.toJSON();
                        const newVideoHtml = `
                    <div class="video-item" style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd;">
                        <div style="margin-bottom: 10px;">
                            <video controls style="width: 100%; max-width: 300px;">
                                <source src="${video.url}" type="video/mp4">
                                <?php _e('Your browser does not support the video tag.', 'custom'); ?>
                            </video>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" name="product_videos[${videoIndex}][url]" value="${video.url}" style="flex: 2;" readonly>
                            <input type="text" name="product_videos[${videoIndex}][title]" value="${video.title || ''}" placeholder="Video Title" style="flex: 2;">
                            <button type="button" class="replace-video button button-secondary">Replace</button>
                            <button type="button" class="remove-video button button-secondary">Remove</button>
                        </div>
                    </div>`;
                        $('#product-videos-container').append(newVideoHtml);
                        videoIndex++;
                    });
                });

                frame.open();
            });

            // 批量删除视频
            $('#clear-videos-button').on('click', function () {
                if (confirm('<?php _e('Are you sure you want to remove all videos?', 'custom'); ?>')) {
                    $('#product-videos-container').empty();
                }
            });

            // 删除单个视频
            $('#product-videos-container').on('click', '.remove-video', function () {
                $(this).closest('.video-item').remove();
            });

            // 替换视频
            $('#product-videos-container').on('click', '.replace-video', function () {
                const button = $(this);
                const parent = button.closest('.video-item');
                const frame = wp.media({
                    title: '<?php _e('Select or Upload Video', 'custom'); ?>',
                    button: {
                        text: '<?php _e('Use this video', 'custom'); ?>'
                    },
                    library: { type: 'video' },
                    multiple: false
                });

                frame.on('select', function () {
                    const video = frame.state().get('selection').first().toJSON();
                    parent.find('video source').attr('src', video.url);
                    parent.find('input[name*="[url]"]').val(video.url);
                    parent.find('video')[0].load();
                });

                frame.open();
            });
        });
    </script>
    <?php
}

// 保存多视频设置数据
function save_product_videos_meta($post_id) {
    // 检查权限
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 检查是否是自动保存
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 保存视频数据
    if (isset($_POST['product_videos'])) {
        $product_videos = array_map(function ($video) {
            return [
                'url' => esc_url_raw($video['url']),
                'title' => sanitize_text_field($video['title']),
            ];
        }, $_POST['product_videos']);

        update_post_meta($post_id, '_product_videos', json_encode($product_videos));
    }
}
add_action('save_post', 'save_product_videos_meta');


