<?php
/**
 * Homepage: testimonials.
 *
 * @package RIDGELINE
 */

$rows = ridgeline_rows( ridgeline_mod( 'reviews_list' ) );
if ( ! $rows ) {
	return;
}
?>
<section class="rdg-section" aria-labelledby="rdg-reviews-title">
	<div class="rdg-wrap">
		<div class="rdg-section__head">
			<h2 id="rdg-reviews-title" class="rdg-h2"><?php echo esc_html( ridgeline_mod( 'reviews_heading' ) ); ?></h2>
		</div>
		<ul class="rdg-reviews rdg-scroller">
			<?php foreach ( $rows as $row ) : ?>
				<li>
					<figure class="rdg-review">
						<p class="rdg-review__stars" aria-label="5 out of 5 stars"><?php echo str_repeat( ridgeline_icon( 'star', 18 ), 5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<blockquote><p><?php echo esc_html( $row[0] ); ?></p></blockquote>
						<?php if ( ! empty( $row[1] ) ) : ?>
							<figcaption><strong><?php echo esc_html( $row[1] ); ?></strong><?php echo ! empty( $row[2] ) ? ' · ' . esc_html( $row[2] ) : ''; ?></figcaption>
						<?php endif; ?>
					</figure>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
