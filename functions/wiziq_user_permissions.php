<?php

/*
 * User Permissions file
 * @since 1.0
 */ 
 
class Wiziq_User_Permissions {
	
	
	/*
	* Function to check class permission of the user from enroll user table
	* @since 1.0
	*/ 
	function wiziq_get_class_permissions ( $course_id ) {
		global $wpdb;
		$current_user_id = get_current_user_id();
		$enroll_table = $wpdb->prefix."wiziq_enroluser";
		$permission_qry = "select * from $enroll_table where user_id = '$current_user_id' and course_id = '$course_id' ";
		$permissions_res = $wpdb->get_row($permission_qry);
		
		if ( $permissions_res ) {
			return $permissions_res;
		} else {
			return false;
		}
	}//Function end 
	
	/*
	 * Function to check if user can see the classes of a course
	 * @since 1.0
	 */
	function wiziq_user_view_course_permission ( $course_id )  {
		if (current_user_can('manage_options')) {
			return true;
		}
		else {
			$class_result = $this->wiziq_get_class_permissions ( $course_id );
			if ( $class_result && $class_result->user_id ) {
				return true;
			} else {
			return false;
			}
		}
	}// function end to view classes of a course
	  
	/*
	 * Function to check class creation permission 
	 * @since 1.0
	 */ 
	 
	function wiziq_front_class_permission ( $course_id ) {
		if (current_user_can('manage_options')) {
			return true;
		}
		else {
			$class_result = $this->wiziq_get_class_permissions ( $course_id );
			if ( $class_result && $class_result->create_class ) {
				return true;
			} else {
			return false;
			}
		}
	} //Function end to check class create permission
	
	/*
	 * Function to check edit class permission for a single class. Only admin or the who created can edit the class
	 * @since 1.0
	 */ 
	function wiziq_edit_class_permission ( $course_id , $class_id ) {
		if (current_user_can('manage_options')) {
			return true;
		}
		else {
			$wiziq_class = new Wiziq_Classes;
			$current_user_id = get_current_user_id();
			$class_result = $wiziq_class->wiziq_get_class_by_course_class ( $course_id, $class_id );
			if ( $class_result && $current_user_id == $class_result->created_by ) {
				return true;
			} else {
				return false;
			}
		}
	}//Function end to edit class permission
	
	/*
	 * Function to check delete class permission. Only admin or the who created can delete the class
	 * @since 1.0
	 */ 
	function wiziq_delete_class_permission ( $course_id , $class_id) {
		if (current_user_can('manage_options')) {
			return true;
		}
		else {
			$wiziq_class = new Wiziq_Classes;
			$current_user_id = get_current_user_id();
			$class_permissions = $wiziq_class->wiziq_get_class_by_course_class ( $course_id, $class_id );
			if ( $class_permissions && $current_user_id == $class_permissions->created_by ) {
				return true;
			} else {
				return false;
			}
		}
	}//Function end for class deleing permission
	
	/*
	 * Function to view recording of a class permission
	 * Check if the class was created by the current logged in user or have permission to view
	 * @since 1.0
	 */ 
	function wiziq_view_recording_class_permission ( $course_id ,$class_id ) {
		if (current_user_can('manage_options')) {
			return true;
		}
		else {
			$class_permissions = $this->wiziq_get_class_permissions ( $course_id , $class_id );
			$wiziq_class = new Wiziq_Classes;
			$current_user_id = get_current_user_id();
			$class_created_by = $wiziq_class->wiziq_get_class_by_course_class ( $course_id, $class_id );
			if ( $class_permissions &&  ( $current_user_id == $class_created_by->created_by || $class_permissions->view_recording ) ) {
				return true;
			} else {
				return false;
			}
		}
	}//Function end to view class recording permission
	 
	/*
	 * Function to download recording of a class, from enroll user table and classes table
	 * Check if the class was created by the current logged in user or have permission to download
	 * @since 1.0
	 */  
	function wiziq_downaload_recording_class_permission ( $course_id, $class_id ) {
		if (current_user_can('manage_options')) {
			return true;
		}
		else {
			$wiziq_class = new Wiziq_Classes;
			$current_user_id = get_current_user_id();
			$class_permissions = $this->wiziq_get_class_permissions ( $course_id );
			$class_created_by = $wiziq_class->wiziq_get_class_by_course_class ( $course_id, $class_id );
			
			if ( $class_permissions &&  ( $current_user_id == $class_created_by->created_by || $class_permissions->download_recording ) ) {
				return true;
			} else {
				return false;
			}
		}
	}//Function end for download recorded class 
	
	/*
	 * Function to view attendee list to only admin or the user who created the class
	 * @since 1.0
	 */  
	function wiziq_view_attendee_class_permission ( $course_id , $classid ) {
		if (current_user_can('manage_options')) {
			return true;
		}
		else {
			$wiziq_class = new Wiziq_Classes;
			$current_user_id = get_current_user_id();
			$class_permissions = $wiziq_class->wiziq_get_class_by_course_class ( $course_id, $classid );
			if ( $class_permissions && $current_user_id == $class_permissions->created_by ) {
				return true;
			} else {
				return false;
			}
		}
	}//Function end  to view attendee permission
	
	/*
	 * Function for content uploading permission
	 * @since 1.0
	 */ 
	function wiziq_upload_content_permission ( $course_id ) {
		if(is_user_logged_in()){
			global $current_user;
			$current_user_id = get_current_user_id();
			get_currentuserinfo();
			if(in_array("administrator", $current_user->roles)){
				return true;
			} else {
				$class_permissions = $this->wiziq_get_class_permissions ( $course_id );
				if ( $class_permissions && "1" == $class_permissions->upload_content ) {
					return true;
				} else {
					return false;
				}
			}
		
		} else {
			return false;
		}
	}// end function for content uploading permission
	
	
	
	/*
	 * Function to check if a user can schedule class or not
	 * pass user id, id generated by wordpress on registration
	 * @since 1.0
	 */ 
	function wiziq_get_teacher_for_enroll ( $user_id ) {
		global $wpdb;
		$wiziq_teacher = $wpdb->prefix."wiziq_teacher";
		$qry = "select * from $wiziq_teacher where puser_id = '$user_id'" ;
		$wiziq_results = $wpdb->get_row( $qry );
		if ( !empty($wiziq_results) ) :
			if ( $wiziq_results->is_active ) :
				if ( $wiziq_results->can_schedule_class )
					return 1;
				else
					return 0;
			else :
				return 0;
			endif;
		else :
			return 0;
		endif;
	} 
}



