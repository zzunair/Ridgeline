<?php
/**
 * Homepage: featured products.
 *
 * @package RIDGELINE
 */

$limit = max( 1, (int) ridgeline_mod( 'featured_count' ) );
$args  = array(
	'status'     => 'publish',
	'limit'      => $limit,
	'visibility' => 'catalog',
	'orderby'    => 'date',
	'order'      => 'DESC',
);
switch ( ridgeline_mod( 'featured_source' ) ) {
	case 'featured':
		$args['featured'] = true;
		break;
	case 'sale':
		$args['include'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		break;
	case 'best':
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery
		break;
	case 'category':
		if ( ridgeline_mod( 'featured_cat' ) ) {
			$args['category'] = array( sanitize_title( ridgeline_mod( 'featured_cat' ) ) );
		}
		break;
}
$products = wc_get_products( $args );
if ( ! $products ) {
	return;
}
?>
<section class="rdg-section" aria-labelledby="rdg-featured-title">
	<div class="rdg-wrap">
		<div class="rdg-section__head">
			<h2 id="rdg-featured-title" class="rdg-h2"><?php echo esc_html( ridgeline_mod( 'featured_heading' ) ); ?></h2>
			<a class="rdg-link" href="<?php echo esc_url( ridgeline_shop_url() ); ?>"><?php echo esc_html( ridgeline_mod( 'featured_link' ) ); ?></a>
		</div>
		<ul class="rdg-grid rdg-grid--4 rdg-scroller">
			<?php foreach ( $products as $product ) : ?>
				<li><?php get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
