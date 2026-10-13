<?php
/**
 * Homepage: shop by category.
 *
 * @package RIDGELINE
 */

$manual = 'manual' === ridgeline_mod( 'cats_source' );
$cards  = array();
if ( $manual ) {
	for ( $n = 1; $n <= 8; $n++ ) {
		$title = trim( (string) ridgeline_mod( "cat{$n}_title" ) );
		$url   = ridgeline_mod( "cat{$n}_url" );
		if ( $title && $url ) {
			$cards[] = array(
				'title' => $title,
				'image' => ridgeline_mod( "cat{$n}_image" ),
				'url'   => $url,
			);
		}
	}
} else {
	$cards = ridgeline_top_categories( max( 1, (int) ridgeline_mod( 'cats_limit' ) ) );
}
if ( ! $cards ) {
	return;
}
$carousel = ridgeline_mod( 'cats_carousel' );
?>
<section class="rdg-section" aria-labelledby="rdg-cats-title">
	<div class="rdg-wrap">
		<div class="rdg-section__head">
			<h2 id="rdg-cats-title" class="rdg-h2"><?php echo esc_html( ridgeline_mod( 'cats_heading' ) ); ?></h2>
			<a class="rdg-link" href="<?php echo esc_url( ridgeline_shop_url() ); ?>"><?php echo esc_html( ridgeline_mod( 'cats_link' ) ); ?></a>
		</div>
		<ul class="rdg-catgrid<?php echo $carousel ? ' rdg-scroller' : ''; ?>">
			<?php
			foreach ( $cards as $card ) :
				$term  = $manual ? null : $card;
				$thumb = $term ? (int) get_term_meta( $term->term_id, 'thumbnail_id', true ) : 0;
				$title = $manual ? $card['title'] : $term->name;
				$url   = $manual ? $card['url'] : get_term_link( $term );
				?>
				<li>
					<a class="rdg-catcard" href="<?php echo esc_url( $url ); ?>">
						<span class="rdg-catcard__media">
							<?php
							if ( $manual && $card['image'] ) {
								printf( '<img src="%s" alt="" loading="lazy">', esc_url( $card['image'] ) );
							} else {
								echo $thumb ? wp_get_attachment_image( $thumb, 'woocommerce_thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ) : '<span class="rdg-ph"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</span>
						<span class="rdg-catcard__body">
							<span class="rdg-catcard__name"><?php echo esc_html( $title ); ?></span>
							<span class="rdg-catcard__count">
								<?php echo $manual ? esc_html__( 'Shop now →', 'ridgeline' ) : esc_html( sprintf( _n( '%s item', '%s items', $term->count, 'ridgeline' ), number_format_i18n( $term->count ) ) ); ?>
							</span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
