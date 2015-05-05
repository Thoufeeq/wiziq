<?php
/*
 * WiziQ installation file
 */ 

/*
 * Create API options
 */ 
function wiziq_install_options() {
	add_option( 'api_url', 'http://class.api.wiziq.com/');
	add_option( 'recurring_api_url', 'http://class.api.wiziq.com/apimanager.ashx');
	add_option( 'content_url', 'http://content.api.wiziq.com/RestService.ashx');
	add_option( 'access_key', 'kndxlmt3RJw=');
	add_option( 'secret_key', 'XxLzvbPUE2D/DFY5osT/6g==');
	add_option( 'content_language', 'http://class.api.wiziq.com/vc-language.xml');
	add_option( 'timezone_api_url', 'http://class.api.wiziq.com/tz.xml');
}

/*
 * Create necessary tables while creating the plugin
 */ 
function wiziq_table_create() {
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	global $wpdb;
	$wiziq_courses = $wpdb->prefix."wiziq_courses";
	$wiziq_wclasses = $wpdb->prefix."wiziq_wclasses";
	$wiziq_enroluser = $wpdb->prefix."wiziq_enroluser";
	$wiziq_contents = $wpdb->prefix."wiziq_contents";
	$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
	$courses_creations = "CREATE TABLE $wiziq_courses (
		id int AUTO_INCREMENT PRIMARY KEY,
		created_by int NOT NULL,
		fullname varchar(255) NOT NULL,
		startdate datetime NULL DEFAULT NULL ,
		enddate datetime NULL DEFAULT NULL,
		description text NOT NULL
	);";
	$classes_creations = "CREATE TABLE $wiziq_wclasses (
		id int AUTO_INCREMENT PRIMARY KEY,
		created_by int NOT NULL,
		class_name varchar(100) NOT NULL,
		class_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		duration varchar(100) NOT NULL,
		courseid int NOT NULL,
		classtimezone varchar(100) NOT NULL,
		language varchar(100) NOT NULL,
		recordclass varchar(10) NOT NULL,
		attendee_limit int NOT NULL,
		response_class_id int NOT NULL,
		response_recording_url varchar(200) NOT NULL,
		response_presenter_url varchar(200) NOT NULL,
		status varchar(200) NOT NULL,
		master_id int(20) NOT NULL,
		attendence_report varchar(100) NOT NULL,
		get_detail tinyint NOT NULL,
		download_recording varchar(100) NOT NULL,
		is_recurring varchar(10) NOT NULL
	);";
	$enroluser_creations = "CREATE TABLE $wiziq_enroluser (
		user_id int NOT NULL,
		course_id int NOT NULL,
		create_class tinyint NOT NULL,
		edit_class tinyint NOT NULL,
		delete_class tinyint NOT NULL,
		view_recording tinyint NOT NULL,
		download_recording tinyint NOT NULL,
		upload_content tinyint NOT NULL
	);";
	$content_creations = "CREATE TABLE $wiziq_contents (
		id int AUTO_INCREMENT PRIMARY KEY,
		status varchar(10) NOT NULL,
		created_by int NOT NULL,
		isfolder tinyint NOT NULL,
		name varchar(255) NOT NULL,
		parent int NOT NULL,
		content_id int NOT NULL,
		uploadingfile varchar(255) NOT NULL,
		folderpath varchar(100) NOT NULL
	);";
	$teacher_creation = "CREATE TABLE $wiziq_teacher (
		id int AUTO_INCREMENT PRIMARY KEY,
		puser_id int NOT NULL,
		status varchar(100) NOT NULL,
		password varchar(100) NOT NULL,
		image varchar(255) NOT NULL,
		phone_number varchar(20) NOT NULL,
		mobile_number varchar(20) NOT NULL,
		timezone varchar(255) NOT NULL,
		about_the_teacher text NOT NULL,
		can_schedule_class tinyint NOT NULL,
		is_active tinyint NOT NULL,
		teacher_id bigint NOT NULL,
		teacher_email varchar(200) NOT NULL,
		deactivated tinyint NOT NULL
	);";
	if($wpdb->get_var("SHOW TABLES LIKE '$wiziq_courses'") != $wiziq_courses) {
		$wpdb->query($courses_creations);
	}
	if($wpdb->get_var("SHOW TABLES LIKE '$wiziq_wclasses'") != $wiziq_wclasses) {
		$wpdb->query($classes_creations);
	}
	if($wpdb->get_var("SHOW TABLES LIKE '$wiziq_enroluser'") != $wiziq_enroluser) {
		$wpdb->query($enroluser_creations);
	}
	if($wpdb->get_var("SHOW TABLES LIKE '$wiziq_contents'") != $wiziq_contents) {
		$wpdb->query($content_creations);
		$content_qry = "insert into $wiziq_contents (created_by, isfolder, name, parent, content_id)
		values ( '0','1','My Content' ,'0', '0') ";
		$wpdb->query($content_qry) ;
	}
	if($wpdb->get_var("SHOW TABLES LIKE '$wiziq_teacher'") != $wiziq_teacher) {
		$wpdb->query($teacher_creation);
	}
	
}
