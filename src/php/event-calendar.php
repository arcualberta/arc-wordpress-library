<?php namespace Awl
defined('ABSPATH') or die('No');


function add_event_calendar($atts, $content = null) {
	$a = shortcode_atts(array(
		'name' => 'awl_event_calendar'
	), $atts);
	ob_start();
	?>
		<script>
			new awl.eventCalendar(<?php namespace Awl echo $a['name']; ?>);
		</script>
	<?php namespace Awl
	return ob_get_clean();

}

add_shortcode('awl_add_event_calendar', 'add_event_calendar');