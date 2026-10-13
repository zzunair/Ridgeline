<?php
/**
 * Homepage: numbered feature blocks.
 *
 * @package RIDGELINE
 */

$rows = ridgeline_rows( ridgeline_mod( 'features_list' ) );
if ( ! $rows ) {
	return;
}
?>
<section class="rdg-section" aria-label="Why shop with us">
	<div class="rdg-wrap">
		<ul class="rdg-features rdg-scroller">
			<?php foreach ( $rows as $i => $row ) : ?>
				<li class="rdg-feature">
					<span class="rdg-feature__num" aria-hidden="true"><?php echo (int) ( $i + 1 ); ?></span>
					<h3 class="rdg-h3"><?php echo esc_html( $row[0] ); ?></h3>
					<?php if ( ! empty( $row[1] ) ) : ?>
						<p><?php echo esc_html( $row[1] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $row[2] ) ) : ?>
						<a class="rdg-link" href="<?php echo esc_url( ridgeline_url( $row[2] ) ); ?>">Learn more<span class="screen-reader-text"> about <?php echo esc_html( $row[0] ); ?></span> →</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
