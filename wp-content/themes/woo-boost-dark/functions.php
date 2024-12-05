<?php

function woo_boost_dark_scripts() {
    // Enqueue parent theme's style
    wp_enqueue_style('woo-boost-dark-parent-style', get_template_directory_uri() . '/style.css');

}
add_action('wp_enqueue_scripts', 'woo_boost_dark_scripts');

/**
 * Registers pattern categories.
 *
 * @since Woo Boost Dark 1.0.0
 *
 * @return void
 */
function woo_boost_dark_register_pattern_category()
{

    $patterns = array();

    $block_pattern_categories = array(
        'woo-boost-dark' => array('label' => __('Woo Boost Dark Patterns', 'woo-boost-dark'))
    );

    $block_pattern_categories = apply_filters('woo_boost_dark_block_pattern_categories', $block_pattern_categories);

    foreach ($block_pattern_categories as $name => $properties) {
        if (!WP_Block_Pattern_Categories_Registry::get_instance()->is_registered($name)) {
            register_block_pattern_category($name, $properties);
        }
    }
}
add_action('init', 'woo_boost_dark_register_pattern_category', 9);




