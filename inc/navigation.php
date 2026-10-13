<?php
/**
 * Main navigation, mega menus and footer link columns.
 *
 * @package RIDGELINE
 */

/**
 * Top-level product categories with products, most stocked first.
 */
function ridgeline_top_categories( $limit = 12 ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$uncategorized = (int) get_option( 'default_product_cat' );
	$terms         = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => $uncategorized ? array( $uncategorized ) : array(),
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $limit,
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

function ridgeline_brands( $limit = 0 ) {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return array();
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_brand',
			'hide_empty' => true,
			'orderby'    => 'name',
			'number'     => $limit,
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Mega menu panel of terms with counts.
 */
function ridgeline_mega( $id, $label, $terms, $all_url, $all_label ) {
	if ( ! $terms ) {
		return;
	}
	?>
	<li class="rdg-nav__item rdg-nav__item--mega">
		<button type="button" class="rdg-nav__link rdg-nav__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $id ); ?>">
			<?php echo esc_html( $label ); ?><?php echo ridgeline_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<div class="rdg-mega" id="<?php echo esc_attr( $id ); ?>" hidden>
			<div class="rdg-wrap">
				<ul class="rdg-mega__grid">
					<?php foreach ( $terms as $term ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><span><?php echo esc_html( $term->name ); ?></span><span class="rdg-mega__count"><?php echo (int) $term->count; ?></span></a></li>
					<?php endforeach; ?>
				</ul>
				<a class="rdg-mega__all" href="<?php echo esc_url( $all_url ); ?>"><?php echo esc_html( $all_label ); ?></a>
			</div>
		</div>
	</li>
	<?php
}

/**
 * Fallback links when no menu is assigned: top categories, then core pages.
 */
function ridgeline_fallback_links() {
	$links = array();
	foreach ( ridgeline_top_categories( 6 ) as $term ) {
		$links[] = array( get_term_link( $term ), $term->name );
	}
	foreach ( array( 'service' => 'Service', 'financing' => 'Financing', 'contact' => 'Contact' ) as $slug => $title ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$links[] = array( get_permalink( $page ), get_the_title( $page ) );
		}
	}
	return $links;
}

function ridgeline_nav_items( $context = 'desktop' ) {
	$shop_url = function_exists( 'ridgeline_shop_url' ) ? ridgeline_shop_url() : home_url( '/' );

	if ( 'desktop' === $context && ridgeline_mod( 'header_shop_mega' ) ) {
		ridgeline_mega( 'rdg-mega-shop', 'Shop', ridgeline_top_categories(), $shop_url, 'Shop all products →' );
	} elseif ( 'drawer' === $context ) {
		echo '<li class="rdg-nav__item"><a class="rdg-nav__link" href="' . esc_url( $shop_url ) . '">Shop all</a></li>';
	}

	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '%3$s',
				'depth'          => 1,
				'walker'         => new Ridgeline_Nav_Walker(),
			)
		);
	} else {
		foreach ( ridgeline_fallback_links() as $link ) {
			if ( is_wp_error( $link[0] ) ) {
				continue;
			}
			echo '<li class="rdg-nav__item"><a class="rdg-nav__link" href="' . esc_url( $link[0] ) . '">' . esc_html( $link[1] ) . '</a></li>';
		}
	}

	if ( 'desktop' === $context && ridgeline_mod( 'header_brands_mega' ) ) {
		$brands     = ridgeline_brands();
		$brands_url = $shop_url;
		ridgeline_mega( 'rdg-mega-brands', 'Brands', $brands, $brands_url, 'All products →' );
	}
}

/**
 * Flat menu items with the theme's classes and aria-current.
 */
class Ridgeline_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$current = in_array( 'current-menu-item', (array) $item->classes, true ) || in_array( 'current-menu-ancestor', (array) $item->classes, true );
		$output .= sprintf(
			'<li class="rdg-nav__item"><a class="rdg-nav__link" href="%1$s"%2$s%3$s>%4$s</a>',
			esc_url( $item->url ),
			$current ? ' aria-current="page"' : '',
			$item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '',
			esc_html( $item->title )
		);
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/**
 * Footer column: a menu location, or links built from terms or pages.
 */
function ridgeline_footer_links( $location ) {
	if ( 'footer-quick' === $location ) {
		if ( has_nav_menu( 'footer-quick' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'footer-quick',
					'container'      => false,
					'menu_class'     => 'rdg-footer__links',
					'depth'          => 1,
				)
			);
			return;
		}
		$links = array();
		if ( function_exists( 'ridgeline_shop_url' ) ) {
			$links[] = array( ridgeline_shop_url(), 'Shop all' );
		}
		foreach ( array( 'about', 'service', 'financing', 'contact' ) as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				$links[] = array( get_permalink( $page ), get_the_title( $page ) );
			}
		}
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$links[] = array( wc_get_page_permalink( 'myaccount' ), 'My account' );
		}
	} elseif ( 'categories' === $location ) {
		$links = array();
		foreach ( ridgeline_top_categories( 7 ) as $term ) {
			$links[] = array( get_term_link( $term ), $term->name );
		}
	} else {
		$links = array();
		foreach ( ridgeline_brands( 7 ) as $term ) {
			$links[] = array( get_term_link( $term ), $term->name );
		}
	}
	if ( ! $links ) {
		return;
	}
	echo '<ul class="rdg-footer__links">';
	foreach ( $links as $link ) {
		if ( ! is_wp_error( $link[0] ) ) {
			echo '<li><a href="' . esc_url( $link[0] ) . '">' . esc_html( $link[1] ) . '</a></li>';
		}
	}
	echo '</ul>';
}
