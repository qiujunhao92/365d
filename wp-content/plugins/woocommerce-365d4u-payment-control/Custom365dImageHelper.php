<?php
function cus_get_intermediate_size($data, $post_id, $size )
{
    $sourceSize = '-300x300.jpg';
    $targetSize = '.jpg';
    if ($data['file'] && strstr($data['file'], $sourceSize)) {
        $data['file'] = str_replace($sourceSize, $targetSize, $data['file']);
    }
    if ($data['path'] && strstr($data['path'], $sourceSize)) {
        $data['path'] = str_replace($sourceSize, $targetSize, $data['path']);
    }
    if ($data['url'] && strstr($data['url'], $sourceSize)) {
        $data['url'] = str_replace($sourceSize, $targetSize, $data['url']);
    }
    return $data;
}































add_filter('image_get_intermediate_size', 'cus_get_intermediate_size', 10, 3);
