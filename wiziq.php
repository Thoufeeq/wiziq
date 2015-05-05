<?php
/*
Plugin Name: WizIQ
Version: 1.1
Description: Making online teaching and learning easier for everyone
Author: athuorGEN Technologies Pvt. Ltd
Author URI: support@wiziq.com
*/

//activation and deactivation hooks
register_activation_hook(__FILE__,'wiziq_install'); 
register_uninstall_hook( __FILE__, 'wiziq_uninstall' );

/*
 * WiziQ installation options
 * @since 1.0
 * 
 */ 
function wiziq_install() {
	require_once('wiziq_install.php');
	wiziq_install_options();
	wiziq_table_create();
}


/*
 * WiziQ Unistallation options
 * @since 1.0
 * 
 */ 
function wiziq_uninstall() {
	require_once('wiziq_uninstall.php');
	wiziq_uninstall_options();
	wiziq_delete_tables();
}


/*
 * Settings link on plugin page 
 */ 

function wiziq_settings_link( $links ) {
   $links[] = '<a href="'. get_admin_url(null, 'admin.php?page=wiziq_settings') .'">Settings</a>';
   return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wiziq_settings_link' );


/*
 * Add css and javascripts in the admin dashboard
 */ 
function load_custom_wp_admin_style() {
	wp_register_style('jquery-ui-custom-css', plugin_dir_url( __FILE__ ) . '/stylesheet/jquery-ui-1.10.3.css');
	wp_enqueue_style( 'jquery-ui-custom-css' ); 
	wp_register_style( 'wiziq_css', plugin_dir_url( __FILE__ ) . '/stylesheet/custom_wiziq.css' );
	wp_enqueue_style( 'wiziq_css' ); 
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-ui-tooltip');
	wp_enqueue_script( 'wiziq_js', plugin_dir_url( __FILE__ ) . 'js/wiziq_javascript.js');  
}

add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
require_once('admin/admin_hooks.php');

/*
 * Global Variables
 */ 
require_once('wiziq_config.php');

/*
 * WiziQ Admin Panel file
 */ 
require_once('admin/wiziq_admin.php');

/*
* WizIQ Front Panel file
*/
require_once('frontend/wiziq_site.php');

/*
* WizIQ Front Panel function file
*/
require_once('functions/frontend-functions.php');

/*
 * Include Functions wiziq courses files
 */ 
require_once( WIZIQ_PLUGIN_PATH. "/functions/wiziq_courses.php" );

/*
 * Include API file
 */ 
require_once('wiziqapi/authentication.php');


/*
 * Include API functions file
 */ 
require_once('wiziqapi/wiziq_api_functions.php');
require_once('wiziqapi/wiziq_frontend_api_functions.php');


/*
 * Include util functions
 */ 
require_once('functions/wiziq_util.php');

/*
 * Include action hooks file
 */ 
require_once('functions/wiziq_action_hooks.php');
 

/*
* WizIQ Classes functions file
*/
require_once('functions/wiziq_class.php');

/*
 * Include permissoins file
 */ 
require_once('functions/wiziq_user_permissions.php');

/*
 * WizIQ content functions file
 */
require_once('functions/wiziq_content.php');  

/*
 * WizIQ teachers functions file
 */
require_once('functions/wiziq_teachers.php');  

	
add_action('init', 'wiziq_translation');
function wiziq_translation() {
	load_plugin_textdomain('wiziq', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
}
