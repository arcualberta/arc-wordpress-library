<?php

defined('ABSPATH') or die('No script kiddies please!');

function arc_sections_scripts() {
    
}

add_action('wp_enqueue_scripts', 'arc_sections_scripts');

function arc_get_posts_by_category($category, $objectOutputFunction, $random = false, $limit = 100) {
    global $wpdb;
    global $result;

    $query = "
    SELECT p.ID AS ID, p.post_title AS post_title, p.post_type AS post_type, p.guid AS url, pm.meta_key AS meta_key, pm.meta_value AS meta_value
    FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
    WHERE p.ID IN (SELECT tr.object_id
                    FROM $wpdb->terms t JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id JOIN $wpdb->term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                        WHERE tt.taxonomy = 'category'
                            AND t.slug = '" . $category . "')
        AND p.post_status = 'publish'
        AND p.post_date < NOW() ";

    if ($random) {
        $query .= "ORDER BY " . rand() . " ^ p.ID "; // An exclusive or is used with a rand to keep meta-data grouped together.
    } else {
        $query .= "ORDER BY p.ID, p.post_date DESC ";
    }

    $results = $wpdb->get_results($query, OBJECT);

    $currentId = null;
    $currentObj = null;
    $index = 0;
    foreach ($results as $result) {
        if ($result->ID != $currentId) {

            if ($currentObj != null) {
                call_user_func($objectOutputFunction, $currentObj);
                ++$index;
            }

            if ($index >= $limit) {
                $currentObj = null;
                break;
            }

            $currentObj = new ARCPostCell;
            $currentObj->id = $result->ID;
            $currentObj->name = $result->post_title;
            $currentObj->post_type = $result->post_type;
            $currentObj->url = $result->url;
            $currentId = $result->ID;
        }

        $currentObj->metadata[$result->meta_key] = $result->meta_value;
    }

    if ($currentObj != null) {
        call_user_func($objectOutputFunction, $currentObj);
    }
}

function arc_limit_content($data, $contentPath, $contentLimit, $breakChar = ".", $padding = "..."){
    $result = arc_convert_content($contentPath, $data);
    //$result = preg_replace("/<[^(p|br|h1|h2|h3|h4)][^>]*\>/i", "", $result); 
    $result = preg_replace("/<br[^>]+\>/i", "\n", $result); 
    $result = preg_replace("/<[^>]+\>/i", "", $result); 
    $result = trim($result);
    
    // Split the results to match the content limit. We will stop it at the periods
    if(strlen($result) > $contentLimit && false !== ($breakpoint = strpos($result, $breakChar, $contentLimit))){ // Is the breakpoint here in the line
        if($breakpoint < strlen($result) - 1){
            $result = substr($result, 0, $breakpoint) . $padding;
        }
    }
    
    $result = nl2br($result); 
    
    return $result;
}

function arc_create_section($id, $data, $imagePath, $titlePath = '', $contentPath = '', $urlPath = '', $classes = '', $isVertical = true){
    ?>
<div id="<?php echo $id ?>" class="arc-sections <?php echo $classes ?>">
    <?php
    foreach($data as $key => $value){
        ?>
    <div class="arc-section">
        <img src="<?php echo arc_convert_content($imagePath, $value)?>"/>
        <h4><?php echo arc_convert_content($titlePath, $value)?></h4>
        <div class='arc-section-content'>
            <?php echo arc_limit_content($value, $contentPath, 140) ?>
            <a class="arc-read-more" href="<?php echo arc_convert_content($urlPath, $value)?>">[Read More]</a>
        </div>
    </div>
            <?php
    }
        ?>
</div>
    <?php
}

function arc_section_by_category($id, $categoryName, $isVertical = true, $classes = '', $limit = 3) {
    global $arc_carousel_array;
    $arc_carousel_array = array();
    
    arc_get_posts_by_category($categoryName, 'arc_carousel_array_push', false, $limit);
    arc_create_section($id, $arc_carousel_array, '{$data->metadata["_arc_image_grid_img"]}', '$data->name', '{$data->get_post()->post_content}', '$data->url', $classes, $isVertical);
}