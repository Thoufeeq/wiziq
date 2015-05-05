<?php

/*
 * File to check for request and display the appropirate page according to the request for classes functionality
 * @since 1.0
 */ 

echo '<div class= "wrap" >';
	$wiziq_classes = new Wiziq_Classes;
	if ( isset ($_REQUEST['action']) && isset ($_REQUEST['course_id']) && isset ( $_REQUEST['wp_nonce'] )  && "add_class" == $_REQUEST['action'] ) 
	{
		$wiziq_classes->wiziq_add_classes_form ( $_REQUEST['wp_nonce']  ,$_REQUEST['course_id'] , WIZIQ_COURSES_MENU );
	}
	
	else if ( isset ($_REQUEST['action']) && "view_course" == $_REQUEST['action'] && isset ($_REQUEST['course_id'] ) ) 
	{
		/*
		 * Check for refresh and update status of classes
		 */ 
		if ( isset ($_POST['delete_classes']) ) 
		{
			$wiziq_classes->wiziq_delete_multiple_class($_POST , WIZIQ_CLASS_MENU);
			$wiziq_classes->wiziq_view_classes ($_REQUEST['course_id']) ;
		}
		else 
		{
			$wiziq_classes->wiziq_view_classes ($_REQUEST['course_id']) ;
		}
		
	} 
	
	else if ( isset ($_REQUEST['action']) && isset ($_REQUEST['course_id']) && isset ($_REQUEST['class_id']) && isset ( $_REQUEST['wp_nonce'] )  && "edit_class" == $_REQUEST['action'] )  
	{
			$returnrul = WIZIQ_CLASS_MENU."&action=view_course&course_id&course_id=".$_REQUEST['course_id'];
			$wiziq_classes->wiziq_edit_class_form ( $_REQUEST['wp_nonce'], $_REQUEST['class_id'] , $_REQUEST['course_id'] , $returnrul );
	}
	
	else if ( isset ($_REQUEST['action']) && "class_detail" == $_REQUEST['action'] && isset ($_REQUEST['class_id'] ) ) 
	{
		$res = $wiziq_classes->wiziq_get_class_by_id ($_REQUEST['class_id']);
		$response_class_id = $res->response_class_id;
		$response = $wiziq_classes->wiziq_live_get_data ( $response_class_id );
		if ( $response ) {
			$wiziq_classes->wiziq_view_class_detail($_REQUEST['class_id']);
		}
	}
	
	else if ( isset ( $_REQUEST['action'] ) && isset ( $_REQUEST['master_class_id'] ) && isset ( $_REQUEST['course_id'] ) && $_REQUEST['action'] == "view_recurring_class" ) 
	{
		if ( isset ($_POST['delete_classes']) ) 
		{
			$wiziq_classes->wiziq_delete_multiple_class($_POST , WIZIQ_CLASS_MENU);
		}
		$wiziq_classes->view_recurring_classes ( $_REQUEST['master_class_id'] , $_REQUEST['course_id'] );
	}
	
	else if ( isset ( $_REQUEST['action'] ) && isset ( $_REQUEST['response_class_id'] ) && isset ( $_REQUEST['course_id'] ) && $_REQUEST['action'] == "view_attendee" ) 
	{
		$wiziq_classes->wiziq_display_attendees ( $_REQUEST['response_class_id'] , $_REQUEST['course_id']  );
	}
echo '</div>';
echo '<div class= "clearfix" ></div>';
