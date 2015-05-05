<?php
	/*
	 * Include Functions wiziq courses files
	 * @since 1.0
	 */ 	
	require_once(WIZIQ_PLUGIN_PATH. "/functions/wiziq_course_enroll.php" );
	
	
	echo '<div class= "wrap" >';
	
	$wiziq_enroll_user = new Wiziq_Enroll_User;
        if ( ($_REQUEST['page']=='wiziq_enroll' )  && isset ($_REQUEST['course_id'] ) && isset ( $_POST['wiziq_enroll_users'] ) ) {
            $wiziq_enroll_user->wiziq_save_permission($_POST, WIZIQ_COURSES_MENU);
        }  else {
            $wiziq_enroll_user->wiziq_view_enroll_user();   
        }

	echo '</div>'
	
?>
