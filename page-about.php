<?php
/**
 * About page (slug "about"). Page content renders below the Customizer intro.
 *
 * @package RIDGELINE
 */

get_header();
$stats = ridgeline_rows( ridgeline_mod( 'about_stats' ) );
?>
<main id="primary" class="rdg-page rdg-about">
	<?php ridgeline_breadcrumb(); ?>
	<section class="rdg-pagehero">
		<div class="rdg-wrap rdg-pagehero__inner">
			<div>
				<p class="rdg-eyebrow"><?php echo esc_html( ridgeline_mod( 'about_eyebrow' ) ); ?></p>
				<h1 class="rdg-h1"><?php echo esc_html( ridgeline_mod( 'about_heading' ) ); ?></h1>
				<p class="rdg-lead"><?php echo esc_html( ridgeline_mod( 'about_lead' ) ); ?></p>
			</div>
			<div class="rdg-pagehero__media"><?php echo ridgeline_media( ridgeline_mod( 'about_image' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		</div>
	</section>

	<?php if ( $stats ) : ?>
		<section class="rdg-trust rdg-trust--stats" aria-label="By the numbers">
			<div class="rdg-wrap">
				<ul class="rdg-trust__grid">
					<?php foreach ( $stats as $row ) : ?>
						<li class="rdg-trust__item"><p class="rdg-stat"><?php echo esc_html( $row[0] ); ?></p><p><?php echo esc_html( isset( $row[1] ) ? $row[1] : '' ); ?></p></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php
	while ( have_posts() ) :
		the_post();
		if ( get_the_content() ) :
			?>
			<section class="rdg-section">
				<div class="rdg-wrap rdg-prose"><?php the_content(); ?></div>
			</section>
			<?php
		endif;
	endwhile;
	?>

	<?php get_template_part( 'template-parts/home/story' ); ?>
	<?php get_template_part( 'template-parts/home/contact' ); ?>
</main>
<?php
get_footer();
