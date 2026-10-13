<?php
/**
 * Post card in listings.
 *
 * @package RIDGELINE
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'rdg-post' ); ?>>
	<a class="rdg-post__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'medium_large', array( 'alt' => '' ) );
		} else {
			echo '<span class="rdg-ph"></span>';
		}
		?>
	</a>
	<div class="rdg-post__body">
		<p class="rdg-post__meta"><?php echo esc_html( get_the_date() ); ?></p>
		<h2 class="rdg-h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26 ) ); ?></p>
		<a class="rdg-link" href="<?php the_permalink(); ?>">Read more<span class="screen-reader-text"> about <?php the_title(); ?></span> →</a>
	</div>
</article>
