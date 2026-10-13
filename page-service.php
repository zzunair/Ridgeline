<?php
/**
 * Service page (slug "service"): service list and request form.
 *
 * @package RIDGELINE
 */

get_header();
$services = ridgeline_rows( ridgeline_mod( 'service_list' ) );
$cats     = function_exists( 'ridgeline_top_categories' ) ? ridgeline_top_categories( 12 ) : array();
?>
<main id="primary" class="rdg-page rdg-service">
	<?php ridgeline_breadcrumb(); ?>
	<div class="rdg-wrap">
		<header class="rdg-page__head">
			<h1 class="rdg-h1"><?php echo esc_html( ridgeline_mod( 'service_heading' ) ); ?></h1>
			<p class="rdg-lead"><?php echo esc_html( ridgeline_mod( 'service_intro' ) ); ?></p>
		</header>

		<?php if ( $services ) : ?>
			<ul class="rdg-services">
				<?php foreach ( $services as $row ) : ?>
					<li class="rdg-service-card">
						<h2 class="rdg-h3"><?php echo esc_html( $row[0] ); ?></h2>
						<?php if ( ! empty( $row[1] ) ) : ?>
							<p class="rdg-service-card__price"><?php echo esc_html( $row[1] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $row[2] ) ) : ?>
							<p><?php echo esc_html( $row[2] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<section class="rdg-section" aria-labelledby="rdg-service-form-title">
			<h2 id="rdg-service-form-title" class="rdg-h2">Request service</h2>
			<form class="rdg-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-rdg-form novalidate>
				<input type="hidden" name="action" value="ridgeline_form">
				<?php ridgeline_form_hidden( 'service' ); ?>
				<div class="rdg-form__alert" role="alert" data-rdg-form-alert hidden></div>
				<div class="rdg-form__row">
					<div class="rdg-field"><label for="s-first">First name <span aria-hidden="true">*</span></label><input id="s-first" name="first_name" required autocomplete="given-name"></div>
					<div class="rdg-field"><label for="s-last">Last name <span aria-hidden="true">*</span></label><input id="s-last" name="last_name" required autocomplete="family-name"></div>
				</div>
				<div class="rdg-form__row">
					<div class="rdg-field"><label for="s-email">Email <span aria-hidden="true">*</span></label><input id="s-email" type="email" name="email" required autocomplete="email"></div>
					<div class="rdg-field"><label for="s-phone">Phone <span aria-hidden="true">*</span></label><input id="s-phone" type="tel" name="phone" required autocomplete="tel"></div>
				</div>
				<div class="rdg-form__row">
					<div class="rdg-field">
						<label for="s-equipment">Equipment type <span aria-hidden="true">*</span></label>
						<select id="s-equipment" name="equipment" class="rdg-select" required>
							<option value="">Choose…</option>
							<?php foreach ( $cats as $term ) : ?>
								<option><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
							<option>Other</option>
						</select>
					</div>
					<div class="rdg-field"><label for="s-brand">Brand &amp; model</label><input id="s-brand" name="brand"></div>
				</div>
				<div class="rdg-form__row">
					<div class="rdg-field">
						<label for="s-service">Service needed</label>
						<select id="s-service" name="service" class="rdg-select">
							<option value="">Not sure</option>
							<?php foreach ( $services as $row ) : ?>
								<option><?php echo esc_html( $row[0] ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="rdg-field"><label for="s-date">Preferred drop-off date</label><input id="s-date" type="date" name="date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>"></div>
				</div>
				<label class="rdg-check">
					<input type="checkbox" name="pickup" value="Yes">
					<span class="rdg-check__box" aria-hidden="true"></span>
					<span class="rdg-check__label">I’d like pickup and delivery (within 25 miles)</span>
				</label>
				<div class="rdg-field"><label for="s-message">Describe the problem <span aria-hidden="true">*</span></label><textarea id="s-message" name="message" rows="5" required></textarea></div>
				<button type="submit" class="rdg-btn rdg-btn--brand rdg-btn--lg">Send request</button>
			</form>
		</section>

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
