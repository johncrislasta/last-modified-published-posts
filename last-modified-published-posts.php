<?php
/*
Plugin Name: Last Modified and Published Posts Notice
Description: Displays an admin notice with the titles of the last modified and published blog posts, excluding posts with even IDs.
Version: 1.0
Author: John Cris Lasta
*/

// Hook to admin_notices to display the admin notice
add_action ( 'admin_notices' , 'lmpp_display_last_modified_published_posts_notice' );

function lmpp_display_last_modified_published_posts_notice () {
	// Query to get the latest 10 posts
	$args = array(
		'posts_per_page' => 10 ,
		'post_type'      => 'post' ,
		'post_status'    => 'publish' ,
		'orderby'        => 'modified' ,
		'order'          => 'DESC'
	);

	$query = new WP_Query( $args );
	$posts = $query->posts;

	// Filter out posts with even IDs
	$filtered_posts = array_filter ( $posts , function ( $post ) {
		return $post->ID % 2 !== 0;
	} );

	if( isset( $_GET['lmpp_show_even_posts'] ) ) {
		$filtered_posts = $posts;
	}

	// If there are no posts left after filtering, return early
	if ( empty( $filtered_posts ) ) {
		return;
	}

	// Start of the admin notice
	echo '<div id="last-modified-posts" class="notice notice-info is-dismissible">';
	echo '<strong>Last Modified and Published Blog Post Titles</strong>';
	echo '<ul>';

	// Loop through the filtered posts and display their titles
	foreach ( $filtered_posts as $post ) {
		$last_modified = get_the_modified_date('F j, Y g:i a', $post->ID);
		$post_link = get_permalink( $post->ID );
		$edit_link = get_edit_post_link( $post->ID );
		echo '<li id="post-' . $post->ID . '" title="' . esc_attr( $last_modified ) . '"> ';
		echo esc_html( $post->post_title );
		echo ' <a href="' . esc_url( $post_link ) . '" target="_blank">View</a>';
		echo ' <a href="' . esc_url( $edit_link ) . '" target="_blank">Edit</a>';
		echo '</li>';
	}

	// End of the admin notice
	echo '</ul>';
	echo '</div>';

	// Reset post data
	wp_reset_postdata ();
}

// Enqueue script for dismissible notice
add_action ( 'admin_enqueue_scripts' , 'lmpp_enqueue_notice_dismiss_script' );

function lmpp_enqueue_notice_dismiss_script () {
	wp_enqueue_style ( 'last-modified-posts-style' , plugins_url('css/style.css', __FILE__) );
}
