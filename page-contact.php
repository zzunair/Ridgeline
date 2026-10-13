<?php
/**
 * Contact page (slug "contact").
 *
 * @package RIDGELINE
 */

get_header();
$phone   = ridgeline_mod( 'dealer_phone' );
$email   = ridgeline_mod( 'dealer_email' );
$product = isset( $_GET['product'] ) ? sanitize_text_field( wp_unslash( $_GET['product'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
?>
<main id="primary" class="rdg-page rdg-contact">
	<?php ridgeline_breadcrumb(); ?>
	<div class="rdg-wrap">
		<header class="rdg-page__head">
			<h1 class="rdg-h1"><?php echo esc_html( ridgeline_mod( 'contact_heading' ) ); ?></h1>
			<p class="rdg-lead"><?php echo esc_html( ridgeline_mod( 'contact_intro' ) ); ?></p>
		</header>

		<div class="rdg-contact__grid">
			<form class="rdg-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-rdg-form novalidate>
				<input type="hidden" name="action" value="ridgeline_form">
				<?php ridgeline_form_hidden( 'contact' ); ?>
				<div class="rdg-form__alert" role="alert" data-rdg-form-alert hidden></div>
				<div class="rdg-form__row">
					<div class="rdg-field"><label for="c-first">First name <span aria-hidden="true">*</span></label><input id="c-first" name="first_name" required autocomplete="given-name"></div>
					<div class="rdg-field"><label for="c-last">Last name <span aria-hidden="true">*</span></label><input id="c-last" name="last_name" required autocomplete="family-name"></div>
				</div>
				<div class="rdg-form__row">
					<div class="rdg-field"><label for="c-email">Email <span aria-hidden="true">*</span></label><input id="c-email" type="email" name="email" required autocomplete="email"></div>
					<div class="rdg-field"><label for="c-phone">Phone</label><input id="c-phone" type="tel" name="phone" autocomplete="tel"></div>
				</div>
				<div class="rdg-field">
					<label for="c-topic">Topic</label>
					<select id="c-topic" name="topic" class="rdg-select">
						<?php foreach ( array( 'Sales question', 'Parts', 'Service & repair', 'Financing', 'Something else' ) as $topic ) : ?>
							<option<?php selected( $product && 'Sales question' === $topic ); ?>><?php echo esc_html( $topic ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="rdg-field"><label for="c-message">Message <span aria-hidden="true">*</span></label><textarea id="c-message" name="message" rows="6" required><?php echo $product ? esc_textarea( sprintf( "I'd like more information about %s.", $product ) ) : ''; ?></textarea></div>
				<p class="rdg-form__note">Fields marked * are required.</p>
				<button type="submit" class="rdg-btn rdg-btn--brand rdg-btn--lg">Send message</button>
			</form>

			<aside class="rdg-contact__aside">
				<div class="rdg-infobox">
					<h2 class="rdg-h3">Visit the shop</h2>
					<p><?php echo esc_html( ridgeline_mod( 'dealer_street' ) ); ?><br><?php echo esc_html( ridgeline_mod( 'dealer_city' ) ); ?></p>
					<p><?php echo esc_html( ridgeline_mod( 'dealer_hours_full' ) ); ?></p>
					<?php if ( $phone ) : ?>
						<a class="rdg-btn rdg-btn--accent rdg-btn--block" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>">Call <?php echo esc_html( $phone ); ?></a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<p><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></p>
					<?php endif; ?>
					<a class="rdg-link" href="<?php echo esc_url( ridgeline_directions_url() ); ?>" target="_blank" rel="noopener">Get directions →<span class="screen-reader-text"> (opens in a new tab)</span></a>
				</div>
				<?php ridgeline_map(); ?>
			</aside>
		</div>

		<?php
		while ( have_posts() ) :
			the_post();
			if ( get_the_content() ) {
				echo '<div class="rdg-prose rdg-section">';
				the_content();
				echo '</div>';
			}
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
