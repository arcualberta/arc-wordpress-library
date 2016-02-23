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
    

    if (!array_key_exists("data", $args) || is_null($args['data'])) {
        return $result;
    }
    
    $post_count = count($args['data']);
    if (!array_key_exists("items_per_slide", $args)) {
        $args['items_per_slide'] = 1;
    }    
    $slide_count = $post_count / $args[items_per_slide];
    

    if (!array_key_exists("carousel_id", $args)) {
        $args['carousel_id'] = '';
    }

    if (!array_key_exists("carousel_class", $args)) {
        $args['carousel_class'] = '';
    }

    // add start of carousel
    $result .= '<div id="'.$args['carousel_id'].'" class="'.$args['carousel_class'].'  carousel slide" data-ride="carousel">';
    
    // add indicators

    if ($slide_count > 1) {
        $result .= '<ol class="carousel-indicators">';

        

        if ($slide_count > 0) {
            $result .= '<li data-target="#'.$args['carousel_id'].'" data-slide-to="0" class="active"></li>';    
            for ($i=1; $i<$slide_count; ++$i) {
                $result .= '<li data-target="#'.$args['carousel_id'].'" data-slide-to="'.$i.'"></li>';    
            }
        }
        $result .= '</ol>';
    }



    // add slides
    if ($slide_count > 0) {
        $result .= '<div class="carousel-inner" role="listbox">';
        
        // 0
        $i = 0;
        $result .= '<div class="item active">';

        list($buffer, $i) = $args['process_slide']($args, $i);
        $result .= $buffer;
        $result .= '</div>';
        // rest
        
        while ($i<$post_count) {
            $result .= '<div class="item">';
            list($buffer, $i) = $args['process_slide']($args, $i);
            $result .= $buffer;
            $result .= '</div>';
        }

        $result .= '</div>';
    }

    // add controls and end
    if ($slide_count > 1) {
        $result .= '<a class="left carousel-control" href="#'.$args['carousel_id'].'" role="button" data-slide="prev">';
        $result .= '<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>';
        $result .= '<span class="sr-only">Previous</span>';
        $result .= '</a>';
        $result .= '<a class="right carousel-control" href="#'.$args['carousel_id'].'" role="button" data-slide="next">';
        $result .= '<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>';
        $result .= '<span class="sr-only">Next</span>';
        $result .= '</a>';
    }

    $result .= '</div>';


    return $result;
}

function generate_media_content($args) {
    
    $data = $args['data'];
    $media_class = $args['media_class'];
    $media_container = $args['media_container'];
    
    if (!array_key_exists('show_author', $args)) {
        $args['show_author'] = true;
    }
    
    $result = '';
    $result .= '<div class="media '.$media_container.'">';
    $result .= '<div class="media-left">';
    $result .= "<div class='".$media_class."' style='background-image: url(\"".$data->_arc_image_grid_img."\");'></div>";
    $result .= '</div>';
    $result .= '<div class="media-body">';
    $result .= '<h4 class="media-heading"><a href="'.$data->guid.'">'.$data->post_title.'</a></h4>';
    $result .= '<div>';
    if (empty($data->_arc_description)) {
        // substitute for excerpt ?
        $result .= arc_limit_content("", $data->post_content, 500);
    } else {
        $result .= $data->_arc_description;
    }
    // $data->_arc_description.

    $author = trim($data->_arc_author);
    $time = trim($data->time);

    if ($args['show_author'] && ($author != "" || $time != "")) {
        $result .= "<br/><br/>Posted";
        if ($author != "") {
            $result .= " by " . $author;
        }

        if ($time != "") {
            $result .= " on " . $time;
        }
    }

    $result .= '</div>';
    if(array_key_exists('tags', $data)){
        $tags = $data->tags;
        $result .= "<div class='media-tags'>Tags:&nbsp;";
        foreach($tags as $key => $value){
            if($key > 0){
                $result .= ',&nbsp';
            }
            $result .= "<a href='" . get_tag_link($value->term_id) . "' >";
            $result .= htmlspecialchars($value->name);
            $result .= "</a>";
        }
        $result .= "</div>";
    }
    $result .= '</div>';
    $result .= '</div>';

    return $result;

}


// args:
// data
// carousel_id
// media_class

function get_media_carousel($args) {  

    if (!array_key_exists("data", $args) || is_null($args['data'])) {
        return '';
    }

    if (!array_key_exists("media_class", $args)) {
        $args['media_class'] = '';
    }
        if (!array_key_exists("media_container", $args)) {
        $args['media_container'] = '';
    }



    $args['process_slide'] = function($args, $i) {
        $args['data'] = $args['data'][$i];
        return array(generate_media_content($args), ++$i);
    };

    $result = generate_carousel($args);

    return $result;
}

function generate_background_image_content($args) {
    $data = $args['data'];
    $background_image_class = $args['background_image_class'];
    $container_class = $args['container_class'];
    $title_class = $args['title_class'];
    $description_class = $args['description_class'];
    $read_more_class = $args['read_more_class'];
    $read_more = $args['read_more'];
    $read_more_text = $args['read_more_text'];
    $max_description_chars = $args['max_description_chars'];

    $result = "";
    // background image
    $result .= "<div class='".$background_image_class."' style='background-image: url(".$data->_arc_image_grid_img.")'>";
    // inside content
    $result .= "<div class='".$container_class."'>";
    $result .= "<div class='".$title_class."'>";
    $result .= $data->post_title;
    $result .= "</div>";
    $result .= "<div class='".$description_class."'>";
    if (empty($data->_arc_description)) {
        // substitute for excerpt ?
        $result .= arc_limit_content("", $data->post_content, $max_description_chars);
    } else {
        $result .= arc_limit_content("", $data->_arc_description, $max_description_chars);
    }

    $result .= "</div>";

    $result .= "<div class='".$read_more_class."'>";
    if ($read_more) {
        $result .= "<a href='".$data->guid."'>";
        $result .= $read_more_text;
        $result .= "</a>";
    }
    $result .= "</div>";
    $result .= "</div>";
    $result .= "</div>";

    return $result;
}

function get_background_image_carousel($args) {

    if (!array_key_exists("data", $args) || is_null($args['data'])) {
        return '';
    }

    // 

    if (!array_key_exists("background_image_class", $args)) {
        $args['background_image_class'] = '';
    }

    if (!array_key_exists("container_class", $args)) {
        $args['container_class'] = '';
    }

    if (!array_key_exists("title_class", $args)) {
        $args['title_class'] = '';
    }

    if (!array_key_exists("description_class", $args)) {
        $args['description_class'] = '';
    }

    if (!array_key_exists("read_more_class", $args)) {
        $args['read_more_class'] = true;
    }

    if (!array_key_exists("read_more", $args)) {
        $args['read_more'] = true;
    }

    if (!array_key_exists("read_more_text", $args)) {
        $args['read_more_text'] = "Read more";
    }

    if (!array_key_exists("max_description_chars", $args)) {
        $args['max_description_chars'] = 500;
    }

    $args['process_slide'] = function($args, $i) {
        $args['data'] = $args['data'][$i];
        return array(generate_background_image_content($args), ++$i);
    };

    $result = generate_carousel($args);
    return $result;
}

function generate_multiple_image_content($args) {
    $data = $args['data'];
    $container_id = $args['container_id'];
    $container_class = $args['container_class'];
    $image_class = $args['image_class'];
    $post_class = $args['post_class'];
    $title_class = $args['title_class'];
    $description_class = $args['description_class'];
    $read_more_class = $args['read_more_class'];
    $read_more_text = $args['read_more_text'];
    $max_characters = $args['max_characters'];


    $result = "";

    $result .= "<div id='".$container_id."' class='".$container_class."'>";

    foreach ($data as $post) {      
        $result .= "<div class='".$post_class."'>";
        $result .= "<div clas='".$image_class."'style='background: url(\"".$post->_arc_image_grid_img."\")' class='".$image_class."' ></div>";
        $result .= "<div class='".$title_class."'>".$post->post_title."</div>";

        $description = $post->_arc_description;
        if (strlen($description) > $max_characters) {
            $description = substr($description, 0, intval($max_characters) - 3) . "...";
        }
        $result .= "<div class='".$description_class."'>".$description."</div>";
        $result .= "<div class='".$read_more_class."'>";
        $result .= "<a href='".$post->guid."'>".$read_more_text."</a>";
        $result .= "</div>";
        $result .= "</div>";
    }
    $result .=  "</div>";

    return $result;
}

function get_multiple_post_carousel($args) {

    if (!array_key_exists("data", $args) || is_null($args['data'])) {
        return '';
    }

    // classes ids

    if (!array_key_exists("container_id", $args)) {
        $args['container_id'] = '';
    }

    if (!array_key_exists("container_class", $args)) {
        $args['container_class'] = '';
    }

    if (!array_key_exists("image_class", $args)) {
        $args['image_class'] = '';
    }

    if (!array_key_exists("post_class", $args)) {
        $args['post_class'] = '';
    }

    if (!array_key_exists("title_class", $args)) {
        $args['title_class'] = '';
    }
    
    if (!array_key_exists("description_class", $args)) {
        $args['description_class'] = '';
    }
    
    if (!array_key_exists("read_more_class", $args)) {
        $args['read_more_class'] = '';
    }

    if (!array_key_exists("read_more_text", $args)) {
        $args['read_more_text'] = 'Read more';
    }

    if (!array_key_exists("max_characters", $args)) {
        $args['max_characters'] = INF;
    } elseif ($args['max_characters'] < 3) {
        $args['max_characters'] = 3;
    }


    // slide generation call

    $args['process_slide'] = function($args, $i) {
        $items_per_slide = $args['items_per_slide'];
        $last_item = $i + $items_per_slide;
        $args['data'] = array_slice($args['data'], $i, $items_per_slide );
        return array(generate_multiple_image_content($args), $last_item);
    };

    $result = generate_carousel($args);
    return $result;
}