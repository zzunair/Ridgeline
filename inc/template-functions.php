<?php
/**
 * Template helpers.
 *
 * @package RIDGELINE
 */

/**
 * Non-empty trimmed lines of a textarea setting.
 */
function ridgeline_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ), 'strlen' ) );
}

/**
 * Lines split on "|" into trimmed cells.
 */
function ridgeline_rows( $text ) {
	$rows = array();
	foreach ( ridgeline_lines( $text ) as $line ) {
		$rows[] = array_map( 'trim', explode( '|', $line ) );
	}
	return $rows;
}

/**
 * Settings store links as "/path/"; resolve those against the site URL.
 */
function ridgeline_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	if ( '/' === $url[0] && ( ! isset( $url[1] ) || '/' !== $url[1] ) ) {
		return home_url( $url );
	}
	return $url;
}

function ridgeline_tel( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', (string) $phone );
}

function ridgeline_sms( $phone ) {
	return 'sms:' . preg_replace( '/[^0-9+]/', '', (string) $phone );
}

function ridgeline_address() {
	return trim( ridgeline_mod( 'dealer_street' ) . ', ' . ridgeline_mod( 'dealer_city' ), ', ' );
}

function ridgeline_directions_url() {
	return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( ridgeline_address() );
}

/**
 * Inline SVG icon. All icons are decorative; callers supply the label.
 */
function ridgeline_icon( $name, $size = 20 ) {
	$paths = array(
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
		'cart'     => '<path d="M3 4h2l2.4 11h11L21 8H6.2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/>',
		'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
		'menu'     => '<path d="M3 6h18M3 12h18M3 18h18"/>',
		'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'phone'    => '<path d="M5 3h4l2 5-2.5 1.5a11 11 0 006 6L16 13l5 2v4a2 2 0 01-2 2A17 17 0 013 5a2 2 0 012-2z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="M3 7l9 6 9-6"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'pin'      => '<path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/>',
		'chevron'  => '<path d="M6 9l6 6 6-6"/>',
		'left'     => '<path d="M15 6l-6 6 6 6"/>',
		'right'    => '<path d="M9 6l6 6-6 6"/>',
		'pause'    => '<path d="M8 5v14M16 5v14"/>',
		'play'     => '<path d="M7 5l12 7-12 7z"/>',
		'grid'     => '<rect x="4" y="4" width="7" height="7"/><rect x="13" y="4" width="7" height="7"/><rect x="4" y="13" width="7" height="7"/><rect x="13" y="13" width="7" height="7"/>',
		'list'     => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'filter'   => '<path d="M4 5h16l-6 8v6l-4-2v-4z"/>',
		'expand'   => '<path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>',
		'check'    => '<path d="M5 12l5 5 9-10"/>',
		'star'     => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z" fill="currentColor"/>',
		'link'     => '<path d="M10 14a4 4 0 005.7 0l3-3a4 4 0 00-5.7-5.7l-1 1M14 10a4 4 0 00-5.7 0l-3 3a4 4 0 005.7 5.7l1-1"/>',
		'chat'     => '<path d="M4 5h16v11H8l-4 4z"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="rdg-icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
		(int) $size,
		$paths[ $name ]
	);
}

/**
 * Social links that have a URL set.
 */
function ridgeline_social_links() {
	$links = array();
	$map   = array(
		'facebook'  => array( 'f', 'Facebook' ),
		'instagram' => array( 'ig', 'Instagram' ),
		'youtube'   => array( 'yt', 'YouTube' ),
	);
	foreach ( $map as $key => $meta ) {
		$url = ridgeline_mod( 'social_' . $key );
		if ( $url ) {
			$links[] = array( 'url' => $url, 'short' => $meta[0], 'label' => $meta[1] );
		}
	}
	return $links;
}

function ridgeline_social_list( $class ) {
	$links = ridgeline_social_links();
	if ( ! $links ) {
		return;
	}
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $links as $link ) {
		printf(
			'<li><a href="%1$s" target="_blank" rel="noopener"><span aria-hidden="true">%2$s</span><span class="screen-reader-text">%3$s (opens in a new tab)</span></a></li>',
			esc_url( $link['url'] ),
			esc_html( $link['short'] ),
			esc_html( $link['label'] )
		);
	}
	echo '</ul>';
}

/**
 * Logo: the custom logo image when set, otherwise the letter mark and name.
 */
function ridgeline_logo() {
	$home = esc_url( home_url( '/' ) );
	if ( has_custom_logo() ) {
		echo '<div class="rdg-logo rdg-logo--image">' . get_custom_logo() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}
	$name   = ridgeline_mod( 'dealer_name' );
	$first  = strtok( $name, ' ' );
	printf(
		'<a class="rdg-logo" href="%1$s" rel="home"><span class="rdg-logo__mark" aria-hidden="true">%2$s</span><span class="rdg-logo__text"><span class="rdg-logo__name">%3$s</span><span class="rdg-logo__sub">%4$s</span></span><span class="screen-reader-text">%5$s, home</span></a>',
		$home,
		esc_html( ridgeline_mod( 'header_logo_letter' ) ),
		esc_html( $first ? $first : $name ),
		esc_html( ridgeline_mod( 'dealer_tagline' ) ),
		esc_html( $name )
	);
}

/**
 * Image or striped placeholder.
 */
function ridgeline_media( $url, $alt = '', $class = '' ) {
	if ( $url ) {
		$id = attachment_url_to_postid( $url );
		if ( $id ) {
			return wp_get_attachment_image( $id, 'large', false, array( 'class' => $class, 'alt' => $alt, 'loading' => 'lazy' ) );
		}
		return sprintf( '<img class="%1$s" src="%2$s" alt="%3$s" loading="lazy">', esc_attr( $class ), esc_url( $url ), esc_attr( $alt ) );
	}
	return '<span class="rdg-ph ' . esc_attr( $class ) . '" aria-hidden="true"></span>';
}

/**
 * Map iframe when an embed URL is set.
 */
function ridgeline_map( $class = 'rdg-map' ) {
	$src = ridgeline_mod( 'map_embed' );
	if ( $src ) {
		printf(
			'<div class="%1$s"><iframe src="%2$s" title="%3$s" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>',
			esc_attr( $class ),
			esc_url( $src ),
			esc_attr( sprintf( 'Map to %s', ridgeline_mod( 'dealer_name' ) ) )
		);
		return;
	}
	printf(
		'<div class="%1$s %1$s--ph"><a class="rdg-btn rdg-btn--outline" href="%2$s" target="_blank" rel="noopener">%3$s<span class="screen-reader-text"> (opens in a new tab)</span></a></div>',
		esc_attr( $class ),
		esc_url( ridgeline_directions_url() ),
		esc_html__( 'Get directions', 'ridgeline' )
	);
}

/**
 * Breadcrumb trail. WooCommerce pages use its breadcrumb with theme markup.
 */
function ridgeline_breadcrumb() {
	if ( function_exists( 'woocommerce_breadcrumb' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		woocommerce_breadcrumb(
			array(
				'delimiter'   => '<span class="rdg-crumbs__sep" aria-hidden="true">/</span>',
				'wrap_before' => '<nav class="rdg-crumbs" aria-label="Breadcrumb"><div class="rdg-wrap">',
				'wrap_after'  => '</div></nav>',
				'home'        => 'Home',
			)
		);
		return;
	}
	$items = array( '<a href="' . esc_url( home_url( '/' ) ) . '">Home</a>' );
	if ( is_singular( 'post' ) || is_category() || is_tag() || is_home() ) {
		$blog = get_option( 'page_for_posts' );
		if ( $blog && ! is_home() ) {
			$items[] = '<a href="' . esc_url( get_permalink( $blog ) ) . '">' . esc_html( get_the_title( $blog ) ) . '</a>';
		}
	}
	if ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = '<a href="' . esc_url( get_permalink( $ancestor ) ) . '">' . esc_html( get_the_title( $ancestor ) ) . '</a>';
		}
	}
	$items[] = '<span aria-current="page">' . esc_html( wp_strip_all_tags( ridgeline_page_title() ) ) . '</span>';
	echo '<nav class="rdg-crumbs" aria-label="Breadcrumb"><div class="rdg-wrap">' . implode( '<span class="rdg-crumbs__sep" aria-hidden="true">/</span>', $items ) . '</div></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

function ridgeline_page_title() {
	if ( is_home() ) {
		$blog = get_option( 'page_for_posts' );
		return $blog ? get_the_title( $blog ) : __( 'News', 'ridgeline' );
	}
	if ( is_search() ) {
		/* translators: %s: search query. */
		return sprintf( __( 'Results for “%s”', 'ridgeline' ), get_search_query() );
	}
	if ( is_404() ) {
		return __( 'Page not found', 'ridgeline' );
	}
	if ( is_archive() ) {
		return get_the_archive_title();
	}
	return get_the_title();
}

/**
 * Body classes.
 */
function ridgeline_body_classes( $classes ) {
	if ( ridgeline_mod( 'header_sticky' ) ) {
		$classes[] = 'rdg-sticky';
	}
	if ( is_singular() && ! is_front_page() ) {
		$classes[] = 'rdg-singular';
	}
	return $classes;
}
add_filter( 'body_class', 'ridgeline_body_classes' );

function ridgeline_archive_title( $title ) {
	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false );
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'ridgeline_archive_title' );
