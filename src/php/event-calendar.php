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

// SELECT DISTINCT post_id FROM wp_postmeta WHERE (meta_key='_arc_start_date' AND STR_TO_DATE(meta_value , '%Y-%m-%d') >= DATE('2000-01-01')) OR (meta_key='_arc_end_date' AND STR_TO_DATE(meta_value , '%Y-%m-%d') <= DATE('2018-01-01'))



