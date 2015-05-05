<?php 
	/*
	 * Class to check for request and display required function
	 */ 
	$wiziq_frontend_courses = new Wiziq_Frontend_Courses;
	$wiziq_frontend_classes = new Wiziq_Frontend_Classes;
	$wiziq_frontend_content = new Wiziq_Frontend_Content;
	$wiziq_Util = new Wiziq_Util;
	$courses_url = get_permalink();
	$qvarsign = $wiziq_Util->wiziq_frontend_url_structure();
	if(is_user_logged_in()) 
	{
		$courses_urls = $courses_url.$qvarsign.'action=courses';
		$add_curl = $courses_url.$qvarsign.'action=addcourse';
		$edit_curl = $courses_url.$qvarsign.'action=editcourse';
		$delete_curl = $courses_url.$qvarsign.'action=deletecourse';
		$wiziq_courses = new Wiziq_Courses;
		/*
		 * Check for courses request and display according to that
		 */ 
		if(!isset($_REQUEST['action']) && ! isset($_REQUEST['caction']) && ! isset($_REQUEST['ccaction'])) 
		{  
			$wiziq_frontend_courses->wiziq_frontend_view_courses_logged();
		}  
		elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'addcourse')
		{ 
			$wiziq_frontend_courses->wiziq_frontend_add_courses_form();
		} 
		elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'editcourse' && $_REQUEST['wn'] != '')
		{
			$wiziq_frontend_courses->wiziq_frontend_edit_courses_form();
		} 
		elseif ( isset ( $_REQUEST['action'] ) && isset ( $_REQUEST['course_id'] ) && "course_desc" == $_REQUEST['action'] ) {
			$wiziq_frontend_courses->wiziq_frontend_view_detail ( $_REQUEST['course_id'] );
		}
		/*
		 * Classes requests starting here
		 */ 
		else if ( isset ($_GET['caction']) && isset ($_GET['course_id']) && $_GET['caction'] == 'view_classes' ) {
			$wiziq_frontend_classes->wiziq_frontend_view_classes ( $_GET['course_id'] );
		}
		else if ( isset ($_GET['caction']) && isset ($_GET['class_id']) && isset ( $_GET['course_id'] ) && isset ( $_GET['wp_nonce'] ) && 'edit_class' == $_GET['caction']  ) {
			$wiziq_frontend_classes->wiziq_frontend_edit_class_form( $_GET['wp_nonce'] , $_GET['class_id'] , $_GET['course_id'] );
		}
		else if ( isset ($_GET['caction'])  && isset ($_GET['master_class_id']) && isset ($_GET['course_id']) && $_GET['caction'] == 'view_recurring_classes' ) {
			$wiziq_frontend_classes->wiziq_frontend_view_recurring_classes ( $_GET['course_id'], $_GET['master_class_id'] );
		}
		else if ( isset ($_GET['caction'])  && isset ($_GET['response_class_id']) && isset ($_GET['course_id']) && $_GET['caction'] == 'view_attendee' ) {
			$wiziq_frontend_classes->wiziq_frontend_view_attendee ( $_GET['response_class_id'] , $_GET['course_id'] );
		}
		else if ( isset ($_GET['caction']) && isset ($_GET['class_id']) && isset ( $_GET['course_id'] ) && 'view_front_class' == $_GET['caction'] ) {
			$wiziq_frontend_classes->wiziq_frontend_view_class_detail ( $_GET['class_id'], $_GET['course_id'] );
		}
		else if ( isset ($_GET['caction'] ) && isset ( $_GET['course_id'] ) && isset ( $_GET['wp_nonce'] ) && 'add_class' == isset ($_GET['caction'] ) ) {
			$wiziq_frontend_classes->wiziq_add_classes_form_front ( $_GET['wp_nonce'], $_GET['course_id']  );
		}
		
		/*
		 * Content requests
		 * 
		 */ 
		else if ( isset ($_GET['ccaction']) && isset ($_GET['course_id']) && "view_content" == $_GET['ccaction'] )  {
			if ( isset ( $_GET['parent'] ) ) {
				$parent  = $_GET['parent'];
			} else {
				$parent = 1;
			}
			$wiziq_frontend_content->wiziq_frontend_view_content ( $parent );
		}
		else if ( isset ($_GET['ccaction']) && isset ( $_GET['course_id'] ) && isset ( $_GET['parent'] ) && "add_content" == $_GET['ccaction'] )  {
			$wiziq_frontend_content->wiziq_frontend_add_content_form ( $_GET['parent'] , $_GET['course_id'] );
		}	
		else 
		{ ?>
			<script>
				window.location = "<?php echo get_permalink(); ?>";
			</script>
		<?php 
		}
	}
	else if ( isset ($_GET['caction']) && isset ($_GET['course_id']) && $_GET['caction'] == 'view_classes' ) {
			$wiziq_frontend_classes->wiziq_frontend_login_to_view ( );
	}elseif ( isset ( $_REQUEST['action'] ) && isset ( $_REQUEST['course_id'] ) && "course_desc" == $_REQUEST['action'] ) {
			$wiziq_frontend_courses->wiziq_frontend_view_detail ( $_REQUEST['course_id'] );
	}
	else
	{
		 $wiziq_frontend_courses->wiziq_frontend_view_courses_not_logged();
	}
