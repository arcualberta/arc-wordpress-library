<?php
/*
  Plugin Name: ARC Image Grid
  Description: Displays image grid.
  Author: Mark McKellar
  Text Domain: arc-image-grid
 */
defined('ABSPATH') or die('No script kiddies please!');

// Enable the arc image grid javascript and css files.
function arc_image_grid_scripts() {
    wp_enqueue_style('arc-image-grid-style', plugins_url('css/arc-image-grid.css', __FILE__), array());

    wp_enqueue_script('arc-image-grid', plugins_url('js/arc-image-grid.js', __FILE__));
}

add_action('wp_enqueue_scripts', 'arc_image_grid_scripts');

function arc_image_grid_add_grid($name, $img_width, $img_height, $max_col_count) {
    $id = uniqid("image_grid");
    ?>
    <div id="<?php echo $id ?>" class="arc-image-grid">
    </div>
    <script>
        var imageList = new Array();
        for (var i = 0; i < 100; ++i) {
            var r = Math.floor(Math.random() * 255);
            var g = Math.floor(Math.random() * 128);
            var b = Math.floor(Math.random() * 100);
            var bgcolor = r.toString(16) + g.toString(16) + b.toString(16);
            var color = (255 - r).toString(16) + (255 - g).toString(16) + (255 - b).toString(16);
            var url = "http://placehold.it/<?php echo $img_width ?>x<?php echo $img_height ?>/" + bgcolor + "/" + color + "?text=image" + (i + 1);

            var obj = new ArcImageGridImage(url, null);

            imageList.push(obj);
        }
        new ArcImageGrid('<?php echo $id ?>', <?php echo $img_width ?>, <?php echo $img_height ?>, <?php echo $max_col_count ?>, imageList);
    </script>
    <?php
}

function arc_image_grid_add_grid_short($atts) {
    $a = shortcode_atts(array(
        'name' => 'arc_image_grid',
        'img_width' => 100,
        'img_height' => 90,
        'max_col_count' => 3,
            ), $atts);

    arc_image_grid_add_grid($a['name'], $a['img_width'], $a['img_height'], $a['max_col_count']);
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
    echo '<img src="' + esc_attr($img) + '"/>';
    echo '<button>Change Image</button>';
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
    $value = sanitize_text_field( $_POST['arc_image_grid_name'] );
    update_post_meta($post_id, '_arc_image_grid_name', $value);
}
add_action('save_post', 'arc_image_grid_meta_box_save');