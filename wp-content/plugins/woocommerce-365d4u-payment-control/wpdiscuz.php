<?php

function customize_mu_file_count($count) {
    return 20;
}
add_filter('wpdiscuz_mu_file_count', 'customize_mu_file_count');
function customize_allowed_upload_extensions($extensions) {

    return "accept='image/*,audio/mp4,video/mp4,.mov' multiple='multiple'";
}
add_filter('wpdiscuz_mu_allowed_extensions', 'customize_allowed_upload_extensions');

function customize_allowed_mime_types($extensions) {
    $extensions['webp'] = 'image/webp';
    $extensions['mp4'] = 'video/mp4|audio/mp4|video/quicktime';
    $extensions['mov'] = 'video/quicktime|audio/mov';
    return $extensions;
}
add_filter('wpdiscuz_mu_mime_types', 'customize_allowed_mime_types');

function customize_add_attachment_ids($attachmentIds, $uploadData, $file) {
    $attachmentIds['videos'][] = $uploadData['id'];
    return $attachmentIds;
}
add_filter('wpdiscuz_mu_add_attachment_ids', 'customize_add_attachment_ids', 10, 3);
function customize_add_attachment_data($attachmentsData, $uploadData, $file) {
    $attachmentsData['videos'][] = $uploadData;
    return $attachmentsData;
}
add_filter('wpdiscuz_mu_add_attachments_data', 'customize_add_attachment_data', 10, 3);

function custom_wpdiscuz_video_attachments($html, $attachments, $comment) {
    foreach ($attachments as $attachment) {
        $mime_type = get_post_mime_type($attachment);
        if (strpos($mime_type, 'video') !== false) {
            // 这里处理视频文件
            $video_url = wp_get_attachment_url($attachment);
            $thumb_id = get_post_thumbnail_id($attachment);
            $posterHtml = '';
            if (!empty( $thumb_id ) ) {
                $poster = wp_get_attachment_url($thumb_id) ?? '';
                $posterHtml = ' poster="' . $poster . '"';
            }
            $html .= '<video controls width="320" height="240"  src="' .  esc_url($video_url) . '"' . $posterHtml . '></video>';

            if (is_admin()) {
                 $html .= '<div class="cusEditThumbnail"><a href="/wp-admin/post.php?post=' . $attachment . '&action=edit" target="_blank">Edit Media</a></div>';
            }
        }
    }
    return $html;
}
add_filter('wpdiscuz_mu_get_attachments', 'custom_wpdiscuz_video_attachments', 10, 3);

function custom_read_video_metadata($metadata, $file, $file_format, $data)
{
    if (isset($data['filename']) && in_array(strtolower($file_format), ['mp4', 'mov'])) {
        $arrUploadDir = wp_upload_dir();
        if ($arrUploadDir && !empty($arrUploadDir['subdir'])) {
            $metadata['file'] = trim($arrUploadDir['subdir'], '\\/') . '/' . $data['filename'];
        }

    }
    return $metadata;
}
add_filter( 'wp_read_video_metadata', 'custom_read_video_metadata', 10, 4);

//hide src
add_filter('wp_calculate_image_srcset', '__return_false');

// Enqueue custom styles and scripts
function csw_enqueue_scripts() {
    wp_enqueue_style( 'wpdiscuz_style', plugin_dir_url( __FILE__ ) . 'css/wpdiscuz_style.css', [], '1.0.0.5' );
    wp_enqueue_script( 'wpdiscuz_script', plugin_dir_url( __FILE__ ) . 'js/wpdiscuz_script.js', array('jquery'), null, true );
}
add_action( 'wp_enqueue_scripts', 'csw_enqueue_scripts' );