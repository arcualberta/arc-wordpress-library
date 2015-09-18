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