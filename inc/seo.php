<?php
/**
 * SEO used only when a dedicated SEO plugin is not active.
 *
 * @package RIDGELINE
 */

function ridgeline_seo_plugin_active() {
	return (bool) (
		defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'SLIM_SEO_VER' )
		|| function_exists( 'the_seo_framework' )
	);
}

function ridgeline_seo_trim( $text, $max = 160 ) {
	$text = html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' );
	$text = wp_strip_all_tags( strip_shortcodes( $text ), true );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	if ( '' === $text || mb_strlen( $text ) <= $max ) {
		return $text;
	}
	$cut   = mb_substr( $text, 0, $max - 1 );
	$space = mb_strrpos( $cut, ' ' );
	if ( false !== $space && $space > 40 ) {
		$cut = mb_substr( $cut, 0, $space );
	}
	return rtrim( $cut, ' ,.;:-' ) . '…';
}

function ridgeline_is_wc( $fn ) {
	return function_exists( $fn ) && call_user_func( $fn );
}

function ridgeline_seo_description() {
	$text = '';
	if ( is_front_page() ) {
		$text = ridgeline_mod( 'seo_home_description' );
		if ( ! $text ) {
			$text = ridgeline_mod( 'dealer_about' );
		}
	} elseif ( ridgeline_is_wc( 'is_product' ) ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product ) {
			$text = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
			if ( ! ridgeline_seo_trim( $text ) ) {
				$text = $product->get_name();
			}
		}
	} elseif ( ridgeline_is_wc( 'is_shop' ) && ! is_search() ) {
		$text = str_replace( '{count}', (string) wp_count_posts( 'product' )->publish, ridgeline_mod( 'shop_intro' ) );
	} elseif ( ridgeline_is_wc( 'is_product_taxonomy' ) ) {
		$term = get_queried_object();
		$text = term_description();
		if ( ! ridgeline_seo_trim( $text ) && $term ) {
			$text = sprintf( 'Shop %s at %s.', $term->name, get_bloginfo( 'name' ) );
		}
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		if ( $post && has_excerpt( $post ) ) {
			$text = get_the_excerpt( $post );
		} elseif ( $post && ! empty( $post->post_content ) ) {
			$text = $post->post_content;
		}
		if ( $post && ! ridgeline_seo_trim( $text ) && 'page' === $post->post_type ) {
			$map = array(
				'about'     => 'about_lead',
				'contact'   => 'contact_intro',
				'service'   => 'service_intro',
				'financing' => 'fin_intro',
			);
			if ( isset( $map[ $post->post_name ] ) ) {
				$text = ridgeline_mod( $map[ $post->post_name ] );
			}
		}
	}
	if ( ! ridgeline_seo_trim( $text ) ) {
		$text = get_bloginfo( 'description' );
	}
	return ridgeline_seo_trim( $text );
}

function ridgeline_seo_canonical_url() {
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	$url   = '';
	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_singular() ) {
		$url = get_permalink( get_queried_object_id() );
	} elseif ( ridgeline_is_wc( 'is_shop' ) && ! is_search() ) {
		$url = ridgeline_shop_url();
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$link = get_term_link( get_queried_object() );
		$url  = is_wp_error( $link ) ? '' : $link;
	} elseif ( is_home() ) {
		$blog = get_option( 'page_for_posts' );
		$url  = $blog ? get_permalink( $blog ) : home_url( '/' );
	}
	if ( $url && $paged > 1 && ! is_singular() ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' );
	}
	return $url;
}

function ridgeline_seo_image() {
	$attachment_id = 0;
	if ( ridgeline_is_wc( 'is_product' ) ) {
		$product       = wc_get_product( get_queried_object_id() );
		$attachment_id = $product ? (int) $product->get_image_id() : 0;
	} elseif ( is_singular() && has_post_thumbnail() ) {
		$attachment_id = (int) get_post_thumbnail_id();
	}
	if ( $attachment_id ) {
		$src = wp_get_attachment_image_src( $attachment_id, 'large' );
		if ( $src ) {
			return array( $src[0], (int) $src[1], (int) $src[2] );
		}
	}
	if ( is_front_page() && ridgeline_mod( 'hero1_image' ) ) {
		return array( ridgeline_mod( 'hero1_image' ), 0, 0 );
	}
	if ( ridgeline_mod( 'seo_share_image' ) ) {
		return array( ridgeline_mod( 'seo_share_image' ), 0, 0 );
	}
	$logo = get_theme_mod( 'custom_logo' );
	if ( $logo ) {
		$src = wp_get_attachment_image_src( $logo, 'full' );
		if ( $src ) {
			return array( $src[0], (int) $src[1], (int) $src[2] );
		}
	}
	return null;
}

function ridgeline_print_json_ld( $data ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}

function ridgeline_seo_head() {
	if ( ridgeline_seo_plugin_active() || is_404() || is_admin() ) {
		return;
	}
	$description = ridgeline_seo_description();
	$canonical   = ridgeline_seo_canonical_url();
	$image       = ridgeline_seo_image();
	$is_product  = ridgeline_is_wc( 'is_product' );
	$type        = $is_product ? 'product' : ( is_singular( 'post' ) ? 'article' : 'website' );

	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
	}
	if ( $canonical && ! is_singular() ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
	}

	$og = array(
		'og:locale'    => get_locale(),
		'og:type'      => $type,
		'og:site_name' => get_bloginfo( 'name' ),
		'og:title'     => wp_get_document_title(),
		'og:url'       => $canonical,
	);
	if ( $description ) {
		$og['og:description'] = $description;
	}
	if ( $image ) {
		$og['og:image'] = $image[0];
		if ( $image[1] && $image[2] ) {
			$og['og:image:width']  = (string) $image[1];
			$og['og:image:height'] = (string) $image[2];
		}
	}
	$product = $is_product ? wc_get_product( get_queried_object_id() ) : null;
	if ( $product && ! ridgeline_is_call_for_price( $product ) ) {
		$og['product:price:amount']   = wc_format_decimal( $product->get_price(), wc_get_price_decimals() );
		$og['product:price:currency'] = get_woocommerce_currency();
		$og['product:availability']   = $product->is_in_stock() ? 'in stock' : 'out of stock';
	}
	foreach ( $og as $property => $content ) {
		if ( '' !== (string) $content ) {
			echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
		}
	}
	echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( wp_get_document_title() ) . '" />' . "\n";
	if ( $description ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n";
	}
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image[0] ) . '" />' . "\n";
	}

	if ( is_front_page() ) {
		ridgeline_print_json_ld( ridgeline_seo_business_schema() );
		ridgeline_print_json_ld(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'WebSite',
				'name'            => get_bloginfo( 'name' ),
				'url'             => home_url( '/' ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => home_url( '/?post_type=product&s={search_term_string}' ),
					'query-input' => 'required name=search_term_string',
				),
			)
		);
	}

	if ( $product ) {
		ridgeline_print_json_ld( ridgeline_seo_product_schema( $product ) );
	}
}
add_action( 'wp_head', 'ridgeline_seo_head', 2 );

function ridgeline_seo_business_schema() {
	$type   = preg_replace( '/[^A-Za-z]/', '', (string) ridgeline_mod( 'seo_business_type' ) );
	$schema = array(
		'@context'  => 'https://schema.org',
		'@type'     => $type ? $type : 'Store',
		'@id'       => home_url( '/#business' ),
		'name'      => ridgeline_mod( 'dealer_name' ),
		'url'       => home_url( '/' ),
		'telephone' => ridgeline_mod( 'dealer_phone' ),
		'email'     => ridgeline_mod( 'dealer_email' ),
	);
	$image = ridgeline_seo_image();
	if ( $image ) {
		$schema['image'] = $image[0];
	}
	$street = ridgeline_mod( 'dealer_street' );
	if ( $street ) {
		$postal = array( '@type' => 'PostalAddress', 'streetAddress' => $street );
		if ( preg_match( '/^(.+?),\s*([A-Za-z .]+?)\s+([A-Za-z0-9 -]{3,10})$/', ridgeline_mod( 'dealer_city' ), $m ) ) {
			$postal['addressLocality'] = trim( $m[1] );
			$postal['addressRegion']   = trim( $m[2] );
			$postal['postalCode']      = trim( $m[3] );
		}
		$schema['address'] = $postal;
	}
	$profiles = wp_list_pluck( ridgeline_social_links(), 'url' );
	if ( $profiles ) {
		$schema['sameAs'] = array_values( $profiles );
	}
	return array_filter( $schema );
}

function ridgeline_seo_product_schema( $product ) {
	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => $product->get_name(),
		'url'         => get_permalink( $product->get_id() ),
		'description' => ridgeline_seo_trim( $product->get_short_description() ? $product->get_short_description() : $product->get_description(), 300 ),
		'sku'         => $product->get_sku(),
	);
	$image_id = $product->get_image_id();
	if ( $image_id ) {
		$schema['image'] = wp_get_attachment_image_url( $image_id, 'large' );
	}
	$brand = ridgeline_brand( $product );
	if ( $brand ) {
		$schema['brand'] = array( '@type' => 'Brand', 'name' => $brand->name );
	}
	if ( ! ridgeline_is_call_for_price( $product ) ) {
		$schema['offers'] = array(
			'@type'         => 'Offer',
			'priceCurrency' => get_woocommerce_currency(),
			'price'         => wc_format_decimal( $product->get_price(), wc_get_price_decimals() ),
			'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'url'           => get_permalink( $product->get_id() ),
		);
	}
	return array_filter( $schema );
}

/**
 * Keep search results, filtered catalog views and private store pages out of the index.
 */
function ridgeline_seo_robots( $robots ) {
	if ( ridgeline_seo_plugin_active() ) {
		return $robots;
	}
	$filtered = false;
	foreach ( array( 'fcat', 'fbrand', 'fstock', 'fattr', 'min_price', 'max_price', 'orderby' ) as $param ) {
		if ( isset( $_GET[ $param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$filtered = true;
			break;
		}
	}
	$private = ridgeline_is_wc( 'is_cart' ) || ridgeline_is_wc( 'is_checkout' ) || ridgeline_is_wc( 'is_account_page' );
	if ( is_search() || $filtered || $private ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'ridgeline_seo_robots' );

function ridgeline_disable_wc_product_schema( $markup ) {
	return ridgeline_seo_plugin_active() ? $markup : array();
}
add_filter( 'woocommerce_structured_data_product', 'ridgeline_disable_wc_product_schema' );
