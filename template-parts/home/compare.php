<?php
/**
 * Homepage: compare specs teaser table.
 *
 * @package RIDGELINE
 */

$columns = ridgeline_rows( ridgeline_mod( 'cmp_columns' ) );
$rows    = ridgeline_rows( ridgeline_mod( 'cmp_rows' ) );
if ( ! $columns || ! $rows ) {
	return;
}
$count = count( $columns );

/**
 * Index of the best value in a row, or -1.
 */
$best_index = static function ( $values, $mode ) {
	if ( 'high' !== $mode && 'low' !== $mode ) {
		return -1;
	}
	$best = -1;
	$best_value = null;
	foreach ( $values as $i => $value ) {
		$number = (float) preg_replace( '/[^0-9.]/', '', (string) $value );
		if ( ! $number ) {
			continue;
		}
		if ( null === $best_value || ( 'high' === $mode ? $number > $best_value : $number < $best_value ) ) {
			$best_value = $number;
			$best       = $i;
		}
	}
	return $best;
};
?>
<section class="rdg-section rdg-cmpteaser" aria-labelledby="rdg-cmp-title">
	<div class="rdg-wrap">
		<div class="rdg-section__head rdg-section__head--stack">
			<?php if ( ridgeline_mod( 'cmp_eyebrow' ) ) : ?>
				<p class="rdg-eyebrow"><?php echo esc_html( ridgeline_mod( 'cmp_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2 id="rdg-cmp-title" class="rdg-h2"><?php echo esc_html( ridgeline_mod( 'cmp_heading' ) ); ?></h2>
			<?php if ( ridgeline_mod( 'cmp_text' ) ) : ?>
				<p><?php echo esc_html( ridgeline_mod( 'cmp_text' ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="rdg-tablewrap" tabindex="0" role="region" aria-labelledby="rdg-cmp-title">
			<table class="rdg-cmptable">
				<thead>
					<tr>
						<th scope="col"><span class="screen-reader-text">Specification</span></th>
						<?php foreach ( $columns as $col ) : ?>
							<th scope="col"><span class="rdg-cmptable__brand"><?php echo esc_html( $col[0] ); ?></span><?php echo isset( $col[1] ) ? esc_html( $col[1] ) : ''; ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $rows as $row ) :
						$values = array_slice( array_pad( array_slice( $row, 1 ), $count + 1, '' ), 0, $count );
						$mode   = isset( $row[ $count + 1 ] ) ? strtolower( $row[ $count + 1 ] ) : 'none';
						$best   = $best_index( $values, $mode );
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $row[0] ); ?></th>
							<?php foreach ( $values as $i => $value ) : ?>
								<td<?php echo $i === $best ? ' class="is-best"' : ''; ?>><?php echo esc_html( $value ); ?><?php echo $i === $best ? ' <span class="rdg-best">Best</span>' : ''; ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php if ( ridgeline_mod( 'cmp_button' ) ) : ?>
			<p class="rdg-cmpteaser__cta"><a class="rdg-btn rdg-btn--outline" href="<?php echo esc_url( ridgeline_url( ridgeline_mod( 'cmp_url' ) ) ); ?>"><?php echo esc_html( ridgeline_mod( 'cmp_button' ) ); ?></a></p>
		<?php endif; ?>
	</div>
</section>
