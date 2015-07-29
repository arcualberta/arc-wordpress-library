<?php
/*
  Plugin Name: ARC Image Grid
  Description: Displays image grid.
  Author: Mark McKellar
  Text Domain: arc-image-grid
 */
defined('ABSPATH') or die('No script kiddies please!');

class ARCImageGridCell {

    public $id = 0;
    public $name = 'MISSINGNO.';
    public $post_type = 'post';
    public $url = '#';
    public $metadata = array();

}

// Enable the arc image grid javascript and css files.
function arc_image_grid_scripts() {
    wp_enqueue_style('arc-image-grid-style', plugins_url('css/arc-image-grid.css', __FILE__), array());

    wp_enqueue_script('arc-image-grid', plugins_url('js/arc-image-grid.js', __FILE__));
}

add_action('wp_enqueue_scripts', 'arc_image_grid_scripts');

function arc_image_grid_add_grid($name, $img_width, $img_height, $max_col_count, $content, $query = false) {
    $id = uniqid("image_grid");
    ?>
    <div id="<?php echo $id ?>" class="arc-image-grid">
    </div>
    <script>
        function init<?php echo $id ?>() {
            var imageList = new Array();

    <?php
    global $wpdb;
    global $result;

    if ($query == false) {
        $query = "
    SELECT p.ID AS ID, p.post_title AS post_title, p.post_type AS post_type, p.guid AS url, pm.meta_key AS meta_key, pm.meta_value AS meta_value
    FROM $wpdb->posts p, $wpdb->postmeta pm
    WHERE p.ID IN (SELECT spm.post_id 
                    FROM $wpdb->postmeta spm 
                        WHERE spm.post_id = pm.post_id
                            AND spm.meta_key = '_arc_image_grid_name'
                            AND spm.meta_value = '" . $name . "')
        AND p.post_status = 'publish'
        AND p.post_date < NOW()
    ORDER BY p.ID, p.post_date DESC";
    }

    $results = $wpdb->get_results($query, OBJECT);
    $currentId = null;
    $currentObj = null;
    foreach ($results as $result) {
        if ($result->ID != $currentId) {

            if ($currentObj != null) {
                echo 'imageList.push(new ArcImageGridImage(' . json_encode($currentObj) . "));\n";
            }

            $currentObj = new ARCImageGridCell;
            $currentObj->id = $result->ID;
            $currentObj->name = $result->post_title;
            $currentObj->post_type = $result->post_type;
            $currentObj->url = $result->url;
            $currentId = $result->ID;
        }

        $currentObj->metadata[$result->meta_key] = $result->meta_value;
    }

    if ($currentObj != null) {
        echo 'imageList.push(new ArcImageGridImage(' . json_encode($currentObj) . "));";
    }
    ?>
            new ArcImageGrid('<?php echo $id ?>', <?php echo $img_width ?>, <?php echo $img_height ?>, <?php echo $max_col_count ?>, imageList, <?php echo json_encode($content) ?>);
        }
        init<?php echo $id ?>();
    </script>
    <?php
}

function arc_image_grid_add_grid_short($atts, $content = null) {
    $a = shortcode_atts(array(
        'name' => 'arc_image_grid',
        'img_width' => 100,
        'img_height' => 90,
        'max_col_count' => 3
            ), $atts);

    ob_start();
    arc_image_grid_add_grid($a['name'], $a['img_width'], $a['img_height'], $a['max_col_count'], $content, false);
    $output = ob_get_contents();
    ob_end_clean();
    
    return $output;
}

add_shortcode('arc_add_image_grid', 'arc_image_grid_add_grid_short');

// Custom meta boxes to post pages
function arc_image_grid_meta_box_add() {
    $screens = array('post', 'page');

    foreach ($screens as $screen) {
        add_meta_box('arc_image_grid_meta_data', 'ARC Image Grid', 'arc_image_grid_meta_box_callback', $screen);
    }
}

add_action('add_meta_boxes', 'arc_image_grid_meta_box_add');

function arc_image_grid_meta_box_callback($post) {
    wp_nonce_field('arc_image_grid_meta_box_save', 'arc_image_grid_meta_box_nonce');

    $name = get_post_meta($post->ID, '_arc_image_grid_name', true);

    echo '<label for="arc_image_grid_name_field">';
    echo 'Grid Name';
    echo '</label>';
    echo '<input type="text" id="arc_image_grid_name_field" name="arc_image_grid_name" value="' . esc_attr($name) . '" size="25" />';

    $img = get_post_meta($post->ID, '_arc_image_grid_img', true);

    echo '<br/>';

    echo '<label for="arc_image_grid_img_field">';
    echo 'Grid Name';
    echo '</label>';
    echo '<input type="hidden" id="arc_image_grid_img_field" name="arc_image_grid_img" value="' . esc_attr($img) . '" />';
    echo '<img id="arc_image_grid_img" style="width: 200px"/>';
    echo '<button id="arc_image_grid_img_button">Change Image</button>';
    ?>
    <script>
        function arcImageGridProperties_ImageClick(event) {
            var gallery_window = wp.media({
                title: 'Select an image to display on image grids.',
                library: {type: 'image'},
                multiple: false,
                button: {text: 'Select'}
            });

            gallery_window.on('select', function () {
                var selection = gallery_window.state().get('selection').first().toJSON();

                document.getElementById('arc_image_grid_img_field').value = selection.url;
                document.getElementById('arc_image_grid_img').src = selection.url;
            });

            gallery_window.open();

            event.preventDefault();
            return false;
        }

        function arcImageGridProperties_Initialize() {
            var value = document.getElementById('arc_image_grid_img_field').value;

            if (value && value !== null) {
                var img = document.getElementById('arc_image_grid_img');
                img.src = value;
            }

            var button = document.getElementById('arc_image_grid_img_button');
            button.addEventListener('click', arcImageGridProperties_ImageClick);
        }

        arcImageGridProperties_Initialize();
    </script>
    <?php
}

function arc_image_grid_meta_box_save($post_id) {
// Check if our nonce is set. This verifies it's from the correct screen
    if (!isset($_POST['arc_image_grid_meta_box_nonce'])) {
        return;
    }

// Check if the nonce is valid
    if (!wp_verify_nonce($_POST['arc_image_grid_meta_box_nonce'], 'arc_image_grid_meta_box_save')) {
        return;
    }

// If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

// Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

// Section to save the information
    $value = sanitize_text_field($_POST['arc_image_grid_name']);
    update_post_meta($post_id, '_arc_image_grid_name', $value);

    $value = sanitize_text_field($_POST['arc_image_grid_img']);
    update_post_meta($post_id, '_arc_image_grid_img', $value);
}

add_action('save_post', 'arc_image_grid_meta_box_save');
