<?php
defined('ABSPATH') or die('No script kiddies please!');
$arc_carousel_array = array();

function arc_carousel_array_push($currentObj){
    global $arc_carousel_array;
    
    array_push($arc_carousel_array, $currentObj);
}

function arc_carousel_scripts() {
    wp_enqueue_style('arc-carousel-style', plugins_url('css/arc-carousel.css', __FILE__), array());
    wp_enqueue_script('arc-carousel', plugins_url('js/arc-carousel.js', __FILE__), array('arc'));
}
add_action('wp_enqueue_scripts', 'arc_carousel_scripts');

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
                <?php arc_convert_content($content, $value); ?>
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
    
    arc_image_grid_get_entries($gridName, 'arc_carousel_array_push');
    arc_image_carousel($id, $arc_carousel_array, '{$data->metadata["_arc_image_grid_img"]}', '$data->name', 'TODO: Obtain page data');
}

function arc_image_carousel_by_category($id, $categoryName, $limit = 10){
    global $arc_carousel_array;
    $arc_carousel_array = array();
    
    arc_get_posts_by_category($categoryName, 'arc_carousel_array_push', false, $limit);
    arc_image_carousel($id, $arc_carousel_array, '{$data->metadata["_arc_image_grid_img"]}', '$data->name', 'TODO: Obtain page data');
}
