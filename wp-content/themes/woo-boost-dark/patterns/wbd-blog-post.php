<?php
/**
 * Title: Woo Boost Dark Blog Post
 * Slug: woo-boost-dark/blog-post
 * Categories: woo-boost-dark
 *
 * @package Woo Boost Dark
 * @since 1.0.0
 */

?>

<!-- wp:group {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"var:preset|spacing|50","right":"var:preset|spacing|50"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained"},"metadata":{"name":"Blog Post"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:var(--wp--preset--spacing--50);padding-bottom:0;padding-left:var(--wp--preset--spacing--50)"><!-- wp:spacer {"height":"80px"} -->
<div style="height:80px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"style":{"typography":{"fontSize":"32px","fontStyle":"normal","fontWeight":"700","lineHeight":"1.2"},"spacing":{"margin":{"bottom":"var:preset|spacing|70","top":"0"}}},"fontFamily":"hind"} -->
<h2 class="wp-block-heading has-hind-font-family" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--70);font-size:32px;font-style:normal;font-weight:700;line-height:1.2"><?php echo esc_html__( 'News', 'woo-boost-dark' ); ?> &amp; <?php echo esc_html__( 'Articles !', 'woo-boost-dark' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:query {"queryId":41,"query":{"perPage":"3","pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}},"border":{"radius":"6px"}},"backgroundColor":"tertiary","layout":{"inherit":false}} -->
<div class="wp-block-group has-tertiary-background-color has-background" style="border-radius:6px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:post-featured-image /-->

<!-- wp:post-date {"style":{"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}},"typography":{"fontSize":"14px"}},"textColor":"contrast"} /-->

<!-- wp:post-title {"isLink":true,"style":{"typography":{"lineHeight":"1.5","fontSize":"21px","fontStyle":"normal","fontWeight":"600"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"},"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary"} /-->

<!-- wp:post-excerpt {"moreText":"Read More","excerptLength":32,"style":{"elements":{"link":{"color":{"text":"var:preset|color|Theme"}}},"spacing":{"margin":{"right":"0","left":"0","top":"var:preset|spacing|30","bottom":"0"}}},"textColor":"contrast"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query -->

<!-- wp:spacer {"height":"80px"} -->
<div style="height:80px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div>
<!-- /wp:group -->