<?php
/**
 * WooCommerce integration: product states, catalog filters, search, compare.
 *
 * @package RIDGELINE
 */

function ridgeline_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 640,
			'single_image_width'    => 1200,
			'product_grid'          => array( 'default_columns' => 3 ),
		)
	);
}
add_action( 'after_setup_theme', 'ridgeline_woocommerce_setup' );

/**
 * WooCommerce 9.6+ ships product_brand; register it only on older versions.
 */
function ridgeline_register_brand_taxonomy() {
	if ( ! post_type_exists( 'product' ) || taxonomy_exists( 'product_brand' ) ) {
		return;
	}
	register_taxonomy(
		'product_brand',
		'product',
		array(
			'labels'            => array( 'name' => 'Brands', 'singular_name' => 'Brand' ),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'public'            => true,
			'rewrite'           => array( 'slug' => 'brand' ),
		)
	);
}
add_action( 'init', 'ridgeline_register_brand_taxonomy', 20 );

/* The theme's templates replace these wrappers, the sidebar, and the default styles for layout. */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_filter(
	'woocommerce_enqueue_styles',
	static function ( $styles ) {
		unset( $styles['woocommerce-layout'], $styles['woocommerce-smallscreen'] );
		return $styles;
	}
);

function ridgeline_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
}

function ridgeline_compare_enabled() {
	return (bool) ridgeline_mod( 'compare_enabled' );
}

/* ---------------------------------------------------------------------------
 * Product state helpers
 * ------------------------------------------------------------------------- */

function ridgeline_brand( $product ) {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return null;
	}
	$terms = get_the_terms( $product->get_id(), 'product_brand' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

function ridgeline_is_call_for_price( $product ) {
	$key = ridgeline_mod( 'call_price_meta' );
	if ( $key && 'yes' === get_post_meta( $product->get_id(), $key, true ) ) {
		return true;
	}
	return '' === $product->get_price();
}

function ridgeline_low_stock_amount( $product ) {
	$amount = method_exists( $product, 'get_low_stock_amount' ) ? $product->get_low_stock_amount() : '';
	if ( '' === $amount || null === $amount ) {
		$amount = get_option( 'woocommerce_notify_low_stock_amount', 2 );
	}
	return max( 1, (int) $amount );
}

/**
 * Stock state: ok, low, back (backorder), out, or call (not tracked, no price).
 *
 * @return array{state:string,label:string,qty:?int}
 */
function ridgeline_stock( $product ) {
	$status = $product->get_stock_status();
	$qty    = $product->managing_stock() ? (int) $product->get_stock_quantity() : null;

	if ( 'outofstock' === $status ) {
		return array( 'state' => 'out', 'label' => 'Out of stock', 'qty' => 0 );
	}
	if ( 'onbackorder' === $status ) {
		return array( 'state' => 'back', 'label' => 'Backorder', 'qty' => $qty );
	}
	if ( null !== $qty && $qty <= ridgeline_low_stock_amount( $product ) ) {
		/* translators: %d: units left. */
		return array( 'state' => 'low', 'label' => sprintf( _n( 'Only %d left', 'Only %d left', $qty, 'ridgeline' ), $qty ), 'qty' => $qty );
	}
	if ( null === $qty && ridgeline_is_call_for_price( $product ) ) {
		return array( 'state' => 'call', 'label' => 'Call to confirm', 'qty' => null );
	}
	return array( 'state' => 'ok', 'label' => 'In stock', 'qty' => $qty );
}

function ridgeline_stock_badge( $product ) {
	$stock = ridgeline_stock( $product );
	return '<span class="rdg-stock rdg-stock--' . esc_attr( $stock['state'] ) . '">' . esc_html( $stock['label'] ) . '</span>';
}

/**
 * Price markup: "Call for price", a sale price with the old price, or the price.
 */
function ridgeline_price_html( $product, $class = 'rdg-price' ) {
	if ( ridgeline_is_call_for_price( $product ) ) {
		return '<p class="' . esc_attr( $class ) . ' ' . esc_attr( $class ) . '--call">Call for price</p>';
	}
	$sale = $product->is_on_sale() ? ' ' . $class . '--sale' : '';
	return '<p class="' . esc_attr( $class . $sale ) . '">' . wp_kses_post( $product->get_price_html() ) . '</p>';
}

function ridgeline_savings( $product ) {
	if ( ! $product->is_on_sale() || ! $product->is_type( 'simple' ) ) {
		return 0;
	}
	return max( 0, (float) $product->get_regular_price() - (float) $product->get_sale_price() );
}

function ridgeline_not_purchasable_when_call( $purchasable, $product ) {
	return ( $purchasable && ridgeline_is_call_for_price( $product ) ) ? false : $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'ridgeline_not_purchasable_when_call', 10, 2 );

/**
 * Replacement product from the TargetWeb `supersededByProductId` field.
 */
function ridgeline_replacement( $product ) {
	$id = absint( trim( (string) get_post_meta( $product->get_id(), 'supersededByProductId', true ) ) );
	if ( ! $id || $id === $product->get_id() ) {
		return null;
	}
	$replacement = wc_get_product( $id );
	return ( $replacement && 'publish' === $replacement->get_status() ) ? $replacement : null;
}

/**
 * Up to $limit visible attributes as label => value for cards and compare.
 */
function ridgeline_product_specs( $product, $limit = 0 ) {
	$specs = array();
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->get_visible() ) {
			continue;
		}
		if ( $attribute->is_taxonomy() ) {
			$values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
		} else {
			$values = $attribute->get_options();
		}
		$specs[ wc_attribute_label( $attribute->get_name() ) ] = implode( ', ', $values );
		if ( $limit && count( $specs ) >= $limit ) {
			break;
		}
	}
	return $specs;
}

/* ---------------------------------------------------------------------------
 * Search: SKU, variation SKU, brand, category, tag and attributes
 * ------------------------------------------------------------------------- */

function ridgeline_product_search_ids( $term ) {
	$term = trim( sanitize_text_field( $term ) );
	if ( '' === $term || ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$ids = wc_get_products(
		array(
			'status' => 'publish',
			's'      => $term,
			'limit'  => -1,
			'return' => 'ids',
		)
	);

	global $wpdb;
	$like     = '%' . $wpdb->esc_like( $term ) . '%';
	$meta_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT DISTINCT CASE WHEN p.post_type = 'product_variation' THEN p.post_parent ELSE p.ID END
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key IN ('_sku', '_product_attributes')
			AND pm.meta_value LIKE %s
			AND p.post_type IN ('product', 'product_variation')
			AND p.post_status = 'publish'",
			$like
		)
	);
	$ids = array_merge( $ids, $meta_ids );

	foreach ( ridgeline_term_matches( $term ) as $matched ) {
		$ids = array_merge( $ids, get_objects_in_term( $matched->term_id, $matched->taxonomy ) );
	}

	return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

/**
 * Category, tag, brand and attribute terms whose name contains the query.
 */
function ridgeline_term_matches( $term, $taxonomies = null ) {
	if ( null === $taxonomies ) {
		$taxonomies = array_filter(
			get_object_taxonomies( 'product', 'names' ),
			static function ( $taxonomy ) {
				return in_array( $taxonomy, array( 'product_cat', 'product_tag', 'product_brand' ), true ) || 0 === strpos( $taxonomy, 'pa_' );
			}
		);
	}
	if ( ! $taxonomies ) {
		return array();
	}
	$terms = get_terms(
		array(
			'taxonomy'   => array_values( $taxonomies ),
			'hide_empty' => true,
			'name__like' => $term,
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

/* ---------------------------------------------------------------------------
 * Catalog query: filters, per page, product search
 * ------------------------------------------------------------------------- */

/**
 * Sanitized slugs from a filter query parameter.
 */
function ridgeline_filter_values( $key ) {
	if ( empty( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return array();
	}
	$raw = wp_unslash( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
	$raw = is_array( $raw ) ? $raw : explode( ',', (string) $raw );
	return array_values( array_filter( array_map( 'sanitize_title', $raw ) ) );
}

/**
 * Chosen attribute filters as taxonomy => slugs.
 */
function ridgeline_attr_filters() {
	$chosen = array();
	if ( empty( $_GET['fattr'] ) || ! is_array( $_GET['fattr'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return $chosen;
	}
	foreach ( wp_unslash( $_GET['fattr'] ) as $taxonomy => $slugs ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$taxonomy = sanitize_key( $taxonomy );
		if ( 0 !== strpos( $taxonomy, 'pa_' ) || ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$slugs = array_values( array_filter( array_map( 'sanitize_title', (array) $slugs ) ) );
		if ( $slugs ) {
			$chosen[ $taxonomy ] = $slugs;
		}
	}
	return $chosen;
}

/**
 * Tax and meta query clauses for the active filters.
 *
 * @return array{tax:array,meta:array}
 */
function ridgeline_filter_clauses( $cats, $brands, $attrs, $stock ) {
	$tax = array();
	if ( $cats ) {
		$tax[] = array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $cats );
	}
	if ( $brands && taxonomy_exists( 'product_brand' ) ) {
		$tax[] = array( 'taxonomy' => 'product_brand', 'field' => 'slug', 'terms' => $brands );
	}
	foreach ( $attrs as $taxonomy => $slugs ) {
		$tax[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $slugs );
	}

	$meta = array();
	if ( $stock ) {
		$or        = array( 'relation' => 'OR' );
		$threshold = max( 1, (int) get_option( 'woocommerce_notify_low_stock_amount', 2 ) );
		if ( in_array( 'instock', $stock, true ) ) {
			$or[] = array( 'key' => '_stock_status', 'value' => 'instock' );
		} elseif ( in_array( 'low', $stock, true ) ) {
			$or[] = array(
				'relation' => 'AND',
				array( 'key' => '_stock_status', 'value' => 'instock' ),
				array( 'key' => '_manage_stock', 'value' => 'yes' ),
				array( 'key' => '_stock', 'value' => $threshold, 'compare' => '<=', 'type' => 'NUMERIC' ),
			);
		}
		if ( in_array( 'onbackorder', $stock, true ) ) {
			$or[] = array( 'key' => '_stock_status', 'value' => 'onbackorder' );
		}
		if ( in_array( 'outofstock', $stock, true ) ) {
			$or[] = array( 'key' => '_stock_status', 'value' => 'outofstock' );
		}
		if ( count( $or ) > 1 ) {
			$meta[] = $or;
		}
	}
	return array( 'tax' => $tax, 'meta' => $meta );
}

function ridgeline_is_product_search( $query ) {
	if ( ! $query->is_search() ) {
		return false;
	}
	$type = $query->get( 'post_type' );
	return 'product' === $type || ( is_array( $type ) && array( 'product' ) === $type );
}

/**
 * @param WP_Query $query Query.
 */
function ridgeline_catalog_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! function_exists( 'wc_get_products' ) ) {
		return;
	}
	$is_catalog = $query->is_post_type_archive( 'product' ) || $query->is_tax( get_object_taxonomies( 'product' ) ) || ridgeline_is_product_search( $query );
	if ( ! $is_catalog ) {
		return;
	}

	if ( ridgeline_is_product_search( $query ) ) {
		$ids = ridgeline_product_search_ids( $query->get( 's' ) );
		$query->set( 'post__in', $ids ? $ids : array( 0 ) );
		$query->set( 'ridgeline_product_search', 1 );
	}

	$clauses = ridgeline_filter_clauses(
		ridgeline_filter_values( 'fcat' ),
		ridgeline_filter_values( 'fbrand' ),
		ridgeline_attr_filters(),
		ridgeline_filter_values( 'fstock' )
	);
	if ( $clauses['tax'] ) {
		$tax = $query->get( 'tax_query' );
		$tax = is_array( $tax ) ? $tax : array();
		$query->set( 'tax_query', array_merge( $tax, array( 'relation' => 'AND' ), $clauses['tax'] ) );
	}
	if ( $clauses['meta'] ) {
		$meta = $query->get( 'meta_query' );
		$meta = is_array( $meta ) ? $meta : array();
		$query->set( 'meta_query', array_merge( $meta, $clauses['meta'] ) );
	}
}
add_action( 'pre_get_posts', 'ridgeline_catalog_query', 20 );

/**
 * Product search ids already contain the matches; drop the core text search.
 */
function ridgeline_drop_core_search( $search, $query ) {
	return $query->get( 'ridgeline_product_search' ) ? '' : $search;
}
add_filter( 'posts_search', 'ridgeline_drop_core_search', 10, 2 );
add_filter( 'posts_search_orderby', 'ridgeline_drop_core_search', 10, 2 );

add_filter(
	'loop_shop_per_page',
	static function () {
		return max( 1, (int) ridgeline_mod( 'shop_per_page' ) );
	},
	20
);

add_filter(
	'woocommerce_catalog_orderby',
	static function () {
		return array(
			'menu_order' => 'Featured',
			'price'      => 'Price: low to high',
			'price-desc' => 'Price: high to low',
			'date'       => 'Newest',
			'popularity' => 'Best selling',
			'title'      => 'Name: A–Z',
		);
	}
);

/**
 * Active filter chips: label and the URL that removes it.
 */
function ridgeline_active_filters() {
	$chips = array();
	$base  = remove_query_arg( array( 'paged', 'product-page' ) );
	$base  = preg_replace( '#/page/\d+/?#', '/', $base );

	foreach ( array( 'fcat' => 'product_cat', 'fbrand' => 'product_brand' ) as $param => $taxonomy ) {
		foreach ( ridgeline_filter_values( $param ) as $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );
			if ( $term ) {
				$rest    = array_diff( ridgeline_filter_values( $param ), array( $slug ) );
				$chips[] = array( 'label' => $term->name, 'url' => add_query_arg( $param, $rest ? $rest : false, remove_query_arg( $param, $base ) ) );
			}
		}
	}
	$stock_labels = array( 'instock' => 'In stock', 'low' => 'Low stock', 'onbackorder' => 'Backorder', 'outofstock' => 'Out of stock' );
	foreach ( ridgeline_filter_values( 'fstock' ) as $slug ) {
		if ( isset( $stock_labels[ $slug ] ) ) {
			$rest    = array_diff( ridgeline_filter_values( 'fstock' ), array( $slug ) );
			$chips[] = array( 'label' => $stock_labels[ $slug ], 'url' => add_query_arg( 'fstock', $rest ? $rest : false, remove_query_arg( 'fstock', $base ) ) );
		}
	}
	$attrs = ridgeline_attr_filters();
	foreach ( $attrs as $taxonomy => $slugs ) {
		foreach ( $slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );
			if ( ! $term ) {
				continue;
			}
			$next = $attrs;
			$next[ $taxonomy ] = array_values( array_diff( $slugs, array( $slug ) ) );
			$next = array_filter( $next );
			$chips[] = array(
				'label' => wc_attribute_label( $taxonomy ) . ': ' . $term->name,
				'url'   => add_query_arg( 'fattr', $next ? $next : false, remove_query_arg( 'fattr', $base ) ),
			);
		}
	}
	// phpcs:disable WordPress.Security.NonceVerification
	if ( isset( $_GET['min_price'] ) || isset( $_GET['max_price'] ) ) {
		$min     = isset( $_GET['min_price'] ) ? wc_price( (float) $_GET['min_price'], array( 'decimals' => 0 ) ) : '';
		$max     = isset( $_GET['max_price'] ) ? wc_price( (float) $_GET['max_price'], array( 'decimals' => 0 ) ) : '';
		$chips[] = array( 'label' => wp_strip_all_tags( trim( $min . ' – ' . $max, ' –' ) ), 'url' => remove_query_arg( array( 'min_price', 'max_price' ), $base ) );
	}
	// phpcs:enable
	return $chips;
}

/* ---------------------------------------------------------------------------
 * AJAX: live search, compare data, finder count
 * ------------------------------------------------------------------------- */

function ridgeline_product_summary( $product, $image_size = 'woocommerce_thumbnail' ) {
	$brand = ridgeline_brand( $product );
	$stock = ridgeline_stock( $product );
	$image = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), $image_size ) : '';
	return array(
		'id'         => $product->get_id(),
		'name'       => $product->get_name(),
		'url'        => get_permalink( $product->get_id() ),
		'brand'      => $brand ? $brand->name : '',
		'sku'        => $product->get_sku(),
		'image'      => $image ? $image : '',
		'price'      => ridgeline_is_call_for_price( $product ) ? 'Call for price' : html_entity_decode( wp_strip_all_tags( $product->get_price_html() ), ENT_QUOTES, 'UTF-8' ),
		'priceValue' => ridgeline_is_call_for_price( $product ) ? null : (float) $product->get_price(),
		'stock'      => $stock['label'],
		'stockState' => $stock['state'],
		'stockQty'   => $stock['qty'],
	);
}

function ridgeline_ajax_search() {
	check_ajax_referer( 'ridgeline_ajax', 'nonce' );
	$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
	if ( mb_strlen( trim( $term ) ) < 2 || ! function_exists( 'wc_get_products' ) ) {
		wp_send_json_success( array( 'products' => array(), 'terms' => array(), 'total' => 0 ) );
	}
	$ids      = ridgeline_product_search_ids( $term );
	$products = $ids ? wc_get_products( array( 'status' => 'publish', 'include' => $ids, 'limit' => 6 ) ) : array();

	$terms = array();
	foreach ( ridgeline_term_matches( $term, array_filter( array( 'product_cat', taxonomy_exists( 'product_brand' ) ? 'product_brand' : '' ) ) ) as $match ) {
		$link = get_term_link( $match );
		if ( ! is_wp_error( $link ) ) {
			$terms[] = array(
				'name'  => $match->name,
				'type'  => 'product_brand' === $match->taxonomy ? 'Brand' : 'Category',
				'count' => (int) $match->count,
				'url'   => $link,
			);
		}
		if ( count( $terms ) >= 6 ) {
			break;
		}
	}

	wp_send_json_success(
		array(
			'products' => array_map( 'ridgeline_product_summary', $products ),
			'terms'    => $terms,
			'total'    => count( $ids ),
			'url'      => add_query_arg( array( 's' => $term, 'post_type' => 'product' ), home_url( '/' ) ),
		)
	);
}
add_action( 'wp_ajax_ridgeline_search', 'ridgeline_ajax_search' );
add_action( 'wp_ajax_nopriv_ridgeline_search', 'ridgeline_ajax_search' );

function ridgeline_ajax_compare() {
	check_ajax_referer( 'ridgeline_ajax', 'nonce' );
	$ids = isset( $_GET['ids'] ) ? array_slice( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['ids'] ) ) ) ) ), 0, 4 ) : array();
	if ( ! $ids || ! function_exists( 'wc_get_products' ) ) {
		wp_send_json_success( array( 'products' => array() ) );
	}
	$products = wc_get_products( array( 'status' => 'publish', 'include' => $ids, 'limit' => 4, 'orderby' => 'include' ) );
	$data     = array();
	foreach ( $products as $product ) {
		$row          = ridgeline_product_summary( $product, 'woocommerce_thumbnail' );
		$row['specs'] = ridgeline_product_specs( $product );
		$data[]       = $row;
	}
	wp_send_json_success( array( 'products' => $data ) );
}
add_action( 'wp_ajax_ridgeline_compare', 'ridgeline_ajax_compare' );
add_action( 'wp_ajax_nopriv_ridgeline_compare', 'ridgeline_ajax_compare' );

/**
 * Number of published, visible products matching the homepage finder.
 */
function ridgeline_finder_count( $cat = '', $brand = '', $instock = false ) {
	$clauses = ridgeline_filter_clauses( $cat ? array( $cat ) : array(), $brand ? array( $brand ) : array(), array(), $instock ? array( 'instock' ) : array() );
	$tax     = $clauses['tax'];
	$tax[]   = array( 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => array( 'exclude-from-catalog' ), 'operator' => 'NOT IN' );
	$query   = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'tax_query'      => $tax, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_query'     => $clauses['meta'], // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return (int) $query->found_posts;
}

function ridgeline_ajax_finder() {
	check_ajax_referer( 'ridgeline_ajax', 'nonce' );
	$cat     = isset( $_GET['cat'] ) ? sanitize_title( wp_unslash( $_GET['cat'] ) ) : '';
	$brand   = isset( $_GET['brand'] ) ? sanitize_title( wp_unslash( $_GET['brand'] ) ) : '';
	$instock = ! empty( $_GET['instock'] );
	wp_send_json_success( array( 'count' => ridgeline_finder_count( $cat, $brand, $instock ) ) );
}
add_action( 'wp_ajax_ridgeline_finder', 'ridgeline_ajax_finder' );
add_action( 'wp_ajax_nopriv_ridgeline_finder', 'ridgeline_ajax_finder' );

/* ---------------------------------------------------------------------------
 * Header cart
 * ------------------------------------------------------------------------- */

function ridgeline_cart_link() {
	if ( ! function_exists( 'wc_get_cart_url' ) ) {
		return;
	}
	$count = ( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	/* translators: %d: items in cart. */
	$label = sprintf( _n( 'Cart, %d item', 'Cart, %d items', $count, 'ridgeline' ), $count );
	printf(
		'<a class="rdg-cart" href="%1$s" aria-label="%2$s">%3$s<span class="rdg-cart__text">Cart</span><span class="rdg-cart__count"%4$s>%5$d</span></a>',
		esc_url( wc_get_cart_url() ),
		esc_attr( $label ),
		ridgeline_icon( 'cart' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$count ? '' : ' hidden',
		(int) $count
	);
}

add_filter(
	'woocommerce_add_to_cart_fragments',
	static function ( $fragments ) {
		ob_start();
		ridgeline_cart_link();
		$fragments['a.rdg-cart'] = ob_get_clean();
		return $fragments;
	}
);

/**
 * Card / loop add-to-cart button classes.
 */
add_filter(
	'woocommerce_loop_add_to_cart_args',
	static function ( $args ) {
		$args['class'] = trim( $args['class'] . ' rdg-btn rdg-btn--brand' );
		return $args;
	}
);

/* Breadcrumb defaults for every WooCommerce breadcrumb call. */
add_filter(
	'woocommerce_breadcrumb_defaults',
	static function ( $defaults ) {
		$defaults['delimiter']   = '<span class="rdg-crumbs__sep" aria-hidden="true">/</span>';
		$defaults['wrap_before'] = '<nav class="rdg-crumbs" aria-label="Breadcrumb"><div class="rdg-wrap">';
		$defaults['wrap_after']  = '</div></nav>';
		return $defaults;
	}
);

/**
 * Locations shown when the TargetWeb stock widget does not load.
 */
function ridgeline_locations() {
	$out = array();
	foreach ( ridgeline_rows( ridgeline_mod( 'locations' ) ) as $row ) {
		$out[] = array( 'name' => $row[0], 'address' => isset( $row[1] ) ? $row[1] : '' );
	}
	return $out;
}

/**
 * The TargetWeb CRM lead form is active and enabled.
 */
function ridgeline_crm_enabled() {
	if ( ! class_exists( 'TW_CRM_Settings' ) ) {
		return false;
	}
	$settings = TW_CRM_Settings::get_settings();
	return ! empty( $settings['enabled'] ) && ! empty( $settings['cta_show'] );
}

/**
 * CTA label configured by the TargetWeb CRM plugin.
 */
function ridgeline_crm_cta_text() {
	if ( ! class_exists( 'TW_CRM_Settings' ) ) {
		return 'Request information';
	}
	$settings = TW_CRM_Settings::get_settings();
	$text     = isset( $settings['cta_text'] ) ? sanitize_text_field( $settings['cta_text'] ) : '';
	return $text ? $text : 'Request information';
}
