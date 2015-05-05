<?php
	/*
	 * Only display functions here.
	 * Functions which has to be done like add teacher before header loads are in admin hooks
	 * @since 1.1
	 */ 
	echo '<div class= "wrap" >';
		$wiziq_teachers = new Wiziq_Teachers;
		if ( isset ($_GET['add_teacher']) ) {
			$wiziq_teachers->wiziq_add_teacher_form();
		} elseif (isset ( $_GET['edit_teacher'] ) && isset ( $_GET['teacher_id'] ) && isset ( $_GET['wp_nonce'] ) )  {
			$wiziq_teachers->wiziq_edit_teacher_form( $_GET['teacher_id'], $_GET['wp_nonce'] ) ;
		}
		else {
			$wiziq_teachers->wiziq_view_teachers();
		}
echo '</div>';
