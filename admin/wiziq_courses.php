<?php
	
	echo '<div class= "wrap" >';
	/*
	 * Check for request and diplay requested course function
	 * @since 1.0
	 */ 
	$wiziq_courses = new Wiziq_Courses;
	if ( isset ($_REQUEST['add_course'] )  && ( ! isset ( $_POST['wiziq_add_course'] ) ) ) {
		$wiziq_courses->wiziq_add_course_form();
	} elseif ( isset ($_REQUEST['edit_course'] )  && ( isset ( $_REQUEST['course_id'] )) && ( ! isset ( $_POST['wiziq_edit_course'] ) ) && isset ( $_REQUEST['wp_nonce'] ) ) {
		$wiziq_courses->wiziq_edit_course_form( $_REQUEST['wp_nonce'] , $_REQUEST['course_id'] , WIZIQ_COURSES_MENU );
	} elseif ( isset ($_REQUEST['edit_course'] )  && ( isset ( $_REQUEST['course_id'] )) && ( isset ( $_POST['wiziq_edit_course'] ) )  ) {
		$wiziq_courses->wiziq_edit_course( $_REQUEST['course_id'], $_POST, WIZIQ_COURSES_MENU );
	}elseif ( isset ($_POST['multiple_actions'] )  && ( isset ( $_POST['multiple_actions']  )) ==  "Delete" ) {
		$wiziq_courses->wiziq_delete_multiple_course ( $_POST );
		$wiziq_courses->wiziq_view_courses();
	} elseif ( isset ($_GET['course_detail'] ) && isset ( $_GET['course_id'] ) ){
		$wiziq_courses->wiziq_view_course_detail( $_GET['course_id'], WIZIQ_COURSES_MENU );
	} else {
		$wiziq_courses->wiziq_view_courses();
	}
	echo '</div>';
	echo '<div class= "clearfix" ></div>';
	
	
	
	  
	
?>
