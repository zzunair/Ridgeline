<?php
/**
 * Homepage: store contact strip with map.
 *
 * @package RIDGELINE
 */

$phone = ridgeline_mod( 'dealer_phone' );
$email = ridgeline_mod( 'dealer_email' );
?>
<section class="rdg-contactstrip" aria-labelledby="rdg-contactstrip-title">
	<div class="rdg-wrap rdg-contactstrip__inner">
		<div>
			<h2 id="rdg-contactstrip-title" class="rdg-h2"><?php echo esc_html( ridgeline_mod( 'contact_strip_heading' ) ); ?></h2>
			<dl class="rdg-facts">
				<div><dt>Address</dt><dd><a href="<?php echo esc_url( ridgeline_directions_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( ridgeline_address() ); ?><span class="screen-reader-text"> (opens directions in a new tab)</span></a></dd></div>
				<?php if ( $phone ) : ?>
					<div><dt>Phone</dt><dd><a href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></dd></div>
				<?php endif; ?>
				<div><dt>Hours</dt><dd><?php echo esc_html( ridgeline_mod( 'dealer_hours_full' ) ); ?></dd></div>
				<?php if ( $email ) : ?>
					<div><dt>Email</dt><dd><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></dd></div>
				<?php endif; ?>
			</dl>
		</div>
		<?php ridgeline_map(); ?>
	</div>
</section>
