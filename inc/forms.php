<?php
/**
 * Contact and service request forms. Posted to admin-ajax and sent with wp_mail.
 *
 * @package RIDGELINE
 */

/**
 * Fields per form: name => [label, required].
 */
function ridgeline_form_fields( $form ) {
	$forms = array(
		'contact' => array(
			'first_name' => array( 'First name', true ),
			'last_name'  => array( 'Last name', true ),
			'email'      => array( 'Email', true ),
			'phone'      => array( 'Phone', false ),
			'topic'      => array( 'Topic', false ),
			'message'    => array( 'Message', true ),
		),
		'service' => array(
			'first_name' => array( 'First name', true ),
			'last_name'  => array( 'Last name', true ),
			'email'      => array( 'Email', true ),
			'phone'      => array( 'Phone', true ),
			'equipment'  => array( 'Equipment type', true ),
			'brand'      => array( 'Brand & model', false ),
			'service'    => array( 'Service needed', false ),
			'date'       => array( 'Preferred drop-off date', false ),
			'pickup'     => array( 'Pickup requested', false ),
			'message'    => array( 'Describe the problem', true ),
		),
		'newsletter' => array(
			'email' => array( 'Email', true ),
		),
	);
	return isset( $forms[ $form ] ) ? $forms[ $form ] : array();
}

function ridgeline_ajax_form() {
	check_ajax_referer( 'ridgeline_form', 'nonce' );

	$form   = isset( $_POST['form'] ) ? sanitize_key( wp_unslash( $_POST['form'] ) ) : '';
	$fields = ridgeline_form_fields( $form );
	if ( ! $fields ) {
		wp_send_json_error( array( 'message' => ridgeline_mod( 'contact_error' ) ), 400 );
	}
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( array( 'message' => ridgeline_mod( 'contact_success' ) ) );
	}

	$values  = array();
	$invalid = array();
	foreach ( $fields as $name => $meta ) {
		$raw   = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$value = 'message' === $name ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		if ( 'email' === $name && '' !== $value && ! is_email( $value ) ) {
			$invalid[] = $name;
		}
		if ( $meta[1] && '' === trim( $value ) ) {
			$invalid[] = $name;
		}
		$values[ $name ] = $value;
	}
	if ( $invalid ) {
		wp_send_json_error( array( 'message' => 'Please check the highlighted fields.', 'fields' => array_values( array_unique( $invalid ) ) ), 422 );
	}

	$recipient = 'service' === $form ? ridgeline_mod( 'service_recipient' ) : ridgeline_mod( 'contact_recipient' );
	if ( ! is_email( $recipient ) ) {
		$recipient = ridgeline_mod( 'dealer_email' );
	}
	if ( ! is_email( $recipient ) ) {
		$recipient = get_option( 'admin_email' );
	}

	$lines = array();
	foreach ( $fields as $name => $meta ) {
		if ( '' !== $values[ $name ] ) {
			$lines[] = $meta[0] . ': ' . $values[ $name ];
		}
	}
	$page = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';
	if ( $page ) {
		$lines[] = 'Sent from: ' . $page;
	}

	$from_name  = ridgeline_mod( 'contact_from_name' ) ? ridgeline_mod( 'contact_from_name' ) : get_bloginfo( 'name' );
	$from_email = is_email( ridgeline_mod( 'contact_from' ) ) ? ridgeline_mod( 'contact_from' ) : get_option( 'admin_email' );
	$name       = isset( $values['first_name'] ) ? trim( $values['first_name'] . ' ' . $values['last_name'] ) : $values['email'];
	$subjects   = array(
		'contact'    => ridgeline_mod( 'contact_subject' ),
		'service'    => 'Service request',
		'newsletter' => 'Newsletter signup',
	);
	$subject    = $subjects[ $form ] . ' — ' . $name;
	$headers    = array(
		'From: ' . $from_name . ' <' . $from_email . '>',
		'Reply-To: ' . $name . ' <' . $values['email'] . '>',
	);
	$success    = 'newsletter' === $form ? 'Thanks — you’re on the list.' : ridgeline_mod( 'contact_success' );

	if ( ! wp_mail( $recipient, $subject, implode( "\n", $lines ), $headers ) ) {
		wp_send_json_error( array( 'message' => ridgeline_mod( 'contact_error' ) ), 500 );
	}
	wp_send_json_success( array( 'message' => $success ) );
}
add_action( 'wp_ajax_ridgeline_form', 'ridgeline_ajax_form' );
add_action( 'wp_ajax_nopriv_ridgeline_form', 'ridgeline_ajax_form' );

/**
 * Hidden fields every theme form needs.
 */
function ridgeline_form_hidden( $form ) {
	printf( '<input type="hidden" name="form" value="%s">', esc_attr( $form ) );
	wp_nonce_field( 'ridgeline_form', 'nonce', false );
	echo '<div class="rdg-hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
}
