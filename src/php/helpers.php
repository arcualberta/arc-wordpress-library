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

function truncate_string($string, $n) {
	if (strlen($string) < $n) {
		return $string;
	}
	return implode(" ", array_slice(explode(" ", substr($string, 0, $n)), 0, -1)) . "...";
}