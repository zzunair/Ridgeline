<?php
/**
 * Homepage: why choose us band.
 *
 * @package RIDGELINE
 */

$rows = ridgeline_rows( ridgeline_mod( 'trust_list' ) );
if ( ! $rows ) {
	return;
}
?>
<section class="rdg-trust" aria-labelledby="rdg-trust-title">
	<div class="rdg-wrap">
		<h2 id="rdg-trust-title" class="rdg-h2"><?php echo esc_html( ridgeline_mod( 'trust_heading' ) ); ?></h2>
		<ul class="rdg-trust__grid">
			<?php foreach ( $rows as $row ) : ?>
				<li class="rdg-trust__item">
					<h3 class="rdg-h3"><?php echo esc_html( $row[0] ); ?></h3>
					<?php if ( ! empty( $row[1] ) ) : ?>
						<p><?php echo esc_html( $row[1] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
