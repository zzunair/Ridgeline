<?php
/**
 * Homepage: promo banner.
 *
 * @package RIDGELINE
 */

if ( ! ridgeline_mod( 'promo_heading' ) ) {
	return;
}
$image = ridgeline_mod( 'promo_image' );
?>
<section class="rdg-promo<?php echo $image ? ' rdg-promo--image' : ''; ?>" aria-labelledby="rdg-promo-title"<?php echo $image ? ' style="--promo-image:url(' . esc_url( $image ) . ')"' : ''; ?>>
	<div class="rdg-wrap rdg-promo__inner">
		<div>
			<?php if ( ridgeline_mod( 'promo_eyebrow' ) ) : ?>
				<p class="rdg-promo__eyebrow"><?php echo esc_html( ridgeline_mod( 'promo_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2 id="rdg-promo-title" class="rdg-promo__title"><?php echo esc_html( ridgeline_mod( 'promo_heading' ) ); ?></h2>
			<?php if ( ridgeline_mod( 'promo_text' ) ) : ?>
				<p><?php echo esc_html( ridgeline_mod( 'promo_text' ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( ridgeline_mod( 'promo_button' ) && ridgeline_mod( 'promo_url' ) ) : ?>
			<a class="rdg-btn rdg-btn--ink rdg-btn--lg" href="<?php echo esc_url( ridgeline_url( ridgeline_mod( 'promo_url' ) ) ); ?>"><?php echo esc_html( ridgeline_mod( 'promo_button' ) ); ?></a>
		<?php endif; ?>
	</div>
</section>
