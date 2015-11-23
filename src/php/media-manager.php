<?php
namespace Awl;
defined('ABSPATH') or die('No');

function get_title_desc_media($args) {

	$result = "";

	if (!array_key_exists("data", $args)) {
		return $result;
	}

	// container_id
	// container_class
	// image_class
	// title_class
	// content_class

	if (!array_key_exists('container_id', $args)) {
		$args['container_id'] = '';
	}
	if (!array_key_exists('container_class', $args)) {
		$args['container_class'] = '';
	}
		if (!array_key_exists('post_class', $args)) {
		$args['post_class'] = '';
	}
	if (!array_key_exists('image_class', $args)) {
		$args['image_class'] = '';
	}
	if (!array_key_exists('title_class', $args)) {
		$args['title_class'] = '';
	}
	if (!array_key_exists('content_class', $args)) {
		$args['content_class'] = '';
	}
	if (!array_key_exists('text_class', $args)) {
		$args['text_class'] = '';
	}


	$result .= "<div id='".$args['container_id']."' class='".$args['container_class']."'>";

	foreach ($args['data'] as $post) {		
		$result .= 	"<div class='".$args['post_class']."'>";
		$result .= 	"<div style='background: url(\"".$post->_arc_image_grid_img."\")' class='".$args['image_class']."' >";
		$result .= 	"</div>";
		$result .= 	"<div class='".$args['content_class']."''>";
		$result .= 		"<div class='".$args['title_class']."'>";
		$result .= 			$post->post_title;
		$result .= 		"</div>";
		$result .= 		"<div class='".$args['text_class']."'>";
		$result .=  		arc_limit_content("", $post->post_content, 50);
		$result .= 		"</div>";
		$result .= 		"<div>";
		$result .= 			"<a href='".$post->guid."'>More</a>";
		$result .= 		"</div>";
		$result .= 	"</div>";
		$result .= 	"</div>";		
	}

	$result .= "</div>";

	return $result;
}