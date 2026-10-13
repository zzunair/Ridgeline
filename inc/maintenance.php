<?php
/**
 * Maintenance mode. While it is on, every public URL redirects to /maintenance/.
 * A timer brings the site back on its own when the end time is reached.
 *
 * @package RIDGELINE
 */

defined( 'ABSPATH' ) || exit;

function ridgeline_maintenance_mod( $key, $default = '' ) {
	return get_theme_mod( 'ridgeline_maintenance_' . $key, $default );
}

function ridgeline_sanitize_maintenance_end( $value ) {
	$value = sanitize_text_field( $value );
	if ( '' === $value ) {
		return '';
	}
	$dt = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $value, wp_timezone() );
	if ( ! $dt ) {
		$dt = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i:s', $value, wp_timezone() );
	}
	return $dt ? $dt->format( 'Y-m-d\TH:i' ) : '';
}

function ridgeline_maintenance_end_timestamp() {
	$raw = (string) ridgeline_maintenance_mod( 'timer_end', '' );
	if ( '' === $raw ) {
		return 0;
	}
	$dt = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $raw, wp_timezone() );
	return $dt ? $dt->getTimestamp() : 0;
}

function ridgeline_maintenance_active() {
	if ( ! ridgeline_maintenance_mod( 'enabled', false ) ) {
		return false;
	}
	if ( ! ridgeline_maintenance_mod( 'timer_enabled', false ) ) {
		return true;
	}
	$end = ridgeline_maintenance_end_timestamp();
	if ( ! $end ) {
		return true;
	}
	return time() < $end;
}

function ridgeline_maintenance_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$path = wp_parse_url( $uri, PHP_URL_PATH );
	$path = is_string( $path ) ? $path : '';
	$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home = is_string( $home ) ? untrailingslashit( $home ) : '';
	if ( $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	return trim( $path, '/' );
}

function ridgeline_maintenance_customize( $wp_customize ) {
	$wp_customize->add_section(
		'ridgeline_maintenance',
		array(
			'title'       => 'Maintenance Mode',
			'description' => 'While this is on, every public URL redirects to the maintenance page. Administrators can still browse the site. With the timer on, the site opens again at the end time.',
			'panel'       => 'ridgeline_integrations',
			'priority'    => 90,
		)
	);

	$wp_customize->add_setting( 'ridgeline_maintenance_enabled', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'ridgeline_maintenance_enabled', array( 'label' => 'Enable maintenance mode', 'section' => 'ridgeline_maintenance', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'ridgeline_maintenance_headline', array( 'default' => "We'll be back soon.", 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'ridgeline_maintenance_headline', array( 'label' => 'Headline', 'section' => 'ridgeline_maintenance', 'type' => 'text' ) );

	$wp_customize->add_setting( 'ridgeline_maintenance_message', array( 'default' => "We're making some updates. Call the shop if you need us today.", 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'ridgeline_maintenance_message', array( 'label' => 'Message', 'section' => 'ridgeline_maintenance', 'type' => 'textarea' ) );

	$wp_customize->add_setting( 'ridgeline_maintenance_timer_enabled', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'ridgeline_maintenance_timer_enabled', array( 'label' => 'Enable countdown timer', 'description' => 'Shows a countdown and turns maintenance off when the end time is reached.', 'section' => 'ridgeline_maintenance', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'ridgeline_maintenance_timer_end', array( 'default' => '', 'sanitize_callback' => 'ridgeline_sanitize_maintenance_end' ) );
	$wp_customize->add_control(
		'ridgeline_maintenance_timer_end',
		array(
			'label'           => 'Timer end',
			'description'     => 'Uses the site timezone. Leave empty to keep maintenance on until you turn it off.',
			'section'         => 'ridgeline_maintenance',
			'type'            => 'datetime-local',
			'active_callback' => static function () {
				return (bool) get_theme_mod( 'ridgeline_maintenance_timer_enabled', false );
			},
		)
	);
}
add_action( 'customize_register', 'ridgeline_maintenance_customize', 20 );

function ridgeline_render_maintenance_page() {
	$end      = ridgeline_maintenance_mod( 'timer_enabled', false ) ? ridgeline_maintenance_end_timestamp() : 0;
	$retry    = ( $end && $end > time() ) ? ( $end - time() ) : 3600;
	$headline = ridgeline_maintenance_mod( 'headline', "We'll be back soon." );
	$message  = ridgeline_maintenance_mod( 'message', "We're making some updates. Call the shop if you need us today." );
	$phone    = ridgeline_mod( 'dealer_phone' );
	$home     = home_url( '/' );

	status_header( 503 );
	nocache_headers();
	header( 'Retry-After: ' . (int) $retry, true );
	header( 'X-Robots-Tag: noindex, nofollow', true );

	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — Maintenance</title>
<style>
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 20px;background:#143D28;color:#fff;font-family:Barlow,Segoe UI,Arial,sans-serif;text-align:center}
main{max-width:600px;border-top:5px solid #F2B100;background:#1E5A3C;padding:40px 28px}
h1{font-family:"Barlow Condensed",Barlow,Arial,sans-serif;font-weight:700;text-transform:uppercase;font-size:2.4rem;line-height:1.05;margin:0 0 12px}
p{margin:0;color:#E4EFE8;font-size:1.05rem;line-height:1.6}
a{color:#F2B100;font-weight:700}
.timer{display:flex;gap:12px;justify-content:center;margin-top:28px;flex-wrap:wrap}
.timer div{min-width:72px;background:#14201A;border-top:3px solid #F2B100;padding:12px 8px}
.timer strong{display:block;font-family:"Barlow Condensed",Barlow,Arial,sans-serif;font-size:2rem;color:#fff}
.timer span{font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:#E4EFE8}
.call{margin-top:24px}
</style>
</head>
<body<?php echo $end ? ' data-end="' . esc_attr( $end ) . '" data-home="' . esc_url( $home ) . '"' : ''; ?>>
<main>
<h1><?php echo esc_html( $headline ); ?></h1>
<p><?php echo esc_html( $message ); ?></p>
<?php if ( $end ) : ?>
<div class="timer" aria-live="polite">
<div><strong data-d>0</strong><span>Days</span></div>
<div><strong data-h>0</strong><span>Hours</span></div>
<div><strong data-m>0</strong><span>Minutes</span></div>
<div><strong data-s>0</strong><span>Seconds</span></div>
</div>
<script>
(function(){
	var end = parseInt(document.body.getAttribute('data-end'), 10) * 1000;
	var home = document.body.getAttribute('data-home') || '/';
	var d = document.querySelector('[data-d]'), h = document.querySelector('[data-h]'), m = document.querySelector('[data-m]'), s = document.querySelector('[data-s]');
	function tick(){
		var left = Math.max(0, end - Date.now()), sec = Math.floor(left / 1000);
		d.textContent = String(Math.floor(sec / 86400));
		h.textContent = String(Math.floor((sec % 86400) / 3600));
		m.textContent = String(Math.floor((sec % 3600) / 60));
		s.textContent = String(sec % 60);
		if (left <= 0) { window.location.replace(home); }
	}
	tick();
	setInterval(tick, 1000);
})();
</script>
<?php endif; ?>
<?php if ( $phone ) : ?>
<p class="call"><a href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>">Call <?php echo esc_html( $phone ); ?></a></p>
<?php endif; ?>
</main>
</body>
</html>
	<?php
	exit;
}

function ridgeline_maintenance_guard() {
	$is_page = ( 'maintenance' === ridgeline_maintenance_request_path() );

	if ( ! ridgeline_maintenance_active() ) {
		if ( $is_page ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
		return;
	}

	if ( $is_page ) {
		ridgeline_render_maintenance_page();
	}

	if ( is_customize_preview() || current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_safe_redirect( home_url( '/maintenance/' ), 302 );
	exit;
}
add_action( 'template_redirect', 'ridgeline_maintenance_guard', 0 );

function ridgeline_maintenance_block_rest( $result ) {
	if ( is_wp_error( $result ) || ! ridgeline_maintenance_active() || current_user_can( 'manage_options' ) ) {
		return $result;
	}
	return new WP_Error( 'maintenance', 'Site is under maintenance.', array( 'status' => 503 ) );
}
add_filter( 'rest_authentication_errors', 'ridgeline_maintenance_block_rest', 120 );

function ridgeline_maintenance_block_ajax() {
	if ( ! wp_doing_ajax() || ! ridgeline_maintenance_active() || current_user_can( 'manage_options' ) ) {
		return;
	}
	status_header( 503 );
	header( 'X-Robots-Tag: noindex, nofollow', true );
	wp_die( esc_html__( 'Site is under maintenance.', 'ridgeline' ), '', array( 'response' => 503 ) );
}
add_action( 'admin_init', 'ridgeline_maintenance_block_ajax' );
