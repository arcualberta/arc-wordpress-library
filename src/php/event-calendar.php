<?php 
namespace Awl;
defined('ABSPATH') or die('No');

function add_event_calendar($atts, $content = null) {
	$a = shortcode_atts(array(
		'name' => 'awl_event_calendar'
	), $atts);
	ob_start();
	?>
		<script>
			new awl.eventCalendar(<?php echo $a['name']; ?>);
		</script>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;

}

add_shortcode('awl_add_event_calendar', 'add_event_calendar');

function events() {

	$start_date = strval( $_GET['start_date'] );
	$end_date = strval( $_GET['end_date'] );
	$category = strval( $_GET['category'] );

	global $wpdb;

	$query = "
		SELECT DISTINCT			
			posts.post_title,
			posts.post_content,
			posts.guid,
			startmeta.meta_value AS _arc_start_date,
			endmeta.meta_value AS _arc_end_date,
			imagemeta.meta_value AS _arc_image_grid_img,
			venuemeta.meta_value AS _arc_venue
		FROM 
			$wpdb->posts posts, 
			$wpdb->postmeta startmeta		
		INNER JOIN 
			$wpdb->postmeta endmeta
			ON endmeta.post_id = startmeta.post_id
			AND endmeta.meta_key = '_arc_end_date'
		INNER JOIN 
			$wpdb->postmeta imagemeta
			ON imagemeta.post_id = startmeta.post_id
			AND imagemeta.meta_key = '_arc_image_grid_img'
		INNER JOIN 
			$wpdb->postmeta venuemeta
			ON venuemeta.post_id = startmeta.post_id
			AND venuemeta.meta_key = '_arc_venue'		
		INNER JOIN
			$wpdb->term_relationships term_relationships
			ON term_relationships.object_id = startmeta.post_id
		INNER JOIN
			$wpdb->term_taxonomy term_taxonomy
			ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
		INNER JOIN
			$wpdb->terms terms
			ON terms.term_id = term_taxonomy.term_id

		WHERE
			posts.post_status = 'publish'
			AND posts.post_type = 'post'
			AND startmeta.meta_key = '_arc_start_date'
			AND posts.ID = startmeta.post_id
			AND terms.name = '" . $category . "'
			AND (
				(
					STR_TO_DATE(startmeta.meta_value , '%Y-%m-%d') >= DATE('" . $start_date . "')
					AND
					STR_TO_DATE(startmeta.meta_value , '%Y-%m-%d') <= DATE('" . $end_date . "')
				)
				OR
				( 
					STR_TO_DATE(endmeta.meta_value , '%Y-%m-%d') <= DATE('" . $end_date . "')
					AND
					STR_TO_DATE(endmeta.meta_value , '%Y-%m-%d') >= DATE('" . $start_date . "')
				)
			)
			;
	";


	$posts = $wpdb->get_results($query);

	wp_send_json($posts); 
}

add_action('wp_ajax_events', 'Awl\events' ); 
add_action('wp_ajax_nopriv_events', 'Awl\events' ); 
