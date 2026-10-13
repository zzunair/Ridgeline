<?php
/**
 * Blog index, archives and fallback.
 *
 * @package RIDGELINE
 */

get_header();
?>
<main id="primary" class="rdg-page rdg-blog">
	<?php ridgeline_breadcrumb(); ?>
	<div class="rdg-wrap">
		<header class="rdg-page__head">
			<h1 class="rdg-h1"><?php echo wp_kses_post( ridgeline_page_title() ); ?></h1>
			<?php the_archive_description( '<div class="rdg-lead">', '</div>' ); ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<ul class="rdg-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					echo '<li>';
					get_template_part( 'template-parts/content', is_search() ? 'search' : get_post_type() );
					echo '</li>';
				endwhile;
				?>
			</ul>
			<?php
			the_posts_pagination(
				array(
					'class'     => 'rdg-pagination',
					'prev_text' => '‹<span class="screen-reader-text"> Previous page</span>',
					'next_text' => '<span class="screen-reader-text">Next page </span>›',
				)
			);
			?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
