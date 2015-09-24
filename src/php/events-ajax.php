<?php


function events() {

	$start_date = strval( $_REQUEST['start_date'] );
	$end_date = strval( $_REQUEST['end_date'] );

	global $wpdb;

	// this query does not take into account all possible overlaps
	$query = "
		SELECT DISTINCT $wpdb->posts.*, $wpdb->postmeta.* 
		FROM $wpdb->posts, $wpdb->postmeta
		WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
		AND 
		(($wpdb->postmeta.meta_key = '_arc_start_date' AND STR_TO_DATE($wpdb->postmeta.meta_value, '%Y-%m-%d') >= DATE('" . $start_date . "'))
		OR
		(($wpdb->postmeta.meta_key = '_arc_end_date' AND STR_TO_DATE($wpdb->postmeta.meta_value, '%Y-%m-%d') <= DATE('" . $end_date . "'))))
	";

	$posts = $wpdb->get_results($query);

	wp_send_json($posts); // encode to JSON and send response
}
add_action('wp_ajax_events', 'events' ); // executed when logged in
add_action('wp_ajax_nopriv_events', 'events' ); // executed when logged out