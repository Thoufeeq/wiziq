<?php
	/*
	 * function to handle request before page is loaded
	 */
	add_action('admin_init', 'wiziq_admin_request_hook');
	
	function wiziq_admin_request_hook () {
		$wiziq_courses = new Wiziq_Courses;
		$wiziq_classes = new Wiziq_Classes;
		$wiziq_teachers = new Wiziq_Teachers;
		
		/*
		 * Courses functions 
		 * @since 1.0
		 */ 
		if ( isset ($_REQUEST['add_course'] )  && (isset ( $_POST['wiziq_add_course'] )) ) {
			$wiziq_courses->wiziq_add_course( $_POST , WIZIQ_COURSES_MENU."&success" );
		}elseif ( isset ($_REQUEST['delete_course'] ) && ! isset ($_POST['multiple_actions']) && ( isset ( $_REQUEST['course_id'] )) && isset ( $_REQUEST['wp_nonce'] ) ) {
			$wiziq_courses->wiziq_delete_course( $_REQUEST['wp_nonce'] , $_REQUEST['course_id'],WIZIQ_COURSES_MENU  );
		}
		
		/*
		 * Classes functions
		 * @since 1.0
		 */ 
		elseif ( isset ($_REQUEST['action']) && isset ($_REQUEST['course_id']) && isset ( $_REQUEST['wp_nonce'] )  && "add_class" == $_REQUEST['action'] && isset( $_POST['add_class_wiziq']) ) {
			$wiziq_classes->wiziq_add_classes ( $_POST) ;
		}else if ( isset ($_REQUEST['action']) && "delete_class" == $_REQUEST['action'] && isset ($_REQUEST['class_id'] ) ) {
			$wiziq_classes->wiziq_delete_single_class($_REQUEST['class_id'] , WIZIQ_CLASS_MENU, $_REQUEST['course_id']);
		}
		
		/*
		 * Teacher functions
		 * @since 1.1
		 */ 
		elseif ( isset ($_GET['add_teacher']) && isset ($_POST['wiziq_add_teacher']) ) {
			$wiziq_teachers->wiziq_add_teacher( $_POST );
		}
		elseif ( isset ( $_GET['edit_teacher'] )  && isset ( $_GET['wp_nonce'] ) && isset ( $_GET['teacher_id'] ) && isset ($_POST['wiziq_edit_teacher']) ) {
			$wiziq_teachers->wiziq_edit_teacher( $_POST, $_GET['teacher_id'], $_GET['wp_nonce'] );
		} 
		
		//teachers deactivation check
		$wiziq_teachers->wiziq_teachers_deactivated();
	}
