<?php
/*
  Plugin Name: AWL
  Description: A container for commonly used graphical components on websites
  Author: <a href="http://omarrodriguez.org/">Omar Rodriguez</a> & <a href="http://www.markmckellar.com/">Mark McKellar</a>
  Version: 1.0
  Text Domain: arc-image-grid
 */
namespace Awl;
defined('ABSPATH') or die('No');

// Set globals
$arcEnableBootstrap = true;
$arcEnableJQuery = true;

class ARCPostCell {
    public $id = 0;
    public $name = 'MISSINGNO.';
    public $post_type = 'post';
    public $url = '#';
    public $metadata = array();
    public $excerpt = '';
    
    function get_post(){
        $post = get_post($this->id);
        
        return $post;
    }
}

// Add scripts
function arc_scripts(){
    global $arcEnableBootstrap;
    global $arcEnableJQuery;
    
    if($arcEnableJQuery){
        wp_enqueue_script( 'jquery_script', plugins_url('js/jquery.min.js', __FILE__), array() );
    }
    
    if($arcEnableBootstrap){
        wp_enqueue_style('bootstrap_style', plugins_url('css/bootstrap.min.css', __FILE__), array() );
        wp_register_script( 'bootstrap_script', plugins_url('js/bootstrap.min.js', __FILE__), array('jquery_script') );
    
        wp_enqueue_script('bootstrap_script');
    }

    wp_enqueue_style('awl_style', plugins_url('css/awl.css', __FILE__));
    wp_enqueue_script('awl_script', plugins_url('js/awl.js', __FILE__));
    wp_localize_script( 'awl_script', 'awlAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action('wp_enqueue_scripts', 'Awl\arc_scripts');

// Functions that can be resused
function arc_convert_content($content, $data){
    eval('$result = "' . $content . '";');
    return $result;
}

// Include components
include 'helpers.php';
include 'sections.php';
include 'carousel.php';
include 'image-grid.php';
include 'event-calendar.php';
include 'media-manager.php';
include 'awl-options.php';

// Custom meta boxes to post pages
function arc_meta_box_add() {
    $screens = array('post', 'page');

    foreach ($screens as $screen) {
        add_meta_box('arc_meta_data', 'ARC Wordpress Library', 'Awl\arc_meta_box_callback', $screen);
    }
}
add_action('add_meta_boxes', 'Awl\arc_meta_box_add');

function arc_meta_box_add_field($post, $id, $label, $type){
    $value = get_post_meta($post->ID, $id, true);
    $field = $id . "_field";
    
    echo '<div>';
    echo '<p><strong>' . $label . '</strong></p>';
    echo '<label class="screen-reader-text" for="' . $field . '">';
    echo $label;
    echo '</label>';
    
    switch($type){
      case 'image':
          echo '<input type="hidden" id="' . $field . '" name="' . $id . '" value="' . esc_attr($value) . '" />';
          echo '<img id="' . $id . '" style="width: 200px"/>';
          echo '<div><button id="' . $id . '_button">Change Image</button><div>';
          //TODO: Add script for button
          break;
      
      case 'textarea':
          echo '<textarea id="' . $field . '" name="' . $id . '">';
          echo htmlspecialchars($value);
          echo '</textarea>';
          break;
      
      default:
          echo '<input type="text" id="' . $field . '" name="' . $id . '" value="' . esc_attr($value) . '" size="25" />';
    }
    
    echo '</div>';
}

function get_meta_values() {


    $options = get_option('awl_options', '[]');
    $meta_values = json_decode($options['awl_meta_json'], true);
    return $meta_values;
}

function arc_meta_box_callback($post) {
    wp_nonce_field('Awl\arc_meta_box_save', 'arc_meta_box_nonce');;
    
    wp_nonce_field('Awl\arc_meta_box_save', 'arc_meta_box_nonce');;    
    $meta_values = get_meta_values();
    $meta_count = count($meta_values);
    for ($i=0; $i < $meta_count; ++$i) {
        $meta_value = $meta_values[$i];
        arc_meta_box_add_field($post, $meta_values[$i]["meta"], $meta_values[$i]["name"], $meta_values[$i]["type"]);
    }
    
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

        function simplifyKey(text) {
            return text.toLowerCase().trim().replace(/\s+/, " ");
        }

        // show or hide options based on selected categories

        function showOptions(options) {

            var categoriesChecked = {};

            jQuery("#categorychecklist input").each(function(i, e){
                var key = simplifyKey(jQuery(e).parent()[0].innerText);
                categoriesChecked[key] = jQuery(e)[0].checked;
            });

            jQuery.each(options, function(i, option){
                showOption = false;
                if (option['categories'].length === 0) {
                    showOption = true;
                } else {
                    jQuery.each(option['categories'], function(j, category){
                        // console.log(category);
                        var key = simplifyKey(category);
                        if (categoriesChecked[key] === true) {
                            showOption = true;
                            return;
                        }
                    });    
                }
                
                //  show meta as id
                if (showOption) {
                    jQuery("[name="+option.meta+"]").parent().show();
                    console.log("show " + option.meta)
                } else {
                    jQuery("[name="+option.meta+"]").parent().hide();
                    console.log("jide " + option.meta)
                }
            });
        }

        jQuery(function(){
            var options = <?php print get_option('awl_options', '[]')['awl_meta_json']; ?> ;
            jQuery("#categorychecklist input").change(function(){
                showOptions(options)
            });

            showOptions(options);
        });

    </script>
    <?php
}

function arc_meta_box_save($post_id) {
// Check if our nonce is set. This verifies it's from the correct screen
    if (!isset($_POST['arc_meta_box_nonce'])) {
        return;
    }

// Check if the nonce is valid
    if (!wp_verify_nonce($_POST['arc_meta_box_nonce'], 'Awl\arc_meta_box_save')) {
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
    $meta_values = get_meta_values();
    $meta_count = count($meta_values);
    for ($i=0; $i < $meta_count; ++$i) {
        $meta_value = $meta_values[$i];
        $value = sanitize_text_field($_POST[$meta_value["meta"]]);
        update_post_meta($post_id, $meta_value["meta"], $value);
    }
}
add_action('save_post', 'Awl\arc_meta_box_save');