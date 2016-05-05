<?php
function awl_custom_options(){
    add_menu_page('AWL Settings', 'AWL Settings', 'manage_options', 'arc-custom-options', 'awl_options_page');
    add_action('admin_init', 'awl_register_settings');
}
add_action('admin_menu', 'awl_custom_options');

function awl_register_settings(){
    register_setting('awl_options', 'awl_options');
    add_settings_section('awl_meta', 'Meta', '', 'awl_options_section');
    add_settings_field('awl_meta_json', 'Meta Fields', 'add_textarea_field', 'awl_options_section', 'awl_meta', array('id' => 'awl_meta_json'));

}

function add_text_field($args){
    $id = $args['id'];
    $options = get_option('awl_options');
    echo "<input id='{$id}' name='awl_options[{$id}]' type='text' value='{$options[$id]}' />";
}

function add_textarea_field($args){
    $id = $args['id'];
    $options = get_option('awl_options');
    echo "<textarea id='{$id}' name='awl_options[{$id}]'>" . $options[$id] . "</textarea>";
}

function awl_options_page(){
?>
<div class="wrap">
<h2>AWL</h2>
<form method="post" action="options.php">
    <?php settings_fields('awl_options')?>
    <?php do_settings_sections('awl_options_section')?>
    <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
</form>
</div>
<?php }