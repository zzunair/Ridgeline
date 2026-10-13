<?php
/**
 * Product card.
 *
 * @package RIDGELINE
 *
 * @var array $args { product: WC_Product, heading: string }
 */

$product = isset( $args['product'] ) ? $args['product'] : null;
if ( ! $product instanceof WC_Product ) {
	return;
}
$heading   = isset( $args['heading'] ) ? $args['heading'] : 'h3';
$id        = $product->get_id();
$url       = get_permalink( $id );
$brand     = ridgeline_brand( $product );
$stock     = ridgeline_stock( $product );
$call      = ridgeline_is_call_for_price( $product );
$sku       = $product->get_sku();
$specs     = ridgeline_product_specs( $product, 3 );
$image     = $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'rdg-card__img', 'loading' => 'lazy' ) );
$has_image = (bool) $product->get_image_id();
$badge     = '';
if ( $product->is_on_sale() && ! $call ) {
	$badge = 'Sale';
} elseif ( $product->is_featured() ) {
	$badge = 'Featured';
}
$title_id = 'rdg-card-' . $id;
?>
<article class="rdg-card" data-product-id="<?php echo (int) $id; ?>" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<a class="rdg-card__media" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true">
		<?php echo $has_image ? $image : '<span class="rdg-ph rdg-card__img"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( $badge ) : ?>
			<span class="rdg-card__badge<?php echo 'Sale' === $badge ? ' rdg-card__badge--sale' : ''; ?>"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>
		<?php echo ridgeline_stock_badge( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
	<div class="rdg-card__body">
		<?php if ( $brand ) : ?>
			<p class="rdg-card__brand"><?php echo esc_html( $brand->name ); ?></p>
		<?php endif; ?>
		<<?php echo tag_escape( $heading ); ?> class="rdg-card__title" id="<?php echo esc_attr( $title_id ); ?>"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></<?php echo tag_escape( $heading ); ?>>
		<?php if ( $sku ) : ?>
			<p class="rdg-card__sku">Model / SKU: <?php echo esc_html( $sku ); ?></p>
		<?php endif; ?>
		<?php if ( $specs ) : ?>
			<ul class="rdg-card__specs">
				<?php foreach ( $specs as $label => $value ) : ?>
					<li><span><?php echo esc_html( $label ); ?></span> <?php echo esc_html( $value ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php echo ridgeline_price_html( $product, 'rdg-card__price' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php if ( ridgeline_compare_enabled() ) : ?>
			<label class="rdg-check rdg-card__compare">
				<input type="checkbox" data-rdg-compare="<?php echo (int) $id; ?>" data-name="<?php echo esc_attr( $product->get_name() ); ?>">
				<span class="rdg-check__box" aria-hidden="true"></span>
				<span class="rdg-check__label" data-rdg-compare-label>Compare</span>
			</label>
		<?php endif; ?>

		<div class="rdg-card__actions">
			<?php if ( 'out' === $stock['state'] && ! $product->backorders_allowed() ) : ?>
				<button type="button" class="rdg-btn rdg-btn--disabled" disabled>Out of stock</button>
			<?php elseif ( $call ) : ?>
				<a class="rdg-btn rdg-btn--accent" href="<?php echo esc_attr( ridgeline_tel( ridgeline_mod( 'dealer_phone' ) ) ); ?>">Call for price</a>
			<?php elseif ( $product->is_type( 'simple' ) && $product->is_purchasable() ) : ?>
				<?php
				$previous_product    = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
				$GLOBALS['product'] = $product;
				woocommerce_template_loop_add_to_cart(
					array(
						'class'      => 'rdg-btn rdg-btn--brand button add_to_cart_button ajax_add_to_cart product_type_simple',
						'attributes' => array(
							'data-product_id'  => $id,
							'data-product_sku' => $sku,
							'aria-label'       => sprintf( 'Add %s to cart', $product->get_name() ),
							'rel'              => 'nofollow',
						),
					)
				);
				$GLOBALS['product'] = $previous_product;
				?>
			<?php else : ?>
				<a class="rdg-btn rdg-btn--brand" href="<?php echo esc_url( $url ); ?>">Choose options</a>
			<?php endif; ?>
			<a class="rdg-btn rdg-btn--outline" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( sprintf( 'View %s', $product->get_name() ) ); ?>"><?php echo $call ? 'Request info' : 'View'; ?></a>
		</div>
	</div>
</article>
