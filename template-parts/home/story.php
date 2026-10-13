<?php
/**
 * Homepage: two alternating image and text blocks.
 *
 * @package RIDGELINE
 */

$blocks = array();
foreach ( array( 1, 2 ) as $n ) {
	if ( ridgeline_mod( "story{$n}_heading" ) ) {
		$blocks[] = $n;
	}
}
if ( ! $blocks ) {
	return;
}
?>
<section class="rdg-section rdg-story" aria-label="About us">
	<div class="rdg-wrap">
		<?php foreach ( $blocks as $i => $n ) : ?>
			<div class="rdg-story__row<?php echo $i % 2 ? ' rdg-story__row--flip' : ''; ?>">
				<div class="rdg-story__media"><?php echo ridgeline_media( ridgeline_mod( "story{$n}_image" ), '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div class="rdg-story__body">
					<?php if ( ridgeline_mod( "story{$n}_eyebrow" ) ) : ?>
						<p class="rdg-eyebrow"><?php echo esc_html( ridgeline_mod( "story{$n}_eyebrow" ) ); ?></p>
					<?php endif; ?>
					<h2 class="rdg-h2"><?php echo esc_html( ridgeline_mod( "story{$n}_heading" ) ); ?></h2>
					<p><?php echo esc_html( ridgeline_mod( "story{$n}_text" ) ); ?></p>
					<?php if ( ridgeline_mod( "story{$n}_button" ) && ridgeline_mod( "story{$n}_url" ) ) : ?>
						<a class="rdg-btn rdg-btn--brand" href="<?php echo esc_url( ridgeline_url( ridgeline_mod( "story{$n}_url" ) ) ); ?>"><?php echo esc_html( ridgeline_mod( "story{$n}_button" ) ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
