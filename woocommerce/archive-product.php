<?php
/**
 * Shop, product category/brand/tag archives and product search.
 *
 * @package RIDGELINE
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;
$term      = is_product_taxonomy() ? get_queried_object() : null;
$is_search = is_search();
$total     = (int) $wp_query->found_posts;
$chips     = ridgeline_active_filters();
$view      = ( isset( $_COOKIE['ridgeline_view'] ) && 'list' === $_COOKIE['ridgeline_view'] ) ? 'list' : 'grid';

if ( $is_search ) {
	$title = sprintf( 'Results for “%s”', get_search_query() );
	$intro = '';
} elseif ( $term ) {
	$title = $term->name;
	$intro = term_description( $term );
} else {
	$title = ridgeline_mod( 'shop_title' );
	$intro = str_replace( '{count}', number_format_i18n( (int) wp_count_posts( 'product' )->publish ), ridgeline_mod( 'shop_intro' ) );
}

$banner_image = '';
if ( $term && 'product_cat' === $term->taxonomy ) {
	$thumb        = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	$banner_image = $thumb ? wp_get_attachment_image_url( $thumb, 'full' ) : '';
} elseif ( ! $term && ! $is_search && ridgeline_mod( 'shop_banner_text' ) ) {
	$banner_image = ridgeline_mod( 'shop_banner_image' );
}
?>
<main id="primary" class="rdg-shop">
	<?php woocommerce_breadcrumb(); ?>

	<?php if ( ! $term && ! $is_search && ridgeline_mod( 'shop_banner_text' ) ) : ?>
		<div class="rdg-banner"<?php echo $banner_image ? ' style="--banner-image:url(' . esc_url( $banner_image ) . ')"' : ''; ?>>
			<div class="rdg-wrap"><p class="rdg-banner__text"><?php echo esc_html( ridgeline_mod( 'shop_banner_text' ) ); ?></p></div>
		</div>
	<?php elseif ( $term && $banner_image ) : ?>
		<div class="rdg-banner rdg-banner--image" style="--banner-image:url(<?php echo esc_url( $banner_image ); ?>)" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="rdg-wrap">
		<header class="rdg-shop__head">
			<h1 class="rdg-h1"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $intro ) : ?>
				<div class="rdg-shop__intro"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div>
			<?php endif; ?>
			<?php if ( $is_search ) : ?>
				<form class="rdg-refine" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label for="rdg-refine-input">Refine your search</label>
					<div class="rdg-refine__row">
						<input id="rdg-refine-input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
						<input type="hidden" name="post_type" value="product">
						<button type="submit" class="rdg-btn rdg-btn--brand">Search</button>
					</div>
				</form>
			<?php endif; ?>
		</header>

		<?php if ( ! $total && ! $chips ) : ?>
			<?php get_template_part( 'template-parts/shop/empty', null, array( 'search' => $is_search ) ); ?>
		<?php else : ?>
			<div class="rdg-shop__layout">
				<?php get_template_part( 'template-parts/shop/filters', null, array( 'term' => $term, 'total' => $total ) ); ?>

				<div class="rdg-shop__main">
					<div class="rdg-toolbar">
						<button type="button" class="rdg-btn rdg-btn--outline rdg-toolbar__filters" data-rdg-filters-open aria-controls="rdg-filters" aria-expanded="false">
							<?php echo ridgeline_icon( 'filter', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>Filters<?php echo $chips ? ' (' . count( $chips ) . ')' : ''; ?>
						</button>
						<p class="rdg-toolbar__count" role="status"><?php echo esc_html( sprintf( _n( '%s result', '%s results', $total, 'ridgeline' ), number_format_i18n( $total ) ) ); ?></p>
						<div class="rdg-toolbar__sort"><?php woocommerce_catalog_ordering(); ?></div>
						<div class="rdg-viewtoggle" role="group" aria-label="Layout">
							<button type="button" data-rdg-view="grid" aria-pressed="<?php echo 'grid' === $view ? 'true' : 'false'; ?>"><?php echo ridgeline_icon( 'grid', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text">Grid view</span></button>
							<button type="button" data-rdg-view="list" aria-pressed="<?php echo 'list' === $view ? 'true' : 'false'; ?>"><?php echo ridgeline_icon( 'list', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text">List view</span></button>
						</div>
					</div>

					<?php if ( $chips ) : ?>
						<ul class="rdg-chips rdg-chips--active" aria-label="Active filters">
							<?php foreach ( $chips as $chip ) : ?>
								<li><a class="rdg-chip rdg-chip--remove" href="<?php echo esc_url( $chip['url'] ); ?>"><?php echo esc_html( $chip['label'] ); ?><span aria-hidden="true">×</span><span class="screen-reader-text"> — remove filter</span></a></li>
							<?php endforeach; ?>
							<li><a class="rdg-link" href="<?php echo esc_url( preg_replace( '#/page/\d+/?#', '/', remove_query_arg( array( 'fcat', 'fbrand', 'fstock', 'fattr', 'min_price', 'max_price', 'paged' ) ) ) ); ?>">Clear all</a></li>
						</ul>
					<?php endif; ?>

					<?php woocommerce_output_all_notices(); ?>

					<?php if ( have_posts() ) : ?>
						<ul class="rdg-grid rdg-grid--3 rdg-products<?php echo 'list' === $view ? ' is-list' : ''; ?>" data-rdg-products>
							<?php
							while ( have_posts() ) :
								the_post();
								$product = wc_get_product( get_the_ID() );
								echo '<li>';
								get_template_part( 'template-parts/product-card', null, array( 'product' => $product, 'heading' => 'h2' ) );
								echo '</li>';
							endwhile;
							?>
						</ul>
						<?php
						$links = paginate_links(
							array(
								'type'      => 'array',
								'prev_text' => '<span aria-hidden="true">‹</span><span class="screen-reader-text">Previous page</span>',
								'next_text' => '<span aria-hidden="true">›</span><span class="screen-reader-text">Next page</span>',
								'current'   => max( 1, get_query_var( 'paged' ) ),
								'total'     => $wp_query->max_num_pages,
							)
						);
						if ( $links ) {
							echo '<nav class="rdg-pagination" aria-label="Pagination"><ul><li>' . implode( '</li><li>', $links ) . '</li></ul></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					<?php else : ?>
						<div class="rdg-noresults">
							<p class="rdg-h3">No products match these filters.</p>
							<p>Remove a filter, or call us — we can usually find it.</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
