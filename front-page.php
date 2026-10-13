<?php
/**
 * Homepage. Sections render in the order set under Customizer → Homepage → Section Order.
 *
 * @package RIDGELINE
 */

get_header();

$allowed = array( 'hero', 'finder', 'categories', 'featured', 'brands', 'story', 'features', 'trust', 'promo', 'testimonials', 'compare', 'contact' );
$order   = array_values( array_intersect( array_map( 'trim', explode( ',', strtolower( (string) ridgeline_mod( 'home_order' ) ) ) ), $allowed ) );
$needs_wc = array( 'finder', 'categories', 'featured', 'brands' );
?>
<main id="primary" class="rdg-home">
	<h1 class="screen-reader-text"><?php echo esc_html( ridgeline_mod( 'dealer_name' ) ); ?></h1>
	<?php
	foreach ( array_unique( $order ) as $section ) {
		if ( in_array( $section, $needs_wc, true ) && ! function_exists( 'wc_get_products' ) ) {
			continue;
		}
		get_template_part( 'template-parts/home/' . $section );
	}
	?>
</main>
<?php
get_footer();
