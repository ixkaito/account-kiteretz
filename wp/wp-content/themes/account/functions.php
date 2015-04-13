<?php
/*
================================================================================
	head title
================================================================================
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

/*
================================================================================
	ID
================================================================================
*/
function get_account_ID() {
	return sprintf( "%08d", get_the_ID() );
}

function the_account_ID() {
	echo esc_html( get_account_ID() );
}

/*
================================================================================
	status
================================================================================
*/
function get_status() {
	return get_field('status');
}

function the_status() {
	echo esc_attr( get_status() );
}

/*
================================================================================
	subtotal
================================================================================
*/
function get_subtotal() {

	$subtotal = 0;
	$rows     = get_field( 'table' );

	if ( $rows ) {
		foreach ( $rows as $key => $row ) {
			$sum = $row['number'] * $row['price'];
			$subtotal += $sum;
		}
		return $subtotal;
	} else {
		return false;
	}

}

/*
================================================================================
	tax
================================================================================
*/
function get_tax() {
	$tax      = 0;
	if ( get_field( 'tax' ) ) {
		$tax_rate = get_field( 'tax' ) * 0.01;
		$tax      = round( get_subtotal() * $tax_rate );
		return $tax;
	} else {
		return 0;
	}
}

/*
================================================================================
	total
================================================================================
*/
function get_total() {
	return get_subtotal() + get_tax();
}

/*
================================================================================
	honorific
================================================================================
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