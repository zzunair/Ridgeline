<?php
/**
 * 404.
 *
 * @package RIDGELINE
 */

get_header();
$phone = ridgeline_mod( 'dealer_phone' );
$cats  = function_exists( 'ridgeline_top_categories' ) ? ridgeline_top_categories( 6 ) : array();
?>
<main id="primary" class="rdg-page rdg-404">
	<div class="rdg-wrap">
		<div class="rdg-empty">
			<span class="rdg-empty__mark" aria-hidden="true">404</span>
			<h1 class="rdg-h1">We can’t find that page</h1>
			<p>The link may be old, or the product may have moved. Search below or call the shop.</p>
			<form class="rdg-refine" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label for="rdg-404-search" class="screen-reader-text">Search products</label>
				<div class="rdg-refine__row">
					<input id="rdg-404-search" type="search" name="s" placeholder="<?php echo esc_attr( ridgeline_mod( 'header_search_text' ) ); ?>">
					<input type="hidden" name="post_type" value="product">
					<button type="submit" class="rdg-btn rdg-btn--brand">Search</button>
				</div>
			</form>
			<div class="rdg-empty__actions">
				<a class="rdg-btn rdg-btn--brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to home</a>
				<?php if ( $phone ) : ?>
					<a class="rdg-btn rdg-btn--accent" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>">Call <?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( $cats ) : ?>
				<p class="rdg-eyebrow">Popular categories</p>
				<ul class="rdg-chips">
					<?php foreach ( $cats as $term ) : ?>
						<li><a class="rdg-chip" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
