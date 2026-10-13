<?php
/**
 * Homepage: shop by brand. Uses the brand thumbnail when set, otherwise the name.
 *
 * @package RIDGELINE
 */

$brands = ridgeline_brands( 12 );
if ( ! $brands ) {
	return;
}
$opacity = min( 100, max( 0, (int) ridgeline_mod( 'brands_opacity' ) ) ) / 100;
?>
<section class="rdg-section" aria-labelledby="rdg-brands-title">
	<div class="rdg-wrap">
		<div class="rdg-section__head">
			<h2 id="rdg-brands-title" class="rdg-h2"><?php echo esc_html( ridgeline_mod( 'brands_heading' ) ); ?></h2>
		</div>
		<ul class="rdg-brandgrid<?php echo ridgeline_mod( 'brands_carousel' ) ? ' rdg-scroller' : ''; ?>" style="--logo-opacity:<?php echo esc_attr( $opacity ); ?>">
			<?php
			foreach ( $brands as $term ) :
				$thumb = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
				?>
				<li>
					<a class="rdg-brandtile" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
						<?php if ( $thumb ) : ?>
							<?php echo wp_get_attachment_image( $thumb, 'medium', false, array( 'alt' => $term->name, 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
							<span><?php echo esc_html( $term->name ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
