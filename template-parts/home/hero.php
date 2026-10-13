<?php
/**
 * Homepage hero slider.
 *
 * @package RIDGELINE
 */

$slides = array();
for ( $n = 1; $n <= 3; $n++ ) {
	if ( ridgeline_mod( "hero{$n}_on" ) && ridgeline_mod( "hero{$n}_heading" ) ) {
		$slides[] = $n;
	}
}
if ( ! $slides ) {
	return;
}
$phone    = ridgeline_mod( 'dealer_phone' );
$multiple = count( $slides ) > 1;
?>
<section class="rdg-hero" aria-roledescription="carousel" aria-label="Featured offers" data-rdg-hero<?php echo ( $multiple && ridgeline_mod( 'hero_autoplay' ) ) ? ' data-autoplay="7000"' : ''; ?>>
	<div class="rdg-hero__track">
		<?php
		foreach ( $slides as $i => $n ) :
			$image  = ridgeline_mod( "hero{$n}_image" );
			$mobile = ridgeline_mod( "hero{$n}_mobile" );
			?>
			<div class="rdg-hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( '%1$d of %2$d', $i + 1, count( $slides ) ) ); ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
				<?php if ( $image ) : ?>
					<picture class="rdg-hero__bg">
						<?php if ( $mobile ) : ?>
							<source media="(max-width: 600px)" srcset="<?php echo esc_url( $mobile ); ?>">
						<?php endif; ?>
						<img src="<?php echo esc_url( $image ); ?>" alt="" <?php echo 0 === $i ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
					</picture>
				<?php endif; ?>
				<div class="rdg-wrap rdg-hero__content">
					<?php if ( ridgeline_mod( "hero{$n}_eyebrow" ) ) : ?>
						<p class="rdg-hero__eyebrow"><?php echo esc_html( ridgeline_mod( "hero{$n}_eyebrow" ) ); ?></p>
					<?php endif; ?>
					<h2 class="rdg-hero__title"><?php echo esc_html( ridgeline_mod( "hero{$n}_heading" ) ); ?></h2>
					<?php if ( ridgeline_mod( "hero{$n}_text" ) ) : ?>
						<p class="rdg-hero__text"><?php echo esc_html( ridgeline_mod( "hero{$n}_text" ) ); ?></p>
					<?php endif; ?>
					<div class="rdg-hero__ctas">
						<?php if ( ridgeline_mod( "hero{$n}_button" ) ) : ?>
							<a class="rdg-btn rdg-btn--accent rdg-btn--lg" href="<?php echo esc_url( ridgeline_url( ridgeline_mod( "hero{$n}_url" ) ) ); ?>"><?php echo esc_html( ridgeline_mod( "hero{$n}_button" ) ); ?></a>
						<?php endif; ?>
						<?php if ( ridgeline_mod( 'hero_call' ) && $phone ) : ?>
							<a class="rdg-btn rdg-btn--outline-light rdg-btn--lg" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>">Call <?php echo esc_html( $phone ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $multiple ) : ?>
		<div class="rdg-wrap rdg-hero__controls">
			<button type="button" class="rdg-hero__ctl" data-rdg-hero-pause aria-label="Pause slideshow"><?php echo ridgeline_icon( 'pause', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			<div class="rdg-hero__dots">
				<?php foreach ( $slides as $i => $n ) : ?>
					<button type="button" class="rdg-hero__dot" data-rdg-hero-dot="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( 'Show slide %d', $i + 1 ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>></button>
				<?php endforeach; ?>
			</div>
			<button type="button" class="rdg-hero__ctl" data-rdg-hero-prev aria-label="Previous slide"><?php echo ridgeline_icon( 'left', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			<button type="button" class="rdg-hero__ctl" data-rdg-hero-next aria-label="Next slide"><?php echo ridgeline_icon( 'right', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		</div>
	<?php endif; ?>
</section>
