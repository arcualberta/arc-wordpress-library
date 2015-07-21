<?php
/*
Plugin Name: ARC Image Grid
Description: Displays image grid.
Author: Mark McKellar
Text Domain: arc-image-grid
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Enable the arc image grid javascript and css files.
function arc_image_grid_scripts(){
    wp_enqueue_style('arc-image-grid-style', plugins_url('css/arc-image-grid.css', __FILE__), array());
    
    wp_enqueue_script('arc-image-grid', plugins_url('js/arc-image-grid.js', __FILE__));
}
add_action( 'wp_enqueue_scripts', 'arc_image_grid_scripts' );

function arc_image_grid_add_grid($name, $img_width, $img_height, $max_col_count){
    $id = uniqid("image_grid");
    ?>
<div id="<?php echo $id ?>" class="arc-image-grid">
</div>
<script>
    new ArcImageGrid('<?php echo $id ?>', '<?php echo $name ?>', <?php echo $img_width ?>, <?php echo $img_height ?>, <?php echo $max_col_count ?>);
</script>
<?php
}

function arc_image_grid_add_grid_short($atts){
    $a = shortcode_atts(array(
            'name' => 'arc_image_grid',
            'img_width' => 100,
            'img_height' => 90,
            'max_col_count' => 3,
        ), $atts);
    
    arc_image_grid_add_grid($a['name'], $a['img_width'], $a['img_height'], $a['max_col_count']);
}
add_shortcode('arc_add_image_grid', 'arc_image_grid_add_grid_short');