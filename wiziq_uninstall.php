<?php
/*
 * WiziQ uninstallation options
 */ 


/*
 * Function to delete the wiziq options on de-activation
 */  
function wiziq_uninstall_options() {
	delete_option('api_url');
	delete_option('recurring_api_url');
	delete_option('content_url');
	delete_option('access_key');
	delete_option('secret_key');
	delete_option('content_language');
	delete_option('timezone_api_url');
}

/*
 * Function to delete tables on plugin de-activation
 */ 
function wiziq_delete_tables() {
	global $wpdb;
	$wiziq_courses = $wpdb->prefix."wiziq_courses";
	$wiziq_wclasses = $wpdb->prefix."wiziq_wclasses";
	$wiziq_enroluser = $wpdb->prefix."wiziq_enroluser";
	$wiziq_contents = $wpdb->prefix."wiziq_contents";
	$wpdb->query("Drop TABLE $wiziq_courses");
	$wpdb->query("Drop TABLE $wiziq_wclasses");
	$wpdb->query("Drop TABLE $wiziq_enroluser");
	$wpdb->query("Drop TABLE $wiziq_contents");
}
