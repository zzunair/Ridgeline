<?php
/**
 * Site header.
 *
 * @package RIDGELINE
 */

$phone       = ridgeline_mod( 'dealer_phone' );
$email       = ridgeline_mod( 'dealer_email' );
$hours       = ridgeline_mod( 'dealer_hours' );
$account_url = ridgeline_mod( 'header_account_url' );
if ( ! $account_url && function_exists( 'wc_get_page_permalink' ) ) {
	$account_url = wc_get_page_permalink( 'myaccount' );
}
$placeholder = ridgeline_mod( 'header_search_text' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'ridgeline' ); ?></a>

<?php if ( ridgeline_mod( 'announce_enabled' ) && ridgeline_mod( 'announce_text' ) ) : ?>
	<div class="rdg-announce" data-rdg-announce="<?php echo esc_attr( md5( ridgeline_mod( 'announce_text' ) ) ); ?>">
		<div class="rdg-wrap rdg-announce__inner">
			<p>
				<?php echo esc_html( ridgeline_mod( 'announce_text' ) ); ?>
				<?php if ( ridgeline_mod( 'announce_link_text' ) && ridgeline_mod( 'announce_link_url' ) ) : ?>
					<a href="<?php echo esc_url( ridgeline_url( ridgeline_mod( 'announce_link_url' ) ) ); ?>"><?php echo esc_html( ridgeline_mod( 'announce_link_text' ) ); ?></a>
				<?php endif; ?>
			</p>
			<?php if ( ridgeline_mod( 'announce_dismissible' ) ) : ?>
				<button type="button" class="rdg-announce__close" data-rdg-announce-close aria-label="<?php esc_attr_e( 'Dismiss announcement', 'ridgeline' ); ?>"><?php echo ridgeline_icon( 'close', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ridgeline_mod( 'header_contact_bar' ) ) : ?>
	<div class="rdg-topbar">
		<div class="rdg-wrap rdg-topbar__inner">
			<div class="rdg-topbar__left">
				<?php if ( $email ) : ?>
					<a class="rdg-topbar__email" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo ridgeline_icon( 'mail', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( antispambot( $email ) ); ?></a>
				<?php endif; ?>
				<?php if ( $hours ) : ?>
					<span class="rdg-topbar__hours"><?php echo ridgeline_icon( 'clock', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $hours ); ?></span>
				<?php endif; ?>
			</div>
			<div class="rdg-topbar__right">
				<?php if ( $phone ) : ?>
					<a class="rdg-topbar__phone" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>"><?php echo ridgeline_icon( 'phone', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>Call <?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
				<?php ridgeline_social_list( 'rdg-topbar__social' ); ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<header id="masthead" class="rdg-header">
	<div class="rdg-header__main">
		<div class="rdg-wrap rdg-header__inner">
			<button type="button" class="rdg-iconbtn rdg-header__menu" data-rdg-drawer-open aria-controls="rdg-drawer" aria-expanded="false">
				<?php echo ridgeline_icon( 'menu', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'ridgeline' ); ?></span>
			</button>

			<?php ridgeline_logo(); ?>

			<button type="button" class="rdg-searchbar" data-rdg-search-open aria-haspopup="dialog" aria-controls="rdg-search">
				<?php echo ridgeline_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="rdg-searchbar__text"><?php echo esc_html( $placeholder ); ?></span>
				<span class="rdg-searchbar__btn" aria-hidden="true">Search</span>
			</button>

			<div class="rdg-header__actions">
				<button type="button" class="rdg-iconbtn rdg-header__search" data-rdg-search-open aria-haspopup="dialog" aria-controls="rdg-search">
					<?php echo ridgeline_icon( 'search', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php esc_html_e( 'Search', 'ridgeline' ); ?></span>
				</button>
				<?php if ( $account_url ) : ?>
					<a class="rdg-account" href="<?php echo esc_url( $account_url ); ?>"><?php echo ridgeline_icon( 'user', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="rdg-account__text"><?php echo esc_html( ridgeline_mod( 'header_account_text' ) ); ?></span></a>
				<?php endif; ?>
				<?php
				if ( function_exists( 'ridgeline_cart_link' ) ) {
					ridgeline_cart_link();
				}
				?>
			</div>
		</div>
	</div>

	<nav class="rdg-nav" aria-label="<?php esc_attr_e( 'Main', 'ridgeline' ); ?>">
		<div class="rdg-wrap">
			<ul class="rdg-nav__list">
				<?php ridgeline_nav_items( 'desktop' ); ?>
			</ul>
		</div>
	</nav>
</header>

<div class="rdg-drawer" id="rdg-drawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'ridgeline' ); ?>" hidden>
	<div class="rdg-drawer__backdrop" data-rdg-drawer-close></div>
	<div class="rdg-drawer__panel">
		<div class="rdg-drawer__head">
			<span>Menu</span>
			<button type="button" class="rdg-iconbtn" data-rdg-drawer-close><?php echo ridgeline_icon( 'close', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'ridgeline' ); ?></span></button>
		</div>
		<div class="rdg-drawer__contact">
			<?php if ( $phone ) : ?>
				<a class="rdg-btn rdg-btn--accent rdg-btn--block" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>"><?php echo ridgeline_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>Call <?php echo esc_html( $phone ); ?></a>
			<?php endif; ?>
			<?php if ( $hours ) : ?>
				<p><?php echo esc_html( $hours ); ?></p>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<p><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></p>
			<?php endif; ?>
		</div>
		<nav aria-label="<?php esc_attr_e( 'Mobile', 'ridgeline' ); ?>">
			<ul class="rdg-drawer__nav">
				<?php ridgeline_nav_items( 'drawer' ); ?>
			</ul>
		</nav>
		<div class="rdg-drawer__foot">
			<?php if ( $account_url ) : ?>
				<a class="rdg-btn rdg-btn--outline rdg-btn--block" href="<?php echo esc_url( $account_url ); ?>"><?php echo is_user_logged_in() ? esc_html__( 'My account', 'ridgeline' ) : esc_html__( 'Sign in / Account', 'ridgeline' ); ?></a>
			<?php endif; ?>
			<?php ridgeline_social_list( 'rdg-social' ); ?>
		</div>
	</div>
</div>

<div class="rdg-search" id="rdg-search" role="dialog" aria-modal="true" aria-labelledby="rdg-search-label" hidden>
	<div class="rdg-search__backdrop" data-rdg-search-close></div>
	<div class="rdg-search__panel">
		<div class="rdg-wrap">
			<form class="rdg-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label id="rdg-search-label" class="screen-reader-text" for="rdg-search-input"><?php esc_html_e( 'Search products', 'ridgeline' ); ?></label>
				<div class="rdg-search__field">
					<?php echo ridgeline_icon( 'search', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<input id="rdg-search-input" type="search" name="s" autocomplete="off" placeholder="<?php echo esc_attr( $placeholder ); ?>" aria-describedby="rdg-search-hint" aria-controls="rdg-search-results">
					<input type="hidden" name="post_type" value="product">
					<button type="button" class="rdg-search__clear" data-rdg-search-clear hidden><?php esc_html_e( 'Clear', 'ridgeline' ); ?></button>
					<button type="submit" class="rdg-btn rdg-btn--brand"><?php esc_html_e( 'Search', 'ridgeline' ); ?></button>
				</div>
				<button type="button" class="rdg-search__close" data-rdg-search-close><?php echo ridgeline_icon( 'close', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php esc_html_e( 'Close search', 'ridgeline' ); ?></span></button>
			</form>
			<p id="rdg-search-hint" class="rdg-search__hint"><?php esc_html_e( 'Search by model, SKU, brand, category or spec. Press Enter to see all results, Esc to close.', 'ridgeline' ); ?></p>

			<div id="rdg-search-results" class="rdg-search__results" aria-live="polite">
				<div class="rdg-search__popular" data-rdg-search-state="idle">
					<?php
					$popular = ridgeline_lines( ridgeline_mod( 'header_popular' ) );
					if ( $popular ) :
						?>
						<p class="rdg-eyebrow"><?php esc_html_e( 'Popular categories', 'ridgeline' ); ?></p>
						<ul class="rdg-chips">
							<?php
							foreach ( $popular as $name ) :
								$term = taxonomy_exists( 'product_cat' ) ? get_term_by( 'name', $name, 'product_cat' ) : false;
								$url  = $term ? get_term_link( $term ) : add_query_arg( array( 's' => $name, 'post_type' => 'product' ), home_url( '/' ) );
								?>
								<li><a class="rdg-chip" href="<?php echo esc_url( is_wp_error( $url ) ? home_url( '/' ) : $url ); ?>"><?php echo esc_html( $name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
				<div class="rdg-search__live" data-rdg-search-live hidden></div>
			</div>
		</div>
	</div>
</div>

<div id="page" class="site">
