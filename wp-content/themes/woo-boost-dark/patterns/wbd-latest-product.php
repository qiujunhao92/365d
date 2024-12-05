<?php
/**
 * Title: Woo Boost Dark Latest Product
 * Slug: woo-boost-dark/latest-product
 * Categories: woo-boost-dark
 *
 * @package Woo Boost Dark
 * @since 1.0.0
 */

?>

<?php

if ( ! function_exists( 'is_woocommerce_activated' ) ) {
	if ( class_exists( 'woocommerce' ) ) {
		?>
		<!-- wp:group {"style":{"spacing":{"margin":{"top":"0"},"padding":{"right":"var:preset|spacing|50","left":"var:preset|spacing|50"}}},"layout":{"type":"constrained","justifyContent":"center"},"metadata":{"name":"Product"}} -->
		<div class="wp-block-group" style="margin-top:0;padding-right:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:spacer {"height":"80px"} -->
		<div style="height:80px" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<!-- wp:heading {"style":{"typography":{"fontSize":"32px","fontStyle":"normal","fontWeight":"700","lineHeight":"1.2"},"spacing":{"margin":{"bottom":"var:preset|spacing|70","top":"0"}}},"fontFamily":"hind"} -->
		<h2 class="wp-block-heading has-hind-font-family" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--70);font-size:32px;font-style:normal;font-weight:700;line-height:1.2"><?php echo esc_html__( 'Choose Your Products !', 'woo-boost-dark' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:woocommerce/product-new {"columns":4,"rows":1,"alignButtons":true,"stockStatus":["","instock","outofstock","onbackorder"]} /--></div>
		<!-- /wp:group -->
		<?php
	}
}
?>
