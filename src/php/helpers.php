<?php
namespace Awl;
defined('ABSPATH') or die('No');


function set_attributes($default, $atts) {

	foreach($default as $key => $value) {
		if (array_key_exists($key, $atts)) {
			$atts[$key] = $atts[$key];
		} else {
			$atts[$key] = $value;
		}
	}

	return $atts;

}