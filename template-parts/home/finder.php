<?php
/**
 * Homepage equipment finder: category, brand, in-stock toggle, live match count.
 *
 * @package RIDGELINE
 */

$cats   = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) );
$brands = function_exists( 'ridgeline_brands' ) ? ridgeline_brands() : array();
$total  = (int) wp_count_posts( 'product' )->publish;
$hint   = str_replace( '{count}', number_format_i18n( $total ), ridgeline_mod( 'finder_hint' ) );
?>
<section class="rdg-finder-wrap" aria-labelledby="rdg-finder-title">
	<div class="rdg-wrap">
		<form class="rdg-finder" method="get" action="<?php echo esc_url( ridgeline_shop_url() ); ?>" data-rdg-finder>
			<div class="rdg-finder__head">
				<h2 id="rdg-finder-title" class="rdg-finder__title"><?php echo esc_html( ridgeline_mod( 'finder_heading' ) ); ?></h2>
				<p class="rdg-finder__hint"><?php echo esc_html( $hint ); ?></p>
			</div>
			<div class="rdg-finder__fields">
				<div class="rdg-field">
					<label for="rdg-finder-cat">Category</label>
					<select id="rdg-finder-cat" name="fcat[]" class="rdg-select">
						<option value=""><?php echo esc_html( ridgeline_mod( 'finder_cat_all' ) ); ?></option>
						<?php if ( ! is_wp_error( $cats ) ) : ?>
							<?php foreach ( $cats as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?> (<?php echo (int) $term->count; ?>)</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
				<div class="rdg-field">
					<label for="rdg-finder-brand">Brand</label>
					<select id="rdg-finder-brand" name="fbrand[]" class="rdg-select"<?php echo $brands ? '' : ' disabled'; ?>>
						<option value=""><?php echo esc_html( ridgeline_mod( 'finder_brand_all' ) ); ?></option>
						<?php foreach ( $brands as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?> (<?php echo (int) $term->count; ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>
				<label class="rdg-switch">
					<input type="checkbox" name="fstock[]" value="instock" role="switch">
					<span class="rdg-switch__track" aria-hidden="true"><span class="rdg-switch__thumb"></span></span>
					<span class="rdg-switch__label"><?php echo esc_html( ridgeline_mod( 'finder_stock' ) ); ?></span>
				</label>
				<button type="submit" class="rdg-btn rdg-btn--brand rdg-btn--lg rdg-finder__submit"><?php echo esc_html( ridgeline_mod( 'finder_button' ) ); ?> <span aria-hidden="true">↓</span></button>
			</div>
			<p class="rdg-finder__preview" data-rdg-finder-preview aria-live="polite" hidden></p>
		</form>
	</div>
</section>
