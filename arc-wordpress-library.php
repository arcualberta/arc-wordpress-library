<?php
/*
  Plugin Name: ARC Wordpress Library
  Description: A container for commonly used graphical components on websites
  Author: Mark McKellar
  Text Domain: arc-image-grid
 */
defined('ABSPATH') or die('No script kiddies please!');
// Define globals
global $arcEnableBootstrap;

// Set globals
$arcEnableBootstrap = true;

class ARCPostCell {
    public $id = 0;
    public $name = 'MISSINGNO.';
    public $post_type = 'post';
    public $url = '#';
    public $metadata = array();
}

// Add scripts
function arc_scripts(){
    if($arcEnableBootstrap){
        wp_register_script( 'bootstrap', plugins_url('js/bootstrap.min.js'), array() );
    
        wp_enqueue_script('bootstrap');
    }
    
    wp_enqueue_script('arc', plugins_url('js/arc.js', __FILE__));
}
add_action('wp_enqueue_scripts', 'arc_scripts');

// Functions that can be resused
function arc_convert_content($content, $data){
    
}

// Include components
include 'arc-image-grid.php';

// Custom meta boxes to post pages
function arc_meta_box_add() {
    $screens = array('post', 'page');

    foreach ($screens as $screen) {
        add_meta_box('arc_meta_data', 'ARC Wordpress Library', 'arc_meta_box_callback', $screen);
    }
}
add_action('add_meta_boxes', 'arc_meta_box_add');

function arc_meta_box_add_field($post, $id, $label, $type){
    $value = get_post_meta($post->ID, $id, true);
    $field = $id . "_field";
    
    echo '<p>';
    echo '<strong>' . $label . '</strong>';
    echo '</p>';
    
    echo '<p>';
    echo '<label class="screen-reader-text" for="' . $field . '">';
    echo $label;
    echo '</label>';
    
    switch($type){
      case 'image':
          echo '<input type="hidden" id="' . $field . '" name="' . $id . '" value="' . esc_attr($value) . '" />';
          echo '<img id="' . $id . '" style="width: 200px"/>';
          echo '<button id="' . $id . '_button">Change Image</button>';
          //TODO: Add script for button
          break;
      
      default:
          echo '<input type="text" id="' . $field . '" name="' . $id . '" value="' . esc_attr($value) . '" size="25" />';
    }
    
    echo '</p>';
}

function arc_meta_box_callback($post) {
    wp_nonce_field('arc_meta_box_save', 'arc_meta_box_nonce');;
    
    arc_meta_box_add_field($post, '_arc_image_grid_img', 'Image', 'image');
    echo '<br/>';
    
    arc_meta_box_add_field($post, '_arc_image_grid_name', 'Grid Name', 'text');
    echo '<br/>';
    
    echo '<br><h3>Event Details</h3>';
    arc_meta_box_add_field($post, '_arc_start_date', 'Start Date', 'date');
    echo '<br/>';
    
    arc_meta_box_add_field($post, '_arc_end_date', 'End Date', 'date');
    echo '<br/>';
    
    arc_meta_box_add_field($post, '_arc_venue', 'Venue', 'text');
    echo '<br/>';

    ?>
    <script>
        function arcProperties_ImageClick(event) {
            var gallery_window = wp.media({
                title: 'Select an image to display on image grids.',
                library: {type: 'image'},
                multiple: false,
                button: {text: 'Select'}
            });

            gallery_window.on('select', function () {
                var selection = gallery_window.state().get('selection').first().toJSON();

                document.getElementById('_arc_image_grid_img_field').value = selection.url;
                document.getElementById('_arc_image_grid_img').src = selection.url;
            });

            gallery_window.open();

            event.preventDefault();
            return false;
        }

        function arcProperties_Initialize() {
            var value = document.getElementById('_arc_image_grid_img_field').value;

            if (value && value !== null) {
                var img = document.getElementById('_arc_image_grid_img');
                img.src = value;
            }

            var button = document.getElementById('_arc_image_grid_img_button');
            button.addEventListener('click', arcProperties_ImageClick);
        }

        arcProperties_Initialize();
    </script>
    <?php
}

function arc_meta_box_save($post_id) {
// Check if our nonce is set. This verifies it's from the correct screen
    if (!isset($_POST['arc_meta_box_nonce'])) {
        return;
    }

// Check if the nonce is valid
    if (!wp_verify_nonce($_POST['arc_meta_box_nonce'], 'arc_meta_box_save')) {
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
    $value = sanitize_text_field($_POST['_arc_image_grid_name']);
    update_post_meta($post_id, '_arc_image_grid_name', $value);

    $value = sanitize_text_field($_POST['_arc_image_grid_img']);
    update_post_meta($post_id, '_arc_image_grid_img', $value);
    
    $value = sanitize_text_field($_POST['_arc_start_date']);
    update_post_meta($post_id, '_arc_start_date', $value);
    
    $value = sanitize_text_field($_POST['_arc_end_date']);
    update_post_meta($post_id, '_arc_end_date', $value);
    
    $value = sanitize_text_field($_POST['_arc_venue']);
    update_post_meta($post_id, '_arc_venue', $value);
}
add_action('save_post', 'arc_meta_box_save');