<?php
/**
 * Financing page (slug "financing").
 *
 * @package RIDGELINE
 */

get_header();
$benefits = ridgeline_rows( ridgeline_mod( 'fin_benefits' ) );
$partners = ridgeline_rows( ridgeline_mod( 'fin_partners' ) );
?>
<main id="primary" class="rdg-page rdg-financing">
	<?php ridgeline_breadcrumb(); ?>
	<div class="rdg-wrap">
		<header class="rdg-page__head">
			<h1 class="rdg-h1"><?php echo esc_html( ridgeline_mod( 'fin_heading' ) ); ?></h1>
			<p class="rdg-lead"><?php echo esc_html( ridgeline_mod( 'fin_intro' ) ); ?></p>
		</header>

		<?php if ( $benefits ) : ?>
			<ul class="rdg-features">
				<?php foreach ( $benefits as $row ) : ?>
					<li class="rdg-feature">
						<p class="rdg-stat"><?php echo esc_html( $row[0] ); ?></p>
						<h2 class="rdg-h3"><?php echo esc_html( isset( $row[1] ) ? $row[1] : '' ); ?></h2>
						<?php if ( ! empty( $row[2] ) ) : ?>
							<p><?php echo esc_html( $row[2] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $partners ) : ?>
			<section class="rdg-section" aria-labelledby="rdg-partners-title">
				<h2 id="rdg-partners-title" class="rdg-h2">Our financing partners</h2>
				<ul class="rdg-partners">
					<?php foreach ( $partners as $row ) : ?>
						<li class="rdg-partner">
							<p class="rdg-partner__name"><?php echo esc_html( $row[0] ); ?></p>
							<?php if ( ! empty( $row[1] ) ) : ?>
								<p><?php echo esc_html( $row[1] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $row[2] ) && '#' !== $row[2] ) : ?>
								<a class="rdg-btn rdg-btn--brand" href="<?php echo esc_url( ridgeline_url( $row[2] ) ); ?>" target="_blank" rel="noopener">Apply with <?php echo esc_html( $row[0] ); ?><span class="screen-reader-text"> (opens in a new tab)</span></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

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
