<?php
/**
 * Head Title
 */
function get_head_title() {
	$title = '';
	if ( is_singular() ):
		$title .= get_the_time('ymd');
	else:
		$title .= get_bloginfo( 'name' );
	endif;
	return $title;
}

function head_title() {
	echo esc_html( get_head_title() );
}

/**
 * ID
 */
function get_account_ID() {
	return sprintf( "%08d", get_the_ID() );
}

function the_account_ID() {
	echo esc_html( get_account_ID() );
}

/**
 * Status
 */
function get_status() {
	return get_field('status');
}

function the_status() {
	echo esc_attr( get_status() );
}

/**
 * Withholding
 */
function get_withholding() {
	$withholding_rate = 0.1021;

	$subtotal = 0;
	$rows     = get_field( 'table' );
	$tax_rate = get_field( 'tax-rate' ) * 0.01;
	$exc_tax  = ( get_field( 'tax' ) === 'excluding' || get_field( 'tax' ) === 'none' ) ? 1 : 1 + $tax_rate;

	if ( $rows ) {
		foreach ( $rows as $key => $row ) {
			if ( ! $row['withholding'] ) continue;
			if ( $row['yen-per'] === 'per' ) {
				$sum = $sum * 0.01 * $subtotal;
			} else {
				$price = round( $row['price'] / $exc_tax );
				$sum = round( $row['number'] * $price );
			}
			$subtotal += $sum;
		}
	}
	return round( $subtotal * $withholding_rate );
}

/**
 * Subtotal
 */
function get_subtotal() {

	$subtotal = 0;
	$rows     = get_field( 'table' );
	$tax_rate = get_field( 'tax-rate' ) * 0.01;
	$exc_tax  = ( get_field( 'tax' ) === 'excluding' || get_field( 'tax' ) === 'none' ) ? 1 : 1 + $tax_rate;

	if ( $rows ) {
		foreach ( $rows as $key => $row ) {
			$price = 0;
			$sum = 0;
			if ( $row['yen-per'] === 'per' ) {
				$sum = $subtotal * $row['price'] * 0.01 * $row['number'];
			} else {
				$price = round( $row['price'] / $exc_tax );
				$sum = round( $row['number'] * $price );
			}
			$subtotal += $sum;
		}
	}
	return $subtotal;
}

/**
 * Tax
 */
function get_tax() {
	$tax_rate = get_field( 'tax-rate' ) * 0.01;
	$tax      = get_field( 'tax' ) === 'none' ? 0 : floor( get_subtotal() * $tax_rate );
	return $tax;
}

/**
 * Total
 */
function get_total() {
	return get_subtotal() + get_tax() - get_withholding();
}

/**
 * Honorific
 */
function get_honorific() {
	$honorific_obj = get_field_object('honorific');
	$honorific_val = get_field('honorific');
	if ( $honorific_obj && $honorific_val ) {
		$honorific_lab = $honorific_obj['choices'][$honorific_val];
		return $honorific_lab;
	} else {
		return false;
	}
}

function the_honorific() {
	echo esc_html( get_honorific() );
}

/**
 * Signature
 */
function get_signature() {
	$signature = get_field('signature');
	$html = '<img src="' . get_stylesheet_directory_uri() .'/assets/images/signature-' . $signature . '.png" />';

	if ( $signature && $signature !== 'none' ) {
		return $html;
	}
}

function the_signature() {
	echo get_signature();
}

/**
 * Customize ACF css
 */
function acf_style() {
?>
	<style type="text/css">
		.acf-th-yen-per.field_key-field_5833a0b5edd0e {
			width: 50px;
		}

		.acf-th-price.field_key-field_5385c1058b51e {
			width: 200px;
		}

		/* Price */
		.sub_field.field_key-field_5385c1058b51e input {
			text-align: right;
		}

		.sub_field.field_key-field_5385c1058b51e input[type="number"] {
			-moz-appearance: textfield;
		}

		.sub_field.field_key-field_5385c1058b51e input[type="number"]::-webkit-outer-spin-button,
		.sub_field.field_key-field_5385c1058b51e input[type="number"]::-webkit-inner-spin-button {
			-webkit-appearance: none;
			margin: 0;
		}

		.acf-th-number.field_key-field_5385c0aa8b51b,
		.acf-th-unit.field_key-field_5385c0fb8b51d {
			width: 100px;
		}

		#acf-instalment .label {
			display: none;
		}

		#acf-instalment .true_false {
			font-weight: bold;
		}

		.acf-th-current.field_key-field_5833c7d70da25 {
			text-align: center;
			width: 45px;
		}

		.sub_field.field_type-true_false.field_key-field_5833c7d70da25 {
			text-align: center;
		}
	</style>
<?php
}
add_action( 'acf/input/admin_head', 'acf_style' );
