<?php
defined('ABSPATH') or die('No script kiddies please!');

// Enable the arc image grid javascript and css files.
function arc_image_grid_scripts() {
    wp_enqueue_style('arc-image-grid-style', plugins_url('css/arc-image-grid.css', __FILE__), array());

    wp_enqueue_script('arc-image-grid', plugins_url('js/arc-image-grid.js', __FILE__), array('arc'));
}

add_action('wp_enqueue_scripts', 'arc_image_grid_scripts');

function arc_image_grid_get_entries($name, $objectOutputFunction, $random = false, $limit = 100) {
    global $wpdb;
    global $result;

    $query = "
    SELECT p.ID AS ID, p.post_title AS post_title, p.post_type AS post_type, p.guid AS url, pm.meta_key AS meta_key, pm.meta_value AS meta_value
    FROM $wpdb->posts p, $wpdb->postmeta pm
    WHERE p.ID IN (SELECT spm.post_id 
                    FROM $wpdb->postmeta spm 
                        WHERE spm.post_id = pm.post_id
                            AND spm.meta_key = '_arc_image_grid_name'
                            AND spm.meta_value = '" . $name . "')
        AND p.post_status = 'publish'
        AND p.post_date < NOW() ";

    if ($random) {
        $query .= "ORDER BY " . rand() . " ^ p.ID "; // An exclusive or is used with a rand to keep meta-data grouped together.
    } else {
        $query .= "ORDER BY p.ID, p.post_date DESC ";
    }

    $query .= "LIMIT " . $limit;

    $results = $wpdb->get_results($query, OBJECT);
    $currentId = null;
    $currentObj = null;
    foreach ($results as $result) {
        if ($result->ID != $currentId) {

            if ($currentObj != null) {
                call_user_func($objectOutputFunction, $currentObj);
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
        json_encode($currentObj);
    }
}

function arc_image_grid_create_JS_cell($currentObj) {
    echo 'imageList.push(new ArcImage(' . json_encode($currentObj) . "));\n";
}

function arc_image_grid_add_grid($name, $img_width, $img_height, $max_col_count, $content, $button_text = "Read More", $random = false, $show_arrows = true, $timer_seconds = 15, $limit = 100) {
    $id = uniqid("image_grid");
    ?>
    <div id="<?php echo $id ?>_container" id="<?php echo $id ?>_left" class="arc-grid-container">
        <span class="arc-grid-button arc-grid-left invisible" id="<?php echo $id ?>_left" style="<?php if (!$show_arrows) {
        echo 'display: none;';
    } ?>"></span>
        <div id="<?php echo $id ?>" class="arc-image-grid">
        </div>
        <span class="arc-grid-button arc-grid-right invisible" id="<?php echo $id ?>_right" style="<?php if (!$show_arrows) {
        echo 'display: none;';
    } ?>"></span>
    </div>
    <script>
        function init<?php echo $id ?>() {
            var imageList = new Array();

    <?php
    arc_image_grid_get_entries($name, 'arc_image_grid_create_JS_cell', $random, $limit);
    ?>

            new ArcImageGrid('<?php echo $id ?>', <?php echo $img_width ?>, <?php echo $img_height ?>, <?php echo $max_col_count ?>, imageList, <?php echo json_encode($content) ?>, <?php echo json_encode($button_text) ?>, <?php echo $timer_seconds ?>);
        }
        arcCheckDocumentReady(init<?php echo $id ?>);
    </script>
    <?php
}

function arc_image_grid_add_grid_short($atts, $content = null) {
    $a = shortcode_atts(array(
        'name' => 'arc_image_grid',
        'img_width' => 100,
        'img_height' => 90,
        'max_col_count' => 3,
        'button_text' => 'Read More',
        'random' => false,
        'show_arrows' => true
            ), $atts);

    ob_start();
    arc_image_grid_add_grid($a['name'], $a['img_width'], $a['img_height'], $a['max_col_count'], $content, $a['button_text'], $a['random'], $a['show_arrows'], 15, 100);
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

add_shortcode('arc_add_image_grid', 'arc_image_grid_add_grid_short');
