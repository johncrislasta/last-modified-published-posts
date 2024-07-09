<?php
/**
 * Plugin Name: Last Modified and Published Posts Notice
 * Description: Displays an admin notice with the titles of the last modified and published blog posts, excluding posts with even IDs.
 * Version: 1.0
 * Author: John Cris Lasta
 */

// Hook to admin_notices to display the admin notice
add_action( 'admin_notices', 'lmpp_display_last_modified_published_posts_notice' );

function lmpp_display_last_modified_published_posts_notice() {
	// Check if the notice has been dismissed by the current user
	if ( get_user_meta( get_current_user_id(), 'dismissed_last_modified_posts_notice', true ) ) {
		return;
	}

	// Query to get the latest 10 posts
	$args = array(
		'posts_per_page' => 10,
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'orderby'        => 'modified',
		'order'          => 'DESC',
	);

	$query = new WP_Query( $args );
	$posts = $query->posts;

	// Filter out posts with even IDs
	$filtered_posts = array_filter(
		$posts,
		function ( $post ) {
			return 0 !== $post->ID % 2;
		}
	);

	if ( isset( $_GET['lmpp_show_even_posts'] ) ) {
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
		$last_modified = get_the_modified_date( 'F j, Y g:i a', $post->ID );
		$post_link     = get_permalink( $post->ID );
		$edit_link     = get_edit_post_link( $post->ID );
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
	wp_reset_postdata();
}

// Enqueue script for dismissible notice
add_action( 'admin_enqueue_scripts', 'lmpp_enqueue_notice_dismiss_script' );

function lmpp_enqueue_notice_dismiss_script() {
	wp_enqueue_style( 'last-modified-posts-style', plugins_url( 'css/style.css', __FILE__ ) );
	wp_enqueue_script( 'last-modified-posts-notice-dismiss', plugins_url( 'js/notice-dismiss.js', __FILE__ ), array( 'jquery' ), null, true );
}

// Handle AJAX request to dismiss the notice
add_action( 'wp_ajax_dismiss_last_modified_posts_notice', 'lmpp_dismiss_last_modified_posts_notice' );

function lmpp_dismiss_last_modified_posts_notice() {
	update_user_meta( get_current_user_id(), 'dismissed_last_modified_posts_notice', true );
	wp_send_json_success();
}

// Check for query parameter to reset the dismissed state
add_action( 'admin_init', 'lmpp_check_reset_notice_query' );

function lmpp_check_reset_notice_query() {
	if ( 'true' === isset( $_GET['lmpp_reset_last_modified_notice'] ) && $_GET['lmpp_reset_last_modified_notice'] ) {
		delete_user_meta( get_current_user_id(), 'dismissed_last_modified_posts_notice' );
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>Last Modified Posts Notice has been reset and will be shown again.</p></div>';
			}
		);
	}
}


// Hook to add custom action link
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'lmpp_add_reset_notice_link' );

// Add reset link to plugin action links
function lmpp_add_reset_notice_link( $links ) {
	// Check if the notice has been dismissed by the current user
	if ( ! get_user_meta( get_current_user_id(), 'dismissed_last_modified_posts_notice', true ) ) {
		return $links;
	}

	$reset_link = '<a href="' . add_query_arg( 'lmpp_reset_last_modified_notice', 'true', admin_url( 'plugins.php' ) ) . '">Reset Notice</a>';
	array_unshift( $links, $reset_link );

	return $links;
}
