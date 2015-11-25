<?php
namespace Awl;
defined('ABSPATH') or die('No');
$arc_carousel_array = array();

function arc_carousel_array_push($currentObj){
    global $arc_carousel_array;
    
    array_push($arc_carousel_array, $currentObj);
}

function arc_create_carousel($id, $data, $content) { 
    ?>
<div id="<?php echo $id ?>" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
        <?php foreach ($data as $key => $value) { ?>
        <li dtata-target="#<?php echo $id ?>" data-slide-to="<?php echo $key ?>" <?php if($key == 0) { echo 'class="active"'; } ?>></li>
        <?php } ?>
    </ol>
    <div class="carousel-inner" role="listbox">
        <?php foreach ($data as $key => $value) { ?>
            <div class="item<?php if($key == 0) { echo ' active'; } ?>">
                <?php echo arc_convert_content($content, $value); ?>
            </div>
        <?php } ?>
    </div>
    <a class="left carousel-control" href="#<?php echo $id ?>" role="button" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="right carousel-control" href="#<?php echo $id ?>" role="button" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>
    <?php
}

function arc_image_carousel($id, $data, $imagePath, $titlePath = '', $descriptionPath = ''){
    $content = "<div class='arc-carousel-image' style='background-image: url($imagePath)'>";
    $content .= "<div class='carousel-caption'>";
    $content .= "<h1>$titlePath</h1>";
    $content .= "<p>$descriptionPath</p>";
    $content .= '</div>';
    $content .= '</div>';
    
    arc_create_carousel($id, $data, $content);
}

function arc_image_carousel_by_grid_name($id, $gridName){
    global $arc_carousel_array;
    $arc_carousel_array = array();
    
    arc_image_grid_get_entries($gridName, 'Awl\arc_carousel_array_push');
    arc_image_carousel($id, $arc_carousel_array, '{$data->metadata["_arc_image_grid_img"]}', '$data->name', '$data->excerpt');
}

function arc_image_carousel_by_category($id, $categoryName, $limit = 10){
    global $arc_carousel_array;
    $arc_carousel_array = array();
    
    arc_get_posts_by_category($categoryName, 'Awl\arc_carousel_array_push', false, $limit);
    arc_image_carousel($id, $arc_carousel_array, '{$data->metadata["_arc_image_grid_img"]}', '$data->name', '$data->excerpt');
}

function generate_carousel($args) {
    $result = "";

    if (!array_key_exists("data", $args)) {
        return $result;
    }
    if (!array_key_exists("carousel_id", $args)) {
        $args['carousel_id'] = '';
    }

    // add start of carousel
    $result .= '<div id="'.$args['carousel_id'].'" class="carousel slide" data-ride="carousel">';
    
    // add indicators

    $result .= '<ol class="carousel-indicators">';

    $post_count = count($args['data']);

    if ($post_count > 0) {
        $result .= '<li data-target="#'.$args['carousel_id'].'" data-slide-to="0" class="active"></li>';    
        for ($i=1; $i<$post_count; ++$i) {
            $result .= '<li data-target="#'.$args['carousel_id'].'" data-slide-to="'.$i.'"></li>';    
        }
    }
    $result .= '</ol>';



    // add slides
    if ($post_count > 0) {
        $result .= '<div class="carousel-inner" role="listbox">';
        
        // 0
        $result .= '<div class="item active">';
        $result .= $args['process_slide']($args, 0);
        $result .= '</div>';
        // rest
        for ($i=1; $i<$post_count; ++$i) {
            $result .= '<div class="item">';
            $result .= $args['process_slide']($args, $i);
            $result .= '</div>';
        }

        $result .= '</div>';
    }

    // add controls and end

    $result .= '<a class="left carousel-control" href="#'.$args['carousel_id'].'" role="button" data-slide="prev">';
    $result .= '<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>';
    $result .= '<span class="sr-only">Previous</span>';
    $result .= '</a>';
    $result .= '<a class="right carousel-control" href="#'.$args['carousel_id'].'" role="button" data-slide="next">';
    $result .= '<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>';
    $result .= '<span class="sr-only">Next</span>';
    $result .= '</a>';
    $result .= '</div>';


    return $result;
}

function generate_media_content($args, $i) {

    $data = $args['data'][$i];
    $media_class = $args['media_class'];
    $media_container = $args['media_container'];

    $result = '';
    $result .= '<div class="media '.$media_container.'">';
    $result .= '<div class="media-left">';    
    // $result .= "<div style='width: 200px; height: 200px; background-image: url(\"".$data->_arc_image_grid_img."\");'></div>";
    $result .= "<div class='".$media_class."' style='background-image: url(\"".$data->_arc_image_grid_img."\");'></div>";
    $result .= '</div>';
    $result .= '<div class="media-body">';
    $result .= '<h4 class="media-heading"><a href="'.$data->guid.'">'.$data->post_title.'</a></h4>';
    $result .= '<div>'.$data->post_content.'</div>';
    $result .= '</div>';
    $result .= '</div>';

    return $result;

}


// args:
// data
// carousel_id
// media_class

function get_media_carousel($args) {
    

    if (!array_key_exists("data", $args)) {
        return '';
    }

    if (!array_key_exists("carousel_id", $args)) {
        $args['carousel_id'] = '';
    }
    if (!array_key_exists("media_class", $args)) {
        $args['media_class'] = '';
    }
        if (!array_key_exists("media_container", $args)) {
        $args['media_container'] = '';
    }



    $args['process_slide'] = function($args, $i) {
        return generate_media_content($args, $i);
    };

    $result = generate_carousel($args);

    return $result;
}
