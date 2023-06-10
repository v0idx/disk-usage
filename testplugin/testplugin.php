<?php
/*
Plugin Name: Test Plugin
*/

//Includes for dependencies
use Carbon_Fields\Container;
use Carbon_Fields\Field;

require "Controllers\DiskController.php";
use Controllers\DiskController;

//Registering the carbon fields settings menu
add_action('carbon_fields_register_fields', 'crb_attach_theme_options');
function crb_attach_theme_options() {
    Container::make('theme_options',__('Settings'))
    ->set_page_parent(__FILE__)
    ->add_fields(array(
        Field::make('text','worker_time', 'Worker Time (Seconds)')
            ->set_default_value(5)
            ->set_attribute('min', 1),
            ) );
}

//Load the Carbon Fields
add_action('after_setup_theme','crb_load');
function crb_load() {
    require_once('vendor/autoload.php');
    \Carbon_Fields\Carbon_Fields::boot();
}

//Get the total space used by the site in mb.
function get_space_used() {
    $space = apply_filters('pre_get_space_used',false);

    if (false === $space) {
        $upload_dir = wp_upload_dir();
        $space = get_dirsize($upload_dir['basedir']) / MB_IN_BYTES;
    }

    return $space;
}

//Add the custom admin menu, with submenu page.
function custom_menu() {
    add_menu_page('Test Plugin','Test Plugin',10,__FILE__);
    add_submenu_page(__FILE__,'Main','Main',10,__FILE__,'main_contents');
}

//register my custom stylesheet. (only if the page is currently the admin page.)
function add_listing_styles($page) {
    $currPage = get_current_screen()->base;
    if('toplevel_page_testplugin/testplugin' != $currPage) {
        return;
    }
    wp_enqueue_style('style-file',plugins_url('//style_listing.css', __FILE__));
}

//A basic main page for the plugin.
function main_contents() {
    ?>
        <h1>Results</h1>
        <div id="result-area"></div>
        <p>Press the start button to generate a disk useage graph.</p>
        <h1>Controls</h1>
        <input id="start"type="button" value="Start"></input>
    <?php
}

//Register my main js file, allowing for the 'Start' button to function
function load_js() {
    wp_enqueue_script('test-script', plugins_url('//scripts//main.js', __FILE__));
}

//Callback function for 'GET' of the disk tree
function get_disk_tree($request) {
    if($request->get_param('start') === null) {
        $controller = new DiskController(null, 'GET', null, null);
        $resp = $controller->processRequest();

        return $resp;
    } else {
        //pass the start parameter through to the disk usage route
        $resp = get_size($request->get_param('start'));
        return $resp;
    }
    
}

function get_size($start) {
    //create a disk controller request, using the options page to get the time
    $controller = new DiskController(null, 'GET', $start,carbon_get_theme_option('worker_time'));
    $resp = $controller->processRequest();

    
    return $resp;
}


//Register my custom functionality with wordpress actions
add_action('admin_menu','custom_menu');
add_action('admin_enqueue_scripts', 'add_listing_styles');
add_action('admin_enqueue_scripts', 'load_js');

//ensure that dashicons is loaded.
function load_dashicons_front_end() {
    wp_enqueue_style( 'dashicons' );
}
add_action( 'admin_enqueue_scripts', 'load_dashicons_front_end' );

//register first api route
add_action('rest_api_init', function() {
    register_rest_route('testplugin/api', '/disk', array(
        'methods' => 'GET',
        'callback' => 'get_disk_tree',
    ));
});

// add_action('rest_api_init', function() {
//     register_rest_route('testplugin/api', '/disk/(?P<file>)', array(
//         'methods' => 'GET',
//         'callback' => 'get_size',
//     ));
// });

?>
