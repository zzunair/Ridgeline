<?php
/**
 * Site-wide search (posts and pages). Product searches use the shop template.
 *
 * @package RIDGELINE
 */

get_header();
$product_results = function_exists( 'ridgeline_product_search_ids' ) ? count( ridgeline_product_search_ids( get_search_query() ) ) : 0;
?>
<main id="primary" class="rdg-page rdg-blog">
	<?php ridgeline_breadcrumb(); ?>
	<div class="rdg-wrap">
		<header class="rdg-page__head">
			<h1 class="rdg-h1"><?php echo esc_html( ridgeline_page_title() ); ?></h1>
			<?php if ( $product_results ) : ?>
				<p class="rdg-notice rdg-notice--info">
					<?php echo esc_html( sprintf( _n( '%s product matches this search.', '%s products match this search.', $product_results, 'ridgeline' ), number_format_i18n( $product_results ) ) ); ?>
					<a href="<?php echo esc_url( add_query_arg( array( 's' => get_search_query(), 'post_type' => 'product' ), home_url( '/' ) ) ); ?>">See products →</a>
				</p>
			<?php endif; ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<ul class="rdg-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					echo '<li>';
					get_template_part( 'template-parts/content', 'search' );
					echo '</li>';
				endwhile;
				?>
			</ul>
			<?php the_posts_pagination( array( 'class' => 'rdg-pagination' ) ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
