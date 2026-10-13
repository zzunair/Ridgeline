<?php
/**
 * Default page. Cart, checkout and account pages use it too.
 *
 * @package RIDGELINE
 */

get_header();

$is_store = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
?>
<main id="primary" class="rdg-page<?php echo $is_store ? ' rdg-page--store' : ''; ?>">
	<?php ridgeline_breadcrumb(); ?>
	<div class="rdg-wrap">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="rdg-page__head">
					<h1 class="rdg-h1"><?php the_title(); ?></h1>
				</header>
				<div class="<?php echo $is_store ? 'rdg-store' : 'rdg-prose'; ?>">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'ridgeline' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
</main>
<?php
get_footer();
