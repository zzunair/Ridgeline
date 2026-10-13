<?php
/**
 * Site footer, compare bar and compare dialog.
 *
 * @package RIDGELINE
 */

$phone  = ridgeline_mod( 'dealer_phone' );
$text   = ridgeline_mod( 'dealer_text' );
$email  = ridgeline_mod( 'dealer_email' );
$action = ridgeline_mod( 'footer_newsletter_action' );
?>
</div><!-- #page -->

<footer class="rdg-footer">
	<div class="rdg-wrap rdg-footer__grid">
		<div class="rdg-footer__brand">
			<?php ridgeline_logo(); ?>
			<p class="rdg-footer__about"><?php echo esc_html( ridgeline_mod( 'dealer_about' ) ); ?></p>
			<address class="rdg-footer__contact">
				<a href="<?php echo esc_url( ridgeline_directions_url() ); ?>" target="_blank" rel="noopener"><?php echo ridgeline_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( ridgeline_mod( 'dealer_street' ) ); ?><br><?php echo esc_html( ridgeline_mod( 'dealer_city' ) ); ?></span><span class="screen-reader-text"> (opens directions in a new tab)</span></a>
				<?php if ( $phone ) : ?>
					<a class="rdg-footer__phone" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>"><?php echo ridgeline_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $phone ); ?></span></a>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo ridgeline_icon( 'mail', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( antispambot( $email ) ); ?></span></a>
				<?php endif; ?>
				<span><?php echo ridgeline_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( ridgeline_mod( 'dealer_hours_full' ) ); ?></span></span>
				<?php if ( $text ) : ?>
					<a href="<?php echo esc_attr( ridgeline_sms( $text ) ); ?>"><?php echo ridgeline_icon( 'chat', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>Text us at <?php echo esc_html( $text ); ?></span></a>
				<?php endif; ?>
			</address>
			<?php ridgeline_social_list( 'rdg-social' ); ?>
		</div>

		<div class="rdg-footer__col">
			<h2 class="rdg-footer__title">Quick links</h2>
			<?php ridgeline_footer_links( 'footer-quick' ); ?>
		</div>
		<div class="rdg-footer__col">
			<h2 class="rdg-footer__title">Categories</h2>
			<?php ridgeline_footer_links( 'categories' ); ?>
		</div>
		<div class="rdg-footer__col">
			<h2 class="rdg-footer__title">Brands</h2>
			<?php ridgeline_footer_links( 'brands' ); ?>
		</div>
	</div>

	<?php if ( ridgeline_mod( 'footer_newsletter' ) ) : ?>
		<div class="rdg-footer__news">
			<div class="rdg-wrap rdg-footer__news-inner">
				<div>
					<h2 class="rdg-footer__news-title"><?php echo esc_html( ridgeline_mod( 'footer_newsletter_title' ) ); ?></h2>
					<p><?php echo esc_html( ridgeline_mod( 'footer_newsletter_text' ) ); ?></p>
				</div>
				<form class="rdg-footer__news-form" method="post" action="<?php echo esc_url( $action ? $action : admin_url( 'admin-ajax.php' ) ); ?>"<?php echo $action ? '' : ' data-rdg-form novalidate'; ?>>
					<?php if ( ! $action ) : ?>
						<input type="hidden" name="action" value="ridgeline_form">
						<?php ridgeline_form_hidden( 'newsletter' ); ?>
					<?php endif; ?>
					<label class="screen-reader-text" for="rdg-news-email"><?php esc_html_e( 'Email address', 'ridgeline' ); ?></label>
					<input id="rdg-news-email" type="email" name="email" required autocomplete="email" placeholder="you@example.com">
					<button type="submit" class="rdg-btn rdg-btn--accent"><?php esc_html_e( 'Subscribe', 'ridgeline' ); ?></button>
					<div class="rdg-form__alert rdg-footer__news-msg" role="alert" data-rdg-form-alert hidden></div>
				</form>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ridgeline_mod( 'footer_map' ) ) : ?>
		<?php ridgeline_map( 'rdg-footer__map' ); ?>
	<?php endif; ?>

	<div class="rdg-footer__bottom">
		<div class="rdg-wrap rdg-footer__bottom-inner">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( ridgeline_mod( 'dealer_name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'ridgeline' ); ?></p>
			<?php
			if ( has_nav_menu( 'footer-legal' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer-legal',
						'container'      => false,
						'menu_class'     => 'rdg-footer__legal',
						'depth'          => 1,
					)
				);
			} elseif ( get_privacy_policy_url() ) {
				echo '<ul class="rdg-footer__legal"><li><a href="' . esc_url( get_privacy_policy_url() ) . '">' . esc_html__( 'Privacy policy', 'ridgeline' ) . '</a></li></ul>';
			}
			?>
			<p>Design by <a href="<?php echo esc_url( ridgeline_mod( 'footer_credit_url' ) ); ?>" target="_blank" rel="noopener">TargetWeb</a></p>
		</div>
	</div>
</footer>

<?php if ( function_exists( 'ridgeline_compare_enabled' ) && ridgeline_compare_enabled() ) : ?>
	<div class="rdg-cmpbar" data-rdg-cmpbar hidden>
		<div class="rdg-wrap rdg-cmpbar__inner">
			<p class="rdg-cmpbar__title">Compare <span data-rdg-cmp-count>(0/4)</span></p>
			<ul class="rdg-cmpbar__items" data-rdg-cmp-items></ul>
			<p class="rdg-cmpbar__max" role="alert" data-rdg-cmp-max hidden>You can compare up to 4 products. Remove one to add another.</p>
			<div class="rdg-cmpbar__actions">
				<button type="button" class="rdg-btn rdg-btn--ghost" data-rdg-cmp-clear>Clear</button>
				<button type="button" class="rdg-btn rdg-btn--accent" data-rdg-cmp-open>Compare</button>
			</div>
		</div>
	</div>

	<div class="rdg-modal rdg-cmp" id="rdg-compare" role="dialog" aria-modal="true" aria-labelledby="rdg-compare-title" hidden>
		<div class="rdg-modal__backdrop" data-rdg-modal-close></div>
		<div class="rdg-modal__panel rdg-modal__panel--wide">
			<div class="rdg-modal__head rdg-modal__head--ink">
				<h2 id="rdg-compare-title">Compare products</h2>
				<button type="button" class="rdg-iconbtn" data-rdg-modal-close><?php echo ridgeline_icon( 'close', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text">Close compare</span></button>
			</div>
			<div class="rdg-modal__body" data-rdg-cmp-body></div>
		</div>
	</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
