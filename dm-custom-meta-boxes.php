<?php

/*
Plugin Name: DM Custom Meta Boxes
Plugin URI: http://designmissoula.com/
Description: This is not just a plugin, awesome.
Author: Bradford Knowlton
Version: 1.0.1
Author URI: http://bradknowlton.com/
GitHub Plugin URI: https://github.com/DesignMissoula/DM-custom-meta-boxes
*/

/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function DM_add_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'DM_sectionid',
			__( 'Page Title & Subtitle', 'DM_textdomain' ),
			'DM_meta_box_callback',
			$screen,
			'side',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'DM_add_meta_box' );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function DM_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'DM_save_meta_box_data', 'DM_meta_box_nonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$dm_page_title = get_post_meta( $post->ID, '_dm_page_title', true );
	$dm_page_sub_title = get_post_meta( $post->ID, '_dm_page_sub_title', true );

	echo '<label for="DM_page_title">';
	_e( 'Page Title', 'DM_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="DM_page_title" name="DM_page_title" value="' . esc_attr( $dm_page_title ) . '" size="25" />';

	echo '<label for="DM_page_sub_title">';
	_e( 'Page Sub Title', 'DM_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="DM_page_sub_title" name="DM_page_sub_title" value="' . esc_attr( $dm_page_sub_title ) . '" size="25" />';
	
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function DM_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['DM_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['DM_meta_box_nonce'], 'DM_save_meta_box_data' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */
	
	// Make sure that it is set.
	if ( ! isset( $_POST['DM_page_title'] ) || ! isset( $_POST['DM_page_sub_title'] ) ) {
		return;
	}

	// Sanitize user input.
	$dm_page_title = sanitize_text_field( $_POST['DM_page_title'] );
	$dm_page_sub_title = sanitize_text_field( $_POST['DM_page_sub_title'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, '_dm_page_title', $dm_page_title );
	update_post_meta( $post_id, '_dm_page_sub_title', $dm_page_sub_title );
}

add_action( 'save_post', 'DM_save_meta_box_data' );