<?php
/**
 * Empty catalog or no search results.
 *
 * @package RIDGELINE
 *
 * @var array $args { search: bool }
 */

$phone   = ridgeline_mod( 'dealer_phone' );
$popular = ridgeline_top_categories( 6 );
?>
<div class="rdg-empty">
	<span class="rdg-empty__mark" aria-hidden="true">?</span>
	<h2 class="rdg-h2"><?php echo ! empty( $args['search'] ) ? 'No products found' : 'Nothing here yet'; ?></h2>
	<p>
		<?php if ( ! empty( $args['search'] ) ) : ?>
			Check the spelling, try a model number, or search a broader term. If you don’t see it, we can probably order it.
		<?php else : ?>
			We’re updating this category. Call us for current stock.
		<?php endif; ?>
	</p>
	<div class="rdg-empty__actions">
		<a class="rdg-btn rdg-btn--brand" href="<?php echo esc_url( ridgeline_shop_url() ); ?>">Browse all products</a>
		<?php if ( $phone ) : ?>
			<a class="rdg-btn rdg-btn--accent" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>">Call <?php echo esc_html( $phone ); ?></a>
		<?php endif; ?>
	</div>
	<?php if ( $popular ) : ?>
		<p class="rdg-eyebrow">Popular categories</p>
		<ul class="rdg-chips">
			<?php foreach ( $popular as $term ) : ?>
				<li><a class="rdg-chip" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
