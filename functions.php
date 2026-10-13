<?php
/**
 * RIDGELINE functions and definitions.
 *
 * @package RIDGELINE
 */

if ( ! defined( '_S_VERSION' ) ) {
	define( '_S_VERSION', '1.0.0' );
}

function ridgeline_setup() {
	load_theme_textdomain( 'ridgeline', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 88,
			'width'       => 320,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	register_nav_menus(
		array(
			'primary'     => esc_html__( 'Main navigation', 'ridgeline' ),
			'footer-quick' => esc_html__( 'Footer: Quick links', 'ridgeline' ),
			'footer-legal' => esc_html__( 'Footer: Legal links', 'ridgeline' ),
		)
	);
}
add_action( 'after_setup_theme', 'ridgeline_setup' );

function ridgeline_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'ridgeline_content_width', 800 );
}
add_action( 'after_setup_theme', 'ridgeline_content_width', 0 );

function ridgeline_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;500;600;700&display=swap';
}

function ridgeline_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'ridgeline_resource_hints', 10, 2 );

function ridgeline_asset_version( $relative ) {
	$file = get_template_directory() . '/' . $relative;
	return file_exists( $file ) ? (string) filemtime( $file ) : _S_VERSION;
}

function ridgeline_scripts() {
	wp_enqueue_style( 'ridgeline-fonts', ridgeline_fonts_url(), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_style( 'ridgeline-style', get_stylesheet_uri(), array(), ridgeline_asset_version( 'style.css' ) );
	wp_add_inline_style( 'ridgeline-style', ridgeline_color_css() );

	wp_enqueue_script( 'ridgeline', get_template_directory_uri() . '/assets/js/ridgeline.js', array(), ridgeline_asset_version( 'assets/js/ridgeline.js' ), true );
	wp_localize_script(
		'ridgeline',
		'ridgelineConfig',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'ridgeline_ajax' ),
			'shopUrl'     => function_exists( 'ridgeline_shop_url' ) ? ridgeline_shop_url() : home_url( '/' ),
			'searchUrl'   => home_url( '/' ),
			'suggestions' => (bool) ridgeline_mod( 'header_suggestions' ),
			'compare'     => function_exists( 'ridgeline_compare_enabled' ) && ridgeline_compare_enabled(),
			'compareMax'  => 4,
			'phone'       => ridgeline_mod( 'dealer_phone' ),
			'tel'         => ridgeline_tel( ridgeline_mod( 'dealer_phone' ) ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ridgeline_scripts' );

/**
 * Customizer brand colors as CSS variables.
 */
function ridgeline_color_css() {
	$vars = array(
		'--brand'   => 'color_brand',
		'--brand-d' => 'color_brand_d',
		'--brand-l' => 'color_brand_l',
		'--accent'  => 'color_accent',
		'--ink'     => 'color_ink',
	);
	$css = '';
	foreach ( $vars as $var => $key ) {
		$value = sanitize_hex_color( ridgeline_mod( $key ) );
		if ( $value ) {
			$css .= $var . ':' . $value . ';';
		}
	}
	return $css ? ':root{' . $css . '}' : '';
}

/**
 * GA4 and Google Tag Manager.
 */
function ridgeline_tracking_head() {
	$gtm = preg_replace( '/[^A-Z0-9-]/', '', strtoupper( (string) ridgeline_mod( 'gtm_id' ) ) );
	$ga4 = preg_replace( '/[^A-Z0-9-]/', '', strtoupper( (string) ridgeline_mod( 'ga4_id' ) ) );
	if ( $gtm ) {
		?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( $gtm ); ?>');</script>
		<?php
	}
	if ( $ga4 ) {
		?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4 ); ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo esc_js( $ga4 ); ?>');</script>
		<?php
	}
}
add_action( 'wp_head', 'ridgeline_tracking_head', 1 );

function ridgeline_tracking_body() {
	$gtm = preg_replace( '/[^A-Z0-9-]/', '', strtoupper( (string) ridgeline_mod( 'gtm_id' ) ) );
	if ( $gtm ) {
		echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( $gtm ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
	}
}
add_action( 'wp_body_open', 'ridgeline_tracking_body', 1 );

/**
 * Optional Constellation dealer widget used by the first ecommerce theme.
 *
 * This is separate from the TargetWeb CRM lead-form module. The CRM module
 * owns its environment, store identifier, endpoint checks and submissions.
 */
function ridgeline_constellation_widget() {
	$client_id = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) ridgeline_mod( 'targetcrm_client' ) );
	if ( ! $client_id ) {
		return;
	}
	printf(
		'<script src="https://app.constellationdealer.com/widget.js" id="target-crm-widget" data-client-id="%s" defer></script>' . "\n",
		esc_attr( $client_id )
	);
}
add_action( 'wp_head', 'ridgeline_constellation_widget', 20 );

/**
 * Create the inner pages the templates expect, once, on activation.
 */
function ridgeline_create_pages() {
	$pages = array(
		'about'     => 'About',
		'contact'   => 'Contact',
		'service'   => 'Service',
		'financing' => 'Financing',
	);
	foreach ( $pages as $slug => $title ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $title,
				'post_name'   => $slug,
			)
		);
	}
}
add_action( 'after_switch_theme', 'ridgeline_create_pages' );

require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/navigation.php';
require get_template_directory() . '/inc/forms.php';
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}
require get_template_directory() . '/inc/seo.php';
require get_template_directory() . '/inc/maintenance.php';
