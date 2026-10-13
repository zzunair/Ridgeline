<?php
/**
 * Single blog post.
 *
 * @package RIDGELINE
 */

get_header();
?>
<main id="primary" class="rdg-page rdg-article">
	<?php ridgeline_breadcrumb(); ?>
	<div class="rdg-wrap rdg-article__wrap">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="rdg-page__head">
					<?php
					$categories = get_the_category();
					if ( $categories ) :
						?>
						<p class="rdg-eyebrow"><a href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a></p>
					<?php endif; ?>
					<h1 class="rdg-h1"><?php the_title(); ?></h1>
					<p class="rdg-article__meta"><?php ridgeline_posted_on(); ?> <?php ridgeline_posted_by(); ?></p>
				</header>
				<?php ridgeline_post_thumbnail(); ?>
				<div class="rdg-prose">
					<?php the_content(); ?>
				</div>
				<?php
				the_post_navigation(
					array(
						'prev_text' => '<span class="rdg-eyebrow">Previous</span> %title',
						'next_text' => '<span class="rdg-eyebrow">Next</span> %title',
					)
				);
				?>
			</article>
		<?php endwhile; ?>
	</div>
</main>
<?php
get_footer();
