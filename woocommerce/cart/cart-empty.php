<?php
/**
 * Empty cart.
 *
 * @package RIDGELINE
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_cart_is_empty' );
$phone = ridgeline_mod( 'dealer_phone' );
?>
<div class="rdg-empty">
	<span class="rdg-empty__mark" aria-hidden="true"><?php echo ridgeline_icon( 'cart', 36 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<h2 class="rdg-h2"><?php esc_html_e( 'Your cart is empty', 'ridgeline' ); ?></h2>
	<p><?php esc_html_e( 'Browse equipment, parts and accessories — or call us and we’ll find the right machine.', 'ridgeline' ); ?></p>
	<div class="rdg-empty__actions">
		<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
			<a class="rdg-btn rdg-btn--brand" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'woocommerce' ) ) ); ?></a>
		<?php endif; ?>
		<?php if ( $phone ) : ?>
			<a class="rdg-btn rdg-btn--accent" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>">Call <?php echo esc_html( $phone ); ?></a>
		<?php endif; ?>
	</div>
</div>
