<section class='jdgm-widget jdgm-revs-tab'>
	<div class='jdgm-revs-tab-btn btn' position='bottom'><?php echo esc_html( $atts['button'] ); ?></div>
	<div class='jdgm-revs-tab__header'>
		<a class='jdgm-close-ico'></a>
		<h3 class='jdgm-revs-tab__title'><?php echo esc_html( $atts['title'] ); ?></h3>
		<a href='/pages/reviews'>
			<div data-score='<?php echo $all_review_rating['all_reviews_rating']; ?>' class='jdgm-all-reviews-rating'></div><?php echo $all_review_count['all_reviews_count']; ?> reviews
		</a>
	</div>
	<?php echo $review_tab['reviews_tab']; ?>
</section>
