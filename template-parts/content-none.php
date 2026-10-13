<?php
/**
 * No posts found.
 *
 * @package RIDGELINE
 */

?>
<div class="rdg-empty">
	<span class="rdg-empty__mark" aria-hidden="true">?</span>
	<h2 class="rdg-h2"><?php esc_html_e( 'Nothing found', 'ridgeline' ); ?></h2>
	<p><?php echo is_search() ? esc_html__( 'Try different words, or search our products instead.', 'ridgeline' ) : esc_html__( 'There are no posts here yet.', 'ridgeline' ); ?></p>
	<div class="rdg-empty__actions">
		<a class="rdg-btn rdg-btn--brand" href="<?php echo esc_url( function_exists( 'ridgeline_shop_url' ) ? ridgeline_shop_url() : home_url( '/' ) ); ?>"><?php esc_html_e( 'Browse products', 'ridgeline' ); ?></a>
	</div>
</div>
