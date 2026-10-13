<?php
/**
 * Single product.
 *
 * @package RIDGELINE
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	global $product;
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) {
		continue;
	}

	$id          = $product->get_id();
	$brand       = ridgeline_brand( $product );
	$stock       = ridgeline_stock( $product );
	$call        = ridgeline_is_call_for_price( $product );
	$phone       = ridgeline_mod( 'dealer_phone' );
	$replacement = ridgeline_replacement( $product );
	$savings     = ridgeline_savings( $product );
	$images      = array_values( array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) ) );
	$specs       = ridgeline_product_specs( $product );
	if ( $product->has_weight() ) {
		$specs['Weight'] = wc_format_weight( $product->get_weight() );
	}
	if ( $product->has_dimensions() ) {
		$specs['Dimensions'] = wc_format_dimensions( $product->get_dimensions( false ) );
	}
	$cats = wc_get_product_category_list( $id, ', ' );
	?>
	<main id="primary" class="rdg-product">
		<?php woocommerce_breadcrumb(); ?>
		<div class="rdg-wrap">
			<?php do_action( 'woocommerce_before_single_product' ); ?>

			<?php if ( $replacement ) : ?>
				<div class="rdg-notice rdg-notice--warn" role="note">
					<strong>This model has been discontinued.</strong>
					It’s been replaced by <a href="<?php echo esc_url( get_permalink( $replacement->get_id() ) ); ?>"><?php echo esc_html( $replacement->get_name() ); ?></a>.
				</div>
			<?php endif; ?>

			<div class="rdg-product__top">
				<div class="rdg-gallery" data-rdg-gallery>
					<div class="rdg-gallery__main">
						<?php if ( $images ) : ?>
							<?php foreach ( $images as $i => $image_id ) : ?>
								<figure class="rdg-gallery__slide<?php echo 0 === $i ? ' is-active' : ''; ?>"<?php echo 0 === $i ? '' : ' hidden'; ?> data-full="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'full' ) ); ?>">
									<?php echo wp_get_attachment_image( $image_id, 'woocommerce_single', false, array( 'loading' => 0 === $i ? 'eager' : 'lazy', 'alt' => get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : $product->get_name() ) ); ?>
								</figure>
							<?php endforeach; ?>
						<?php else : ?>
							<figure class="rdg-gallery__slide is-active"><span class="rdg-ph"></span></figure>
						<?php endif; ?>

						<?php if ( $product->is_on_sale() && ! $call ) : ?>
							<span class="rdg-card__badge rdg-card__badge--sale">Sale</span>
						<?php endif; ?>

						<?php if ( count( $images ) > 1 ) : ?>
							<button type="button" class="rdg-gallery__nav rdg-gallery__nav--prev" data-rdg-gallery-prev aria-label="Previous image"><?php echo ridgeline_icon( 'left', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
							<button type="button" class="rdg-gallery__nav rdg-gallery__nav--next" data-rdg-gallery-next aria-label="Next image"><?php echo ridgeline_icon( 'right', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
							<span class="rdg-gallery__counter" aria-live="polite"><span data-rdg-gallery-index>1</span> / <?php echo (int) count( $images ); ?></span>
						<?php endif; ?>
						<?php if ( $images ) : ?>
							<button type="button" class="rdg-gallery__full" data-rdg-gallery-full><?php echo ridgeline_icon( 'expand', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>Fullscreen</button>
						<?php endif; ?>
					</div>

					<?php if ( count( $images ) > 1 ) : ?>
						<ul class="rdg-gallery__thumbs">
							<?php foreach ( $images as $i => $image_id ) : ?>
								<li>
									<button type="button" data-rdg-gallery-thumb="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( 'Show image %d', $i + 1 ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>>
										<?php echo wp_get_attachment_image( $image_id, 'woocommerce_gallery_thumbnail', false, array( 'alt' => '' ) ); ?>
									</button>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<div class="rdg-product__info">
					<?php if ( $brand ) : ?>
						<p class="rdg-eyebrow"><a href="<?php echo esc_url( get_term_link( $brand ) ); ?>"><?php echo esc_html( $brand->name ); ?></a></p>
					<?php endif; ?>
					<h1 class="rdg-h1 rdg-product__title"><?php the_title(); ?></h1>
					<p class="rdg-product__meta">
						<?php if ( $product->get_sku() ) : ?>
							<span>Model / SKU: <strong><?php echo esc_html( $product->get_sku() ); ?></strong></span>
						<?php endif; ?>
						<?php if ( $cats ) : ?>
							<span><?php echo wp_kses_post( $cats ); ?></span>
						<?php endif; ?>
					</p>

					<div class="rdg-product__price">
						<?php if ( $call ) : ?>
							<p class="rdg-bigprice rdg-bigprice--call">Call for price</p>
							<?php if ( $phone ) : ?>
								<p><a class="rdg-link" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>">Call <?php echo esc_html( $phone ); ?> for today’s price</a></p>
							<?php endif; ?>
						<?php else : ?>
							<p class="rdg-bigprice<?php echo $product->is_on_sale() ? ' rdg-bigprice--sale' : ''; ?>"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
							<?php if ( $savings > 0 ) : ?>
								<span class="rdg-save">Save <?php echo wp_kses_post( wc_price( $savings, array( 'decimals' => 0 ) ) ); ?></span>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<div class="rdg-stockbox rdg-stockbox--<?php echo esc_attr( $stock['state'] ); ?>" role="status">
						<strong><?php echo esc_html( $stock['label'] ); ?></strong>
						<span>
							<?php
							switch ( $stock['state'] ) {
								case 'ok':
									echo null !== $stock['qty'] ? esc_html( sprintf( '%d available. Ready for pickup today.', $stock['qty'] ) ) : 'Ready for pickup today.';
									break;
								case 'low':
									echo 'Order soon — we’re almost out.';
									break;
								case 'back':
									echo 'Order now and we’ll ship or hold it as soon as it arrives.';
									break;
								case 'out':
									echo 'Call and we’ll tell you when it’s back, or suggest an alternative.';
									break;
								default:
									echo 'Call to confirm current availability.';
							}
							?>
						</span>
					</div>

					<?php if ( $product->get_short_description() ) : ?>
						<div class="rdg-product__short"><?php echo wp_kses_post( apply_filters( 'woocommerce_short_description', $product->get_short_description() ) ); ?></div>
					<?php endif; ?>

					<div class="rdg-product__buy">
						<?php woocommerce_template_single_add_to_cart(); ?>
					</div>

					<div class="rdg-product__ctas">
						<?php if ( ridgeline_crm_enabled() ) : ?>
							<button type="button" id="tw-request-info-btn" class="rdg-btn rdg-btn--accent rdg-btn--lg" data-product="<?php echo esc_attr( $product->get_name() ); ?>" data-product-id="<?php echo (int) $id; ?>"><?php echo esc_html( ridgeline_crm_cta_text() ); ?></button>
						<?php else : ?>
							<?php $contact = get_page_by_path( 'contact' ); ?>
							<a class="rdg-btn rdg-btn--accent rdg-btn--lg" href="<?php echo esc_url( add_query_arg( 'product', rawurlencode( $product->get_name() . ( $product->get_sku() ? ' (' . $product->get_sku() . ')' : '' ) ), $contact ? get_permalink( $contact ) : home_url( '/contact/' ) ) ); ?>">Request information</a>
						<?php endif; ?>
						<?php if ( $phone ) : ?>
							<a class="rdg-btn rdg-btn--outline rdg-btn--lg" href="<?php echo esc_attr( ridgeline_tel( $phone ) ); ?>"><?php echo ridgeline_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>Call <?php echo esc_html( $phone ); ?></a>
						<?php endif; ?>
					</div>

					<?php if ( ridgeline_mod( 'pickup_notice' ) ) : ?>
						<p class="rdg-pickup"><?php echo ridgeline_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( ridgeline_mod( 'pickup_notice' ) ); ?></span></p>
					<?php endif; ?>

					<div class="rdg-product__tools">
						<?php if ( ridgeline_compare_enabled() ) : ?>
							<label class="rdg-check">
								<input type="checkbox" data-rdg-compare="<?php echo (int) $id; ?>" data-name="<?php echo esc_attr( $product->get_name() ); ?>">
								<span class="rdg-check__box" aria-hidden="true"></span>
								<span class="rdg-check__label" data-rdg-compare-label>Compare</span>
							</label>
						<?php endif; ?>
						<div class="rdg-share">
							<span>Share:</span>
							<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( get_permalink() ) ); ?>" target="_blank" rel="noopener">Facebook<span class="screen-reader-text"> (opens in a new tab)</span></a>
							<a href="<?php echo esc_url( 'mailto:?subject=' . rawurlencode( $product->get_name() ) . '&body=' . rawurlencode( get_permalink() ) ); ?>">Email</a>
							<button type="button" class="rdg-link" data-rdg-copy="<?php echo esc_url( get_permalink() ); ?>"><?php echo ridgeline_icon( 'link', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>Copy link</button>
							<span class="rdg-share__msg" role="status" data-rdg-copy-msg></span>
						</div>
					</div>
				</div>
			</div>

			<?php if ( $replacement ) : ?>
				<section class="rdg-product__section" aria-labelledby="rdg-replacement-title">
					<h2 id="rdg-replacement-title" class="rdg-h2">Replacement model</h2>
					<ul class="rdg-grid rdg-grid--4"><li><?php get_template_part( 'template-parts/product-card', null, array( 'product' => $replacement ) ); ?></li></ul>
				</section>
			<?php endif; ?>

			<div class="rdg-product__details">
				<?php if ( get_the_content() ) : ?>
					<section class="rdg-product__section" aria-labelledby="rdg-desc-title">
						<h2 id="rdg-desc-title" class="rdg-h2">Description</h2>
						<div class="rdg-prose"><?php the_content(); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $specs ) : ?>
					<section class="rdg-product__section" aria-labelledby="rdg-specs-title">
						<h2 id="rdg-specs-title" class="rdg-h2">Specifications</h2>
						<table class="rdg-specs">
							<tbody>
								<?php foreach ( $specs as $label => $value ) : ?>
									<tr><th scope="row"><?php echo esc_html( $label ); ?></th><td><?php echo esc_html( $value ); ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</section>
				<?php endif; ?>

				<section class="rdg-product__section" aria-labelledby="rdg-avail-title">
					<h2 id="rdg-avail-title" class="rdg-h2">Availability by location</h2>
					<?php if ( ridgeline_mod( 'stock_widget' ) ) : ?>
						<script>window.targetWebShopifyProductId = <?php echo (int) $id; ?>;</script>
						<div id="product-stock-div" class="rdg-stockwidget" data-rdg-stock-widget></div>
					<?php endif; ?>
					<?php $locations = ridgeline_locations(); ?>
					<?php if ( $locations ) : ?>
						<div class="rdg-tablewrap" data-rdg-stock-fallback>
							<table class="rdg-loctable">
								<thead><tr><th scope="col">Location</th><th scope="col">Availability</th><th scope="col"><span class="screen-reader-text">Directions</span></th></tr></thead>
								<tbody>
									<?php foreach ( $locations as $location ) : ?>
										<tr>
											<th scope="row"><strong><?php echo esc_html( $location['name'] ); ?></strong><br><span><?php echo esc_html( $location['address'] ); ?></span></th>
											<td><?php echo ridgeline_stock_badge( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
											<td><a class="rdg-link" href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $location['address'] ) ); ?>" target="_blank" rel="noopener">Directions<span class="screen-reader-text"> to <?php echo esc_html( $location['name'] ); ?> (opens in a new tab)</span></a></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>
				</section>
			</div>

			<?php
			$related = array_filter( array_map( 'wc_get_product', wc_get_related_products( $id, 4 ) ) );
			if ( $related ) :
				?>
				<section class="rdg-product__section" aria-labelledby="rdg-related-title">
					<h2 id="rdg-related-title" class="rdg-h2">Related products</h2>
					<ul class="rdg-grid rdg-grid--4 rdg-scroller">
						<?php foreach ( $related as $related_product ) : ?>
							<li><?php get_template_part( 'template-parts/product-card', null, array( 'product' => $related_product ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php do_action( 'woocommerce_after_single_product' ); ?>
		</div>
	</main>

	<?php if ( $images ) : ?>
		<div class="rdg-lightbox" id="rdg-lightbox" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $product->get_name() . ' images' ); ?>" hidden>
			<button type="button" class="rdg-lightbox__close" data-rdg-lightbox-close><?php echo ridgeline_icon( 'close', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text">Close</span></button>
			<?php if ( count( $images ) > 1 ) : ?>
				<button type="button" class="rdg-lightbox__nav rdg-lightbox__nav--prev" data-rdg-lightbox-prev aria-label="Previous image"><?php echo ridgeline_icon( 'left', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				<button type="button" class="rdg-lightbox__nav rdg-lightbox__nav--next" data-rdg-lightbox-next aria-label="Next image"><?php echo ridgeline_icon( 'right', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			<?php endif; ?>
			<img src="" alt="<?php echo esc_attr( $product->get_name() ); ?>" data-rdg-lightbox-img>
		</div>
	<?php endif; ?>

	<?php if ( ridgeline_crm_enabled() ) : ?>
		<div id="tw-crm-popup" class="tw-crm-popup rdg-crm" role="dialog" aria-modal="true" aria-labelledby="rdg-crm-title" aria-hidden="true" hidden>
			<div class="tw-crm-popup__overlay" data-tw-crm-close></div>
			<div class="tw-crm-popup__panel" role="document">
				<div class="rdg-modal__head">
					<h2 id="rdg-crm-title"><?php echo esc_html( ridgeline_crm_cta_text() ); ?></h2>
					<button type="button" class="rdg-iconbtn" data-tw-crm-close><?php echo ridgeline_icon( 'close', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text">Close</span></button>
				</div>
				<p class="rdg-crm__about">About: <strong><?php echo esc_html( $product->get_name() ); ?></strong><?php echo $product->get_sku() ? ' · ' . esc_html( $product->get_sku() ) : ''; ?></p>
				<div id="leads-form-div"></div>
			</div>
		</div>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
