<?php
/**
 * Shop filters. A sticky sidebar on desktop, a drawer below 1024px.
 *
 * @package RIDGELINE
 *
 * @var array $args { term: ?WP_Term, total: int }
 */

$term   = $args['term'];
$total  = (int) $args['total'];
$action = preg_replace( '#/page/\d+/?#', '/', strtok( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), '?' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
$chosen = array(
	'fcat'   => ridgeline_filter_values( 'fcat' ),
	'fbrand' => ridgeline_filter_values( 'fbrand' ),
	'fstock' => ridgeline_filter_values( 'fstock' ),
);
$attrs  = ridgeline_attr_filters();

$groups = array();
$groups[] = array(
	'title'   => 'Availability',
	'name'    => 'fstock[]',
	'key'     => 'fstock',
	'options' => array(
		array( 'instock', 'In stock', null ),
		array( 'low', 'Low stock', null ),
		array( 'onbackorder', 'Backorder', null ),
		array( 'outofstock', 'Out of stock', null ),
	),
);

$is_cat_archive = $term && 'product_cat' === $term->taxonomy;
$cat_terms      = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => $is_cat_archive ? $term->term_id : 0,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
	)
);
if ( ! is_wp_error( $cat_terms ) && $cat_terms ) {
	$groups[] = array(
		'title'   => $is_cat_archive ? 'Subcategory' : 'Category',
		'name'    => 'fcat[]',
		'key'     => 'fcat',
		'options' => array_map(
			static function ( $t ) {
				return array( $t->slug, $t->name, $t->count );
			},
			$cat_terms
		),
	);
}

if ( ! ( $term && 'product_brand' === $term->taxonomy ) ) {
	$brands = ridgeline_brands();
	if ( $brands ) {
		$groups[] = array(
			'title'   => 'Brand',
			'name'    => 'fbrand[]',
			'key'     => 'fbrand',
			'options' => array_map(
				static function ( $t ) {
					return array( $t->slug, $t->name, $t->count );
				},
				$brands
			),
		);
	}
}

foreach ( wc_get_attribute_taxonomies() as $attribute ) {
	$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
	$values   = taxonomy_exists( $taxonomy ) ? get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) ) : array();
	if ( is_wp_error( $values ) || ! $values ) {
		continue;
	}
	$groups[] = array(
		'title'   => $attribute->attribute_label,
		'name'    => 'fattr[' . $taxonomy . '][]',
		'key'     => $taxonomy,
		'options' => array_map(
			static function ( $t ) {
				return array( $t->slug, $t->name, $t->count );
			},
			$values
		),
	);
}

// phpcs:disable WordPress.Security.NonceVerification
$min = isset( $_GET['min_price'] ) ? absint( $_GET['min_price'] ) : '';
$max = isset( $_GET['max_price'] ) ? absint( $_GET['max_price'] ) : '';
// phpcs:enable
?>
<aside class="rdg-filters" id="rdg-filters" aria-label="Filters" data-rdg-filters>
	<div class="rdg-filters__backdrop" data-rdg-filters-close></div>
	<form class="rdg-filters__panel" method="get" action="<?php echo esc_url( $action ); ?>" data-rdg-filter-form>
		<div class="rdg-filters__head">
			<h2 class="rdg-h3">Filters</h2>
			<button type="button" class="rdg-iconbtn" data-rdg-filters-close><?php echo ridgeline_icon( 'close', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text">Close filters</span></button>
		</div>
		<?php if ( is_search() ) : ?>
			<input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
			<input type="hidden" name="post_type" value="product">
		<?php endif; ?>
		<?php if ( isset( $_GET['orderby'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( sanitize_key( wp_unslash( $_GET['orderby'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>">
		<?php endif; ?>

		<div class="rdg-filters__body">
			<?php
			foreach ( $groups as $index => $group ) :
				$selected = isset( $chosen[ $group['key'] ] ) ? $chosen[ $group['key'] ] : ( isset( $attrs[ $group['key'] ] ) ? $attrs[ $group['key'] ] : array() );
				$long     = count( $group['options'] ) > 6;
				?>
				<fieldset class="rdg-facet<?php echo $long ? ' is-long' : ''; ?>">
					<legend class="rdg-facet__title"><?php echo esc_html( $group['title'] ); ?></legend>
					<ul class="rdg-facet__list">
						<?php foreach ( $group['options'] as $i => $option ) : ?>
							<li<?php echo ( $long && $i >= 6 && ! in_array( $option[0], $selected, true ) ) ? ' data-rdg-more hidden' : ''; ?>>
								<label class="rdg-check">
									<input type="checkbox" name="<?php echo esc_attr( $group['name'] ); ?>" value="<?php echo esc_attr( $option[0] ); ?>"<?php checked( in_array( $option[0], $selected, true ) ); ?>>
									<span class="rdg-check__box" aria-hidden="true"></span>
									<span class="rdg-check__label"><?php echo esc_html( $option[1] ); ?></span>
									<?php if ( null !== $option[2] ) : ?>
										<span class="rdg-facet__count"><?php echo (int) $option[2]; ?></span>
									<?php endif; ?>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php if ( $long ) : ?>
						<button type="button" class="rdg-link rdg-facet__more" data-rdg-facet-more aria-expanded="false">Show all <?php echo (int) count( $group['options'] ); ?></button>
					<?php endif; ?>
				</fieldset>
			<?php endforeach; ?>

			<fieldset class="rdg-facet">
				<legend class="rdg-facet__title">Price</legend>
				<div class="rdg-price-range">
					<div class="rdg-field">
						<label for="rdg-min-price">Min</label>
						<input id="rdg-min-price" type="number" inputmode="numeric" min="0" step="1" name="min_price" value="<?php echo esc_attr( $min ); ?>" placeholder="$0">
					</div>
					<div class="rdg-field">
						<label for="rdg-max-price">Max</label>
						<input id="rdg-max-price" type="number" inputmode="numeric" min="0" step="1" name="max_price" value="<?php echo esc_attr( $max ); ?>" placeholder="Any">
					</div>
				</div>
				<button type="submit" class="rdg-btn rdg-btn--outline rdg-btn--block">Apply price</button>
			</fieldset>
		</div>

		<div class="rdg-filters__foot">
			<a class="rdg-btn rdg-btn--ghost" href="<?php echo esc_url( is_search() ? add_query_arg( array( 's' => get_search_query(), 'post_type' => 'product' ), $action ) : $action ); ?>">Clear all</a>
			<button type="submit" class="rdg-btn rdg-btn--brand">Show <?php echo esc_html( number_format_i18n( $total ) ); ?> results</button>
		</div>
	</form>
</aside>
