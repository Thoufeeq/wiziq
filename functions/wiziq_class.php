<?php
/*
 * Class for wiziq classes 
 * @since 1.0
 */ 

class Wiziq_Classes
{
	
	/*
	 * Function to refresh all the classes
	 * pass course id to refresh courses of a particular class
	 * @since 1.0
	 */ 
	function wiziq_refresh_classes ( $course_id , $sortby, $orderby ) {
		$wiziq_api_functions = new wiziq_api_functions;
		$wiziq_Util = new Wiziq_Util;
		$wiziq_class_res = $this->wiziq_get_class ( $course_id , '0' , '0', '0' );
		$total_pages = !empty($wiziq_class_res)?count($wiziq_class_res):0 ;
		$limit = WIZIQ_PAGINATION_LIMIT;
		$adjacents = 3;
		$page = isset($_GET['pageno'])?$_GET['pageno']:'';
		if($page) 
			$start = ($page - 1) * $limit; 			//first item to display on this page
		else
			$start = 0;								//if no page var is given, set start to 0
		$targetpage = "?page=wiziq_class&action=view_course&course_id=$course_id";
		$pagination =  $wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
		$countrow = 0;
		$wiziq_class_result  = $this->wiziq_get_class_sorted ( $course_id , '1' , $start , $limit, $sortby, $orderby  );

		if ( $wiziq_class_result )  {
			foreach ( $wiziq_class_result as $re ) {
				if ( $re->status == "upcoming" ||  $re->status == "completed"  ) {
					$class_response_id = $re->response_class_id;
					$this->wiziq_live_get_data ( $class_response_id );
				}
			}
		}
		return true;
	} // end refresh classes function 
	 
	/*
	 * Function to view all the classes
	 * pass course id to view the classes
	 * @since 1.0
	 */ 
	function wiziq_view_classes ( $course_id ) {
		global $wpdb;
		$wiziq_course = new Wiziq_Courses;
		$wiziq_Util = new Wiziq_Util;
		$wiziq_api_functions = new wiziq_api_functions;
		
		
		$course_result = $wiziq_course->wiziq_get_single_courses ($course_id);
		$course_name = $course_result->fullname;
		$add_class_nonce = wp_create_nonce( 'add-class-' . $course_id );
		
		/*
		 * Method for updating the database with recurring classes
		 */ 
		$recurring_classes = $this->wiziq_get_recurring_classes ( $course_id );
		if ($recurring_classes) {
			foreach ( $recurring_classes as $recurr ) {
				$courseid = $recurr->courseid;
				$classid = $recurr->response_class_id;
				$master_id = $recurr->master_id;
				/*
				 * call to api and get list and update schedule
				 */ 
				$recurringlist = $wiziq_api_functions->wiziq_view_schedule ( $master_id , $classid) ;
			}
		}
		
		
		/*
		 * Sorting functionality
		 * Create refresh url 
		 */
		if ( isset ( $_GET['sort-by'] ) && isset ($_GET['order-by']) ) {
			$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
			if ( $wiziq_Util->wiziq_table_exist_check( $wiziq_classes ,$_GET['sort-by'] ) && ('desc' == $_GET['order-by'] || 'asc' == $_GET['order-by']) ) {
				$sortby = $_GET['sort-by'];
				$orderby = $_GET['order-by'];
				$refresh_url = WIZIQ_CLASS_MENU.'&action=view_course&course_id='.$course_id.'&sort-by='.$sortby.'&order-by='.$orderby.'&refresh';
			}else {
				$sortby = "id";
				$orderby = "desc";
				$refresh_url = WIZIQ_CLASS_MENU.'&action=view_course&course_id='.$course_id.'&refresh';
			}
		} else {
			$sortby = "id";
			$orderby = "desc";
			$refresh_url = WIZIQ_CLASS_MENU.'&action=view_course&course_id='.$course_id.'&refresh';
		}
		
		
		/*
		 * Function to refresh the classes
		 */ 
		$this->wiziq_refresh_classes ( $course_id, $sortby, $orderby ) ;
		  
		// pagination functionality
		
		$wiziq_class_res = $this->wiziq_get_class ( $course_id , '0' , '0', '0');
		$total_pages = !empty($wiziq_class_res)?count($wiziq_class_res):0 ;
		$limit = WIZIQ_PAGINATION_LIMIT;
		$adjacents = 3;
		$page = isset($_GET['pageno'])?$_GET['pageno']:'';
		if($page) 
			$start = ($page - 1) * $limit; 
		else
			$start = 0;
		$targetpage = "?page=wiziq_class&action=view_course&course_id=$course_id&";
		if ( isset ($_GET['sort-by']) && isset ($_GET['order-by']) ) {
			$targetpage .= 'sort-by='.$_GET['sort-by'].'&order-by='.$_GET['order-by'].'&';
		}
		$pagination =  $wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
		$countrow = 0;
		
		$wiziq_class_result  = $this->wiziq_get_class_sorted ( $course_id , '1' , $start , $limit , $sortby , $orderby  );
		if ( $page == "" ) {
			$page = 1;
		}
		$pagetogo = $page;
		$refresh_url .= '&pageno='.$pagetogo;
		
		if ( isset ( $_GET['sort-by'] ) && isset ($_GET [ 'order-by']  )) :
			
			if ( "asc" == $_GET['order-by']) {
				$nameclass = "sorting-up";
				$ordering = "desc";
				$nametitle = __( 'Click to sort by descending order' ,'wiziq');
			} else {
				$nameclass = "sorting-down";
				$ordering = "asc";
				$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
			}
			$sortby = $_GET['sort-by'];
		else :
			$nameclass = "sorting-up";
			$ordering = "asc";
			$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
			$sortby = "id";	
		endif;
		
		?>
		<h2><?php _e('WizIQ Classes', 'wiziq'); ?><a class = "add-new-h2"  href= "<?php echo WIZIQ_CLASS_MENU; ?>&action=add_class&course_id=<?php echo $course_id; ?>&wp_nonce=<?php echo $add_class_nonce; ?>" ><?php _e('Add New Class', 'wiziq'); ?></a><a class = "add-new-h2" href = "<?php echo WIZIQ_COURSES_MENU;?>" ><?php _e( 'Back To Courses' , 'wiziq' ); ?></a></h2>
		<h3><?php _e('Course', 'wiziq'); ?><?php echo " : ".$course_name ; ?></h3>
		<?php 
			//display if any errors
			global $myerror;
			if ( is_wp_error( $myerror ) ) {
				$add_error = $myerror->get_error_message('wiziq_class_delete_error');
				if ( $add_error ) {
					echo $add_error;
				}
				$get_error = $myerror->get_error_message('wiziq_class_get_data_error');
				if ( $get_error ) {
					echo $get_error;
				}
			}
			if (isset ($_GET['sdelete']) && !isset ($_POST['delete_classes'])) {
				echo '<div class="updated"><p><strong>'.__('Class deleted successfully','wiziq').'</strong></p></div>';
			}
		?>
		<h4>
			<?php 
				if(isset ($_GET['addsucess'])) {
					echo '<div class = "updated" ><p><strong>'.__('Class created successfully','wiziq').'</strong></p></div>';
				} else if( isset ($_GET['raddsucess']) ) {
					echo '<div class = "updated" ><p><strong>'.__('Classes created successfully','wiziq').'</strong></p></div>';
				} else if( isset ($_GET['editsuccess']) ) {
					echo '<div class = "updated" ><p><strong>'.__('Class updated successfully','wiziq').'</strong></p></div>';
				}
			?>
		</h4>
		<form method = "post" >
			<div class = "tablenav top">
				<div class="alignleft actions bulkactions">
					<select name="multiple_actions" id= "delete_action_class">
						<option value = "-1" ><span><?php _e('Bulk Actions', 'wiziq'); ?></span></option>
						<option value = "1"><span><?php _e('Delete', 'wiziq'); ?></span></option>
					</select>
					<input id="delete_mul_class" class="button action delete-classes" type="submit" value="<?php _e('Apply', 'wiziq'); ?>" name = "delete_classes">
				</div>
			</div>
			<table class= "wp-list-table widefat fixed pages" >
				<thead>
					<tr>
						<th class = "manage-column column-cb check-column" >
							<label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All' , 'wiziq' ); ?></label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th id = "course_name" class = "manage-column sortable desc" >
							<a href = "<?php echo WIZIQ_CLASS_MENU.'&action=view_course&course_id='.$course_id.'&sort-by=class_name&order-by='.$ordering; ?>" title = "<?php echo $nametitle; ?>" >
								<span><?php _e('Class Title', 'wiziq'); ?></span>
							</a>
							<?php if (isset ( $_GET[ 'sort-by' ] ) && "class_name" == $_GET[ 'sort-by' ] ) : ?>
								<div class = "<?php echo $nameclass; ?>" ></div>
							<?php endif; ?>
						</th>
						<th id = "course_description" class = "manage-column" >
							<?php _e('Class Time', 'wiziq'); ?>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Presenter', 'wiziq'); ?>
						</th>
						
						<th class = "manage-column" >
							<?php _e('Status', 'wiziq'); ?>
							<?php if ( !empty($wiziq_class_res) ) : ?>
							<a href="<?php echo $refresh_url; ?>">
							<?php else : ?>
							<a href="javascript:;">
							<?php endif; ?>
							<img title= "<?php _e('Refresh', 'wiziq'); ?>" class = "classes-images" src= "<?php echo plugins_url( 'images/refresh20.png' , dirname(__FILE__) ) ; ?>" alt ="Refresh" />
							</a>
						</th>
						<th class = "manage-column" >
							<?php _e('Manage Class', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Attendance Report', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('View Recording', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Download Recording', 'wiziq'); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class = "manage-column column-cb check-column" >
							<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th id = "course_name" class = "manage-column sortable desc" >
								<span><?php _e('Class Title', 'wiziq'); ?></span>
						</th>
						<th id = "course_description" class = "manage-column" >
							<?php _e('Class Time', 'wiziq'); ?>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Presenter', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('Status', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Manage Class', 'wiziq'); ?>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Attendance Report', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('View Recording', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('Download Recording', 'wiziq'); ?>
						</th>
					</tr>
				</tfoot>
				<tbody>
					<?php 
					$user_permissions  = new Wiziq_User_Permissions;
					if ( $wiziq_class_result )  {
						foreach ( $wiziq_class_result as $res ) {
							$editnonce = wp_create_nonce( 'edit-class-' . $res->id );
							$recoding_status = $user_permissions->wiziq_downaload_recording_class_permission ( $course_id , $res->id );
							
							/*
							 * get status of classes and update the content
							 */ 
							
							
							$countrow++;
							if( "1" == $countrow) 
								{ 
									$row_class = "alternate iedit cclass";
								} 
								else 
								{
									$countrow = "0";
									$row_class ="iedit cclass" ;
								}
								?>
								<tr id = "cclass-<?php echo $res->id; ?>" class = "<?php echo $row_class; ?>" >
									<th class="check-column" scope="row">
										<label class="screen-reader-text" >Select <?php echo $res->class_name; ?></label>
										<input id="cb-select-<?php echo $res->id; ?>" type="checkbox" value="<?php echo $res->id; ?>" name = "class-checkbox[]" value= "<?php echo $res->id; ?>">
										<div class="locked-indicator"></div>
									</th>
									<td class = "post-title page-title column-title" >
										<strong>
											<a href="<?php echo WIZIQ_CLASS_MENU ?>&action=class_detail&class_id=<?php echo $res->id; ?>&course_id=<?php echo $course_id; ?>">
												<?php echo $res->class_name; ?>
											</a>
										</strong>
										<div class="row-actions">
										<span class="edit">
											<?php if ( "upcoming" == $res->live_status) : ?>
												<a title="<?php _e('Edit this class', 'wiziq'); ?>" href="<?php echo WIZIQ_CLASS_MENU ?>&action=edit_class&class_id=<?php echo $res->id; ?>&course_id=<?php echo $course_id; ?>&wp_nonce=<?php echo $editnonce; ?>"><?php _e( 'Edit' , 'wiziq' ); ?></a>
											<?php else : ?>
												<a title="<?php _e('Edit this class', 'wiziq'); ?>" href="javascript:;"><?php _e( 'Edit' , 'wiziq' ); ?></a>
											<?php endif; ?>
											|
										</span>
										<span class="trash">
											<a id = "<?php echo $res->id;?>" class="submitdelete-class" href="<?php echo WIZIQ_CLASS_MENU ?>&action=delete_class&class_id=<?php echo $res->id; ?>&course_id=<?php echo $course_id; ?>" title="<?php _e('Delete this class', 'wiziq'); ?>"><?php _e( 'Delete' , 'wiziq' ); ?></a>
										</span>
									</div>
									</td>
									<td> 
										<?php 
										echo date( WIZIQ_DATE_TIME_FORMAT, strtotime($res->class_time));
										?>
									</td>
									<td> 
										<?php 
										$presenter_id = $res->created_by;
										$user_info = get_userdata( $presenter_id );
										echo $user_info->display_name;
										?>
									</td>
									<td> 
										<?php 
											$wiziq_util = new Wiziq_Util;
											if($res->live_status == 'upcoming'){
												$datetime_result = $wiziq_util->wiziq_get_datetime ( $res->class_time, $res->duration, $res->classtimezone );
												if( $datetime_result ){
													_e( 'Live Class', 'wiziq' );
												}
												else{
													$stat = ucfirst($res->live_status);
													_e( $stat, 'wiziq' );
												}
											}
											else
											{
												$stat = ucfirst($res->live_status);
												_e( $stat, 'wiziq' );
											}
										 ?>
									</td>
									<td>
										<?php if ( "upcoming" == $res->live_status) : ?>
											<a title="<?php _e('Edit this class', 'wiziq'); ?>" href="<?php echo WIZIQ_CLASS_MENU ?>&action=edit_class&class_id=<?php echo $res->id; ?>&course_id=<?php echo $course_id; ?>&wp_nonce=<?php echo $editnonce; ?>"><img title= "<?php _e('Edit this class', 'wiziq'); ?>" class = "classes-images" src= "<?php echo plugins_url( 'images/edit20.png' , dirname(__FILE__) ) ; ?>" alt ="<?php _e('Edit','wiziq'); ?>" /></a>
										<?php endif; ?>
										<?php if ( $res->is_recurring == "True")  {	?>
											<a title="<?php _e('View complete schedule', 'wiziq'); ?>" href="<?php echo WIZIQ_CLASS_MENU ?>&action=view_recurring_class&master_class_id=<?php echo $res->master_id; ?>&course_id=<?php echo $course_id; ?>" ><img title= "<?php _e('View complete schedule', 'wiziq'); ?>" class = "classes-images" src= "<?php echo plugins_url( 'images/list.png' , dirname(__FILE__) ) ; ?>" alt ="<?php _e( 'View' , 'wiziq' ); ?>" /></a>
										<?php } ?>
									</td>
									<td>
										<?php if ($res->attendence_report == "available" ) : ?>
											<a title="<?php _e('View list of attendees', 'wiziq'); ?>" href="<?php echo WIZIQ_CLASS_MENU ?>&action=view_attendee&response_class_id=<?php echo $res->response_class_id; ?>&course_id=<?php echo $course_id; ?>" ><?php _e('View','wiziq'); ?></a>
										<?php else : ?>
											---
										<?php endif; ?>
									</td>
									<td>
										<?php if ( "completed" == $res->status  &&  "true" == $res->recordclass ) : ?>
											<a target= "_blank" title="<?php _e('View recording', 'wiziq'); ?>" href="<?php echo $res-> response_recording_url;  ?>" ><?php _e('View', 'wiziq'); ?></a>
										<?php else : ?>
											---
										<?php endif; ?>
									</td>
									<td>
										<?php if ($res->download_recording  ) : ?>
										 <a title = "<?php _e('Download' , 'wiziq' ); ?>" href = "<?php echo $res->download_recording; ?>"><?php _e('Download','wiziq');?></a>
										 <?php
										elseif ( "upcoming" == $res->status  &&  "false" == $res->recordclass  && $recoding_status ) :
											_e( 'Recording not opted' , 'wiziq' );
										else :
										 ?>
										 ---
										<?php endif; ?>
									</td>
								</tr>
							<?php
						}
					} else {
						echo '<tr id = "course" class = "alternate iedit" >';
							echo '<td colspan = "9">';
								echo __('No classes available for this course','wiziq');
							echo '</td>';
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
			<div class= "tablenav bottom">
			<!-- display pagination-->
					<?php echo $pagination ; ?>
			</div>
			<br class="clear">
			<div class = "wiziq_hide" >
				<span id = "wiziq_are_u_sure" ><?php _e('Are you sure, you want to delete','wiziq');?></span>
				<span id = "wiziq_select_class" ><?php _e('Please select classes to delete','wiziq');?></span>
			</div>
		</form>
		<?php
	}// end add class form
	
	
	/*
	 * function to get and update the live data
	 * pass responce class id returned by wiziq when creating a class
	 * @since 1.0
	 */ 
	
	function wiziq_live_get_data ( $class_response_id ) {
		global $wpdb;
		$wiziq_api_functions = new wiziq_api_functions;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		//call api function and pass response class id
		$response = $wiziq_api_functions->wiziq_get_data ( $class_response_id ) ;
		if ( $response ) {
			
			$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
			$presenterid = $response->presenter_id;
			$presentername = $response->presenter_name;
			$presenterurl = $response->presenter_url;
			$starttime = $response->start_time;
			$timezone = $response->time_zone;
			$createrecording = $response->create_recording;
			$status = $response->status;
			$qry = "update $wiziq_classes SET 
					status                    = '" . $response->status . "', "
					. "classtimezone             = '" . $response->time_zone . "', "
					. "language                  = '" . $response->language_culture_name . "', "
					. "response_recording_url    = '" . $response->recording_url . "', "
					. "class_name                = '" . $response->title . "', "
					. "response_presenter_url    = '" . $response->presenter_url . "', "
					. "is_recurring              = '" . $response->is_recurring . "', "
					. "recordclass               = '" . $response->create_recording . "', "
					. "master_id                 = '" . $response->class_master_id . "', "
					. "attendee_limit            = '" . $response->attendee_limit . "', "
					. "attendence_report         = '" . $response->attendance_report_status . "'"
					. " WHERE response_class_id  =" . $class_response_id;
			$wpdb->query($qry);
		}
		$class_response = $this->wiziq_get_class_by_response_id ( $class_response_id );
		if ( "completed" == $class_response->status && "true" == $class_response->recordclass ) {
			//Get download link of completed and recording opted classes
			$download_record = $wiziq_api_functions->getdownloadlink($class_response_id);
			if ( $download_record ) {
				$qry = "update $wiziq_classes SET download_recording = '$download_record'
					WHERE response_class_id  =" . $class_response_id;
				$wpdb->query($qry);
			}
		} 
		return true;
	}// end get and update live class function
	
	/*
	 * Function to get a single class
	 * pass class id
	 * @since 1.0
	 */  
	function wiziq_get_class_by_id ( $class_id ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$qry = "select * from $wiziq_classes where id = '$class_id' ";
		$res = $wpdb->get_row($qry);
		if ( !empty($res) ) {
			return $res;
		} else {
			return false;
		}
	}// end function to get class data by class id
	
	/*
	 * Function to get a single class by course id and class id
	 * pass course id and class id
	 * @since 1.0
	 */ 
	function  wiziq_get_class_by_course_class ( $course_id, $class_id ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$qry = "select * from $wiziq_classes where id = '$class_id' and courseid = '$course_id' ";
		$res = $wpdb->get_row($qry);
		if ( !empty($res) ) {
			return $res;
		} else {
			return false;
		}
	}//  end function to get class by class id and course id
	
	/*
	 * Function to get a single class from response class id
	 * pass response class id which is returned by wiziq on creating a class
	 * @since 1.0
	 * 
	 */  
	function wiziq_get_class_by_response_id ( $response_class_id ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$qry = "select * from $wiziq_classes where response_class_id = '$response_class_id' ";
		$res = $wpdb->get_row($qry);
		if ( !empty($res) ) {
			return $res;
		} else {
			return false;
		}
	}// end function to get class data from response class id
	
	/*
	 * Function to delete a single class
	 * @since 1.0
	 */ 
	
	function wiziq_delete_single_class ( $class_id, $returnurl ,$course_id ) {
		$wiziq_api_functions = new wiziq_api_functions;
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$qry = "select status from $wiziq_classes where id = '$class_id'";
		$status = $wpdb->get_row($qry);
		$statusres = $status->status;
		// delete expired, cancelled and completed classes directly. no need to send request to server
		if ( "expired" == $statusres || "cancelled" == $statusres || "completed" == $statusres )
		{			
			$deletqry = $wpdb->query("delete from $wiziq_classes where id = '$class_id'");
		}
		else
		{
			$wiziq_api_functions->wiziq_cancel( $class_id );
		}
		?>
		<script>
			window.location = "<?php echo $returnurl."&action=view_course&course_id=".$course_id; ?>&sdelete";
		</script>
		<?php
	}
	
	/*
	 * Function to delete multiple classes
	 * pass post array
	 * @since 1.0
	 */ 
	function wiziq_delete_multiple_class ( $content , $returnurl) {
		$wiziq_api_functions = new wiziq_api_functions;
		$classes = $content['class-checkbox'];
		foreach( $classes as $class_id ) {
			global $wpdb;
			$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
			$qry = "select status from $wiziq_classes where id = '$class_id'";
			$status = $wpdb->get_row($qry);
			$statusres = $status->status;
			// delete expired, cancelled and completed classes directly. no need to send request to server
			if ( "expired" == $statusres || "cancelled" == $statusres || "completed" == $statusres )
			{
				$deletqry = $wpdb->query("delete from $wiziq_classes where id = '$class_id'");
			}
			else
			{
				$wiziq_api_functions->wiziq_cancel( $class_id );
			}
		}
	}
	
	/*
	 * @since 1.0
	 * Function to delete a single class for teacher deattivation
	 * pass class id
	 */ 
	
	function wiziq_delete_single_class_teacher ( $class_id ) {
		$wiziq_api_functions = new wiziq_api_functions;
		$wiziq_api_functions->wiziq_cancel( $class_id );
	}
	
	/*
	 * Function to get all the classes not in sorted
	 * Pass 1 if paginted result required else pass 0
	 * @since 1.0
	 */ 
	function wiziq_get_class ( $course_id , $pagination ,$start, $limit ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		if ( $pagination ) {
			$qry = "select *, status AS live_status from $wiziq_classes where courseid = '$course_id' order by id DESC LIMIT $start ,$limit" ;
			
		} else {
			$qry = "select * from $wiziq_classes where courseid = '$course_id' order by id DESC" ;
		}
		$wiziq_results = $wpdb->get_results( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}// end function to get classes result for a course
	
	
	/*
	 * Function to get all the classes in sorted way
	 * Alternative of wiziq_get_class for sorted result
	 * Pass 1 if paginted result required else pass 0
	 * @since 1.0
	 */ 
	function wiziq_get_class_sorted ( $course_id , $pagination ,$start, $limit , $sortby , $orderby ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		if ( $pagination && $sortby && $orderby) {
			$qry = "select *, status AS live_status from $wiziq_classes where courseid = '$course_id' order by $sortby $orderby LIMIT $start ,$limit" ;
			
		} else {
			$qry = "select * from $wiziq_classes where courseid = '$course_id' order by id DESC" ;
		}
		$wiziq_results = $wpdb->get_results( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}// end function to get classes result for a course
	
	/*
	 * Function to get all the recurring classes
	 * pass course id
	 * @since 1.0
	 */ 
	function wiziq_get_recurring_classes ( $course_id ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$wiziq_results = $wpdb->get_results( "select * from $wiziq_classes where is_recurring='True' and get_detail = '0' " );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}// end function to get recurring classes
	
	/*
	 * Function to add class
	 * pass post array
	 * @since 1.0
	 */ 
	function wiziq_add_classes ( $content ) {
		$wiziq_api_functions = new wiziq_api_functions;
		$response = $wiziq_api_functions->addLiveClass($content);
	}// end add class function
	
	
	
	/*
	 * Function to check if there is a teacher in a course
	 * @since 1.0
	 */ 
	 function wiziq_check_teacher_course( $course_id ) {
		 global $wpdb;
		 $wiziq_enroluser = $wpdb->prefix."wiziq_enroluser";
		 $qry = "select * from $wiziq_enroluser where course_id = '$course_id' and create_class = '1'";
		 $res = $wpdb->get_results($qry);
		 return $res;
	 }
	
	/*
	 * Function of add class form
	 * pass nonce, course id and return url
	 * @since 1.0
	 */ 
	function wiziq_add_classes_form ( $nonce, $course_id , $returnurl ) {
		
		//Check for valid request
		if ( ! wp_verify_nonce( $nonce , 'add-class-'.$course_id  ) ) {
			?>
			<script>
				window.location = "<?php echo $returnurl; ?>";
			</script>
		<?php
		}
		else {
			global $wpdb;
			$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
			$wiziq_api_functions = new wiziq_api_functions;
			$wiziq_teachers = new Wiziq_Teachers;
			$current_user = get_current_user_id();
			
			
			if ( isset ($_POST ['add_class_wiziq'] ) ) {
				$classcon = $_POST;
			} else {
				$classcon = 0;
			}
			?>
		<h2><?php _e('Add New Class','wiziq'); ?></h2>
		<?php		
			// display error if any while viewing the class
			global $myerror;
			if ( is_wp_error( $myerror ) ) {
					$add_error = $myerror->get_error_message('wiziq_class_add_error');
					if ( $add_error ) {
						echo $add_error;
					}
			} 
			
			$chec_teacher = $this->wiziq_check_teacher_course( $course_id );
			if ( empty ( $chec_teacher ) ) {
				echo '<br><h4>'.__( 'Please enroll/add teacher first to schedule the class.' , 'wiziq' ).'</h4>';
				return;
			}
			
			/*
			 * get list of teachers
			 */ 
			$teacherlist = $wiziq_teachers->wiziq_get_teachers_in_course( $course_id ); 
			if (!empty ($teacherlist)) {
				foreach ($teacherlist as $teacher_res){ 
					$teachers[]=$teacher_res->user_id;
				}
			}
		?>
		<form method = "post" id= "add_class_form" name= "add_class_form" >
			<div class= "wiziq_hide" id = "class_name_wrong" ><?php _e("Class title can't be empty.", 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_durantion_wrong" ><?php _e('Please enter duration between 30 to 300 minutes.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_start_date_wrong" ><?php _e('Start date required.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_end_date_wrong" ><?php _e('Please enter end date.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_end_date_occurance_wrong" ><?php _e('Please enter number of classes.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_end_occurance_wrong" ><?php _e('You can add upto 60 classes.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_week_days_error" ><?php _e('Please select week days.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_attendee_error" ><?php _e(' Please enter users between 1 and 1999.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_attendee_number_error" ><?php _e('Please enter number.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "wiziq_class_repeat_error" ><?php _e('Please select when this class repeats.', 'wiziq'); ?></div>
			<table class = "form-table" >
				<tbody>
					<tr>
						<th><?php _e('Class title', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<input maxlength= "70" type = "text" class = "regular-text" id = "class_name" name = "class_name"  value  = "<?php if (isset($_POST['class_name'])) echo $_POST['class_name']; ?>" />
							<div class = "wiziq_error" id = "class_name_err" ></div>
						</td>
					</tr>
					
					<tr>
						<?php 
							if(isset ($_POST['classmethod'])) 
							{
								$classmethod = $_POST['classmethod'];
								if ( "single" == $classmethod ){
									$singlechecked = " checked ";
									$recusivecheked = "";
								} else {
									$recusivecheked = " checked ";
									$singlechecked = "";
								}
							} 
							else 
							{
								$singlechecked = " checked ";
								$recusivecheked = "";
							}
						?>
						<td>
							&nbsp;
						</td>
						<td>
							<input type="radio" name="classmethod" class= "wiziq_class_type"  value = "single"  <?php echo $singlechecked; ?>    ><?php _e('Want to schedule a single class', 'wiziq'); ?><br>
							<input type="radio" name="classmethod" class= "wiziq_class_type"  value = "recurring"  <?php echo $recusivecheked; ?>><?php _e('Want to schedule a recurring class', 'wiziq'); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Teacher','wiziq');?></th>
						<td>
							<select name = "wiziq_teacher">
								<option value = "0" ><?php _e('Select teacher','wiziq');?></option>
								<?php 
									
									if (isset ($teachers) && !empty($teachers) ) {
										foreach ( $teachers as $id ) {
											if(isset ($_POST['wiziq_teacher']) &&  $id == $_POST['wiziq_teacher'] ) 
											{
												$selected = ' selected ';
											} else {
												$selected = '';
											}
											$user_info = get_userdata( $id );
											if(!empty($user_info->first_name)){
												$teacher_name = $user_info->first_name.' '.$user_info->last_name;
											} else	{
												$teacher_name = $user_info->display_name;
											}
											echo '<option value = "'.$id.'" '.$selected.' >'.$teacher_name.'</option>';
										}
									}
								?>
							</select>
						</td>
					</tr>
					<?php
					if ( $classcon && $classcon['classmethod'] == "single" ) {
						$class = 'wiziq_class_schedule wiziq_hide';
					} else if ( $classcon && $classcon['classmethod'] == "recurring" ) {
						$class = 'wiziq_class_schedule ';
					}
					else {
						$class = 'wiziq_class_schedule wiziq_hide';
					}
					?>
					<tr class = "<?php echo $class ?>" id = "class_recurring" >
						<th>
							<div>
								<?php _e('Class schedule', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span>
							</div>
						</th>
						<td>
							<div>
							<?php 
							if ( isset ($classcon) ) {
								$repeat = $classcon['class_repeat'];
							}
							else {
								$repeat = 0;
							}
							?>
								<select id="wiziq_class_repeat" name="class_repeat">
									<option value="0" <?php if ($repeat == "0") { echo  " selected "; } ?>  ><?php _e('Select when class repeats', 'wiziq' ) ;?></option>
									<option value="1" <?php if ($repeat == "1") { echo  " selected "; } ?>><?php _e('Daily (all 7 Days)', 'wiziq' ) ;?></option>
									<option value="2" <?php if ($repeat == "2") { echo  " selected "; } ?>><?php _e('6 Days (Mon-Sat)', 'wiziq' ) ;?></option>
									<option value="3" <?php if ($repeat == "3") { echo  " selected "; } ?>><?php _e('5 Days (Mon-Fri)', 'wiziq' ) ;?></option>
									<option value="4" <?php if ($repeat == "4") { echo  " selected "; } ?>><?php _e('Weekly', 'wiziq' ) ;?></option>
									<option value="5" <?php if ($repeat == "5") { echo  " selected "; } ?>><?php _e('Monthly', 'wiziq' ) ;?></option>
								</select>
							</div>
							<div class = "wiziq_error" id = "wiziq_class_repeat_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Select date', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<input type = "text" class = "regular-text" id = "class_start_date" name="class_time" value = "<?php if (isset($_POST['class_time'])) echo $_POST['class_time']; ?>" /> 
							<div class = "wiziq_error" id = "class_start_date_err" ></div>
						</td>
					</tr>
					</tr>
					<tr>
						<th><?php _e('Class time', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td><?php _e('Hours','wiziq');?>
							<select id="start_time_hours" name="hours" >
								<option value="00" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "00") { echo  ' selected '; } ?>>00</option>
								<option value="01" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "01") { echo  ' selected '; } ?>>01</option>
								<option value="02" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "02") { echo  ' selected '; } ?>>02</option>
								<option value="03" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "03") { echo  ' selected '; } ?>>03</option>
								<option value="04" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "04") { echo  ' selected '; } ?>>04</option>
								<option value="05" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "05") { echo  ' selected '; } ?>>05</option>
								<option value="06" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "06") { echo  ' selected '; } ?>>06</option>
								<option value="07" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "07") { echo  ' selected '; } ?>>07</option>
								<option value="08" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "08") { echo  ' selected '; } ?>>08</option>
								<option value="09" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "09") { echo  ' selected '; } ?>>09</option>
								<option value="10" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "10") { echo  ' selected '; } ?>>10</option>
								<option value="11" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "11") { echo  ' selected '; } ?>>11</option>
								<option value="12" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "12") { echo  ' selected '; } ?>>12</option>
								<option value="13" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "13") { echo  ' selected '; } ?>>13</option>
								<option value="14" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "14") { echo  ' selected '; } ?>>14</option>
								<option value="15" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "15") { echo  ' selected '; } ?>>15</option>
								<option value="16" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "16") { echo  ' selected '; } ?>>16</option>
								<option value="17" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "17") { echo  ' selected '; } ?>>17</option>
								<option value="18" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "18") { echo  ' selected '; } ?>>18</option>
								<option value="19" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "19") { echo  ' selected '; } ?>>19</option>
								<option value="20" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "20") { echo  ' selected '; } ?>>20</option>
								<option value="21" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "21") { echo  ' selected '; } ?>>21</option>
								<option value="22" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "22") { echo  ' selected '; } ?>>22</option>
								<option value="23" <?php if ( isset ($classcon['hours']) && $classcon['hours'] == "23") { echo  ' selected '; } ?>>23</option>
							</select>
							<?php _e('Minutes','wiziq');?>
							<select id="start_time_minutes" name="minutes" >
								<?php 
								for ( $i = 0; $i < 60 ; $i++ ){
									if($i<10) {
										$val = "0".$i;
										?>
										<option value="<?php echo  $val ;?>"  <?php if ( isset($classcon['minutes']) &&  $classcon['minutes'] == $val) { echo  ' selected '; } ?> ><?php echo $val; ?></option>
										<?php
									}
									else {
										?>
										<option value="<?php echo $i; ?>"  <?php if ( isset($classcon['minutes']) &&  $classcon['minutes'] ==  $i) { echo  " selected "; } ?> ><?php echo $i; ?></option>
										<?php
									}
								}
								?>
							</select>
							<label>
								<input type="checkbox" id = "class_schedule" name="schedule_now" value="1"><?php _e( 'Schedule right now' , 'wiziq' ); ?>
							</label>
							
						</td>
					</tr>
					<tr>
						<th><?php _e('Class duration', 'wiziq'); ?><span class="description"> (<?php _e('in minutes', 'wiziq' ); ?>)</span><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<input type = "text" class = "regular-text" id = "class_duration" name = "duration" value = "<?php if (isset($_POST['duration'])) { echo $_POST['duration']; } else { echo '60'; }?>" />
							<p class= "description" id= "class_duration_ms" ><?php _e( 'Minimum 30 min and maximum 300 min' , 'wiziq' ); ?> </p>
							<div class = "wiziq_error" id = "class_duration_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Time zone', 'wiziq'); ?></th>
						<td>
							<?php
							$qry = "select id,created_by, classtimezone from $wiziq_classes where created_by = '$current_user' order by id desc";
							$res = $wpdb->get_row($qry);
							?>
							<select id="class_timezone" name="classtimezone">
							<?php 
							
							$timezone = $wiziq_api_functions->getTimeZone();
							foreach ($timezone as $key => $values) {
								echo  $key;
								if ( !empty ($res) && $key == $res->classtimezone ) {
									$selected =  ' selected';
								} else {
									$selected =  '';
								}
							?>
								<option value = "<?php echo $key; ?>"  <?php if(isset ($_POST['classtimezone'] ) ) {  if ( $_POST['classtimezone'] == $key ) echo ' selected' ; } else echo $selected; ?> ><?php echo $values; ?></option>
							<?php 
							
							}
							?>
							</select>
							
						</td>
					</tr>
					<?php
					if ( $classcon && $classcon['class_repeat'] == "4" ) {
						$class = 'wiziq_repeat_week_class_schedule';
					} else {
						$class = 'wiziq_repeat_week_class_schedule wiziq_hide';
					}
					?>
					<tr class = "<?php echo $class; ?>" >
							<th><?php _e('Repeat every week', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
							<td>
							<?php 
								if ( isset ($classcon) ) {
									$week = $classcon['specific_week'];
								}
								else {
									$week = 0;
								}
								?>
								<select id="class_specific_week" name="specific_week">
									<option value="1" <?php if ($week == "1") { echo  " selected "; } ?> >1</option>
									<option value="2" <?php if ($week == "2") { echo  " selected "; } ?>>2</option>
									<option value="3" <?php if ($week == "3") { echo  " selected "; } ?>>3</option>
									<option value="4" <?php if ($week == "4") { echo  " selected "; } ?>>4</option>
									<option value="5" <?php if ($week == "5") { echo  " selected "; } ?>>5</option>
								</select><?php _e( 'Week' , 'wiziq');?>
							</td>
					</tr>
					<?php
					if ( $classcon && $classcon['class_repeat'] == "4" ) {
						$class = 'wiziq_weekly_class';
					} else {
						$class = 'wiziq_weekly_class wiziq_hide';
					}
					$sun=0;
					$mon=0;
					$tue=0;
					$wed=0;
					$thu=0;
					$fri=0;
					$sat=0;
					if(isset ($classcon['days_of_week'] ) ){
						if($classcon['days_of_week'][0] == "sunday")
						$sun=1;
						if($classcon['days_of_week'][0] == "monday" || $classcon['days_of_week'][1] == "monday")
						$mon=1;
						if($classcon['days_of_week'][0] == "tuesday" || $classcon['days_of_week'][1] == "tuesday"|| $classcon['days_of_week'][2] == "tuesday")
						$tue=1;
					    if($classcon['days_of_week'][0] == "wednesday" || $classcon['days_of_week'][1] == "wednesday" || $classcon['days_of_week'][2] == "wednesday" || $classcon['days_of_week'][3] == "wednesday")
					    $wed=1;
					    if($classcon['days_of_week'][0] == "thursday" || $classcon['days_of_week'][1] == "thursday"|| $classcon['days_of_week'][2] == "thursday"|| $classcon['days_of_week'][3] == "thursday"|| $classcon['days_of_week'][4] == "thursday")
					    $thu=1;
					    if($classcon['days_of_week'][0] == "friday"|| $classcon['days_of_week'][1] == "friday"|| $classcon['days_of_week'][2] == "friday"|| $classcon['days_of_week'][3] == "friday"|| $classcon['days_of_week'][4] == "friday"|| $classcon['days_of_week'][5] == "friday")
					    $fri=1;
					    if($classcon['days_of_week'][0] == "saturday"||$classcon['days_of_week'][1] == "saturday"||$classcon['days_of_week'][2] == "saturday"||$classcon['days_of_week'][3] == "saturday"||$classcon['days_of_week'][4] == "saturday"||$classcon['days_of_week'][5] == "saturday"||$classcon['days_of_week'][6] == "saturday")
					    $sat=1;
					}
					?>
					<tr class= "<?php echo $class; ?>" id= "wiziq_class_weekly">
							<th><?php _e('On', 'wiziq'); ?></th>
							<td>
								<ul>
									<li><input class= "week_days_check" type="checkbox" name="days_of_week[]" value="sunday" <?php if ( $classcon && $sun) echo "checked"; ?> > <span class = "weekly_class" >S</span></li>
									<li><input class= "week_days_check" type="checkbox" name="days_of_week[]" value="monday" <?php if ( $classcon && $mon) echo "checked"; ?>><span class = "weekly_class" >M</span></li>
									<li><input class= "week_days_check" type="checkbox" name="days_of_week[]" value="tuesday" <?php if ( $classcon && $tue ) echo "checked"; ?>><span class = "weekly_class" >T</span></li>
									<li><input class= "week_days_check" type="checkbox" name="days_of_week[]" value="wednesday" <?php if ( $classcon && $wed) echo "checked"; ?>><span class = "weekly_class" >W</span></li>
									<li><input class= "week_days_check" type="checkbox" name="days_of_week[]" value="thursday" <?php if ( $classcon && $thu) echo "checked"; ?>><span class = "weekly_class" >T</span></li>
									<li><input class= "week_days_check" type="checkbox" name="days_of_week[]" value="friday" <?php if ( $classcon && $fri) echo "checked"; ?>><span class = "weekly_class" >F</span></li>
									<li><input class= "week_days_check" type="checkbox" name="days_of_week[]" value="saturday" <?php if ( $classcon && $sat) echo "checked"; ?>><span class = "weekly_class" >S</span></li>
								</ul>
								<br class= "clearfix">
								<div class= "wiziq_error" id="class_week_days_error_msg" ></div>
							</td>
					</tr>
					<?php
					if ( $classcon && $classcon['class_repeat'] == "5" ) {
						$class = 'wiziq_monthly_class';
					} else {
						$class = 'wiziq_monthly_class wiziq_hide';
					}
					?>
					<tr class= "<?php echo $class; ?>">
						<th><?php _e('Repeat by', 'wiziq'); ?></th>
						<td>
						<?php
							if(isset ($classcon['class_repeatby_type']) && $classcon['class_repeatby_type'] == "repeat_day" ) 
							{		
								$repeat_day = " checked ";
								$repeat_date = "";
							} 
							else if ($classcon['class_repeatby_type'] && $classcon['class_repeatby_type'] == "repeat_date" )
							{
								$repeat_date = " checked " ;
								$repeat_day = "" ;
							}
							else 
							{
								$repeat_date = " checked ";
								$repeat_day = "";
							}
						?>
						
							<input type="radio" name="class_repeatby_type" class= "wiziq_class_monhtly_repeat" <?php echo $repeat_date; ?> value = "repeat_date"><?php _e('Date', 'wiziq'); ?>
							<input type="radio" name="class_repeatby_type" class= "wiziq_class_monhtly_repeat" <?php echo $repeat_day; ?> value = "repeat_day"><?php _e('Day', 'wiziq'); ?>
						</td>
					</tr>
					<?php
					if ( $classcon && $classcon['class_repeat'] == "5" ) {
						$class = 'wiziq_monthly_class';
					} else {
						$class = 'wiziq_monthly_class wiziq_hide';
					}
					?>
					<tr class= "<?php echo $class; ?>">
						<th><?php _e('On', 'wiziq'); ?></th>
						<td>
							<?php
							if ( $classcon && $classcon['class_repeatby_type'] == "repeat_date" ) {
								$dayclass = 'wiziq_hide';
								$dateclass = "";
							} else if ( $classcon && $classcon['class_repeatby_type'] == "repeat_day" ) {
								$dayclass = '';
								$dateclass = "wiziq_hide";
							} else {
								$dayclass = 'wiziq_hide';
								$dateclass = "";
							}
							?>
						
							<div class="<?php echo $dayclass; ?>" id = "month_day_repeat" >
								<?php 
								if ( isset ($classcon) ) {
									$monthday = $classcon['every_month_day_no'];
								}
								else {
									$monthday = 0;
								}
								?>
								<select id="every_month_day_no" name="every_month_day_no">
									<option value="1st" >1st</option>
									<option value="2nd" <?php if ($monthday == "2nd") { echo  " selected "; } ?> >2nd</option>
									<option value="3rd" <?php if ($monthday == "3rd") { echo  " selected "; } ?> >3rd</option>
									<option value="4th" <?php if ($monthday == "4th") { echo  " selected "; } ?> >4th</option>
									<option value="Last" <?php if ($monthday == "Last") { echo  " selected "; } ?> ><?php _e('Last','wiziq');?></option>
								</select>
								<?php 
								if ( isset ($classcon) ) {
									$monthday_day = $classcon['every_month_day_day'];
								}
								else {
									$monthday_day = 0;
								}
								?>
								<select id="every_month_day_day" name="every_month_day_day">
									<option value="monday" ><?php _e('Mon', 'wiziq' )  ?></option>
									<option value="tuesday" <?php if ($monthday_day == "tuesday") { echo  " selected "; } ?> ><?php _e('Tue', 'wiziq' )  ?></option>
									<option value="wednesday" <?php if ($monthday_day == "wednesday") { echo  " selected "; } ?> ><?php _e('Wed', 'wiziq' )  ?></option>
									<option value="thursday" <?php if ($monthday_day == "thursday") { echo  " selected "; } ?> ><?php _e('Thr', 'wiziq' )  ?></option>
									<option value="friday" <?php if ($monthday_day == "friday") { echo  " selected "; } ?> ><?php _e('Fri', 'wiziq' )  ?></option>
									<option value="saturday" <?php if ($monthday_day == "saturday") { echo  " selected "; } ?> ><?php _e('Sat', 'wiziq' )  ?></option>
									<option value="sunday" <?php if ($monthday_day == "sunday") { echo  " selected "; } ?> ><?php _e('Sun', 'wiziq' )  ?></option>
								</select> <?php _e('of every month' , 'wiziq');?>
							</div>
							<div class="<?php echo $dateclass; ?>" id= "month_date_repeat" >
								<?php 
									if ( isset ($classcon) ) {
										$every_month_date = $classcon['every_month_date'];
									}
									else {
										$every_month_date = 0;
									}
									?>
									<select id="every_month_day_no" name="every_month_date">
										<option value="1st">1st</option>
										<option value="2nd" <?php if ($every_month_date == "2nd") { echo  " selected "; } ?> >2nd</option>
										<option value="3rd" <?php if ($every_month_date == "3rd") { echo  " selected "; } ?> >3rd</option>
										<option value="4th" <?php if ($every_month_date == "4th") { echo  " selected "; } ?> >4th</option>
										<option value="5th" <?php if ($every_month_date == "5th") { echo  " selected "; } ?> >5th</option>
										<option value="6th" <?php if ($every_month_date == "6th") { echo  " selected "; } ?> >6th</option>
										<option value="7th" <?php if ($every_month_date == "7th") { echo  " selected "; } ?> >7th</option>
										<option value="8th" <?php if ($every_month_date == "8th") { echo  " selected "; } ?> >8th</option>
										<option value="9th" <?php if ($every_month_date == "9th") { echo  " selected "; } ?> >9th</option>
										<option value="10th" <?php if ($every_month_date == "10th") { echo  " selected "; } ?> >10th</option>
										<option value="11th" <?php if ($every_month_date == "11th") { echo  " selected "; } ?> >11th</option>
										<option value="12th" <?php if ($every_month_date == "12th") { echo  " selected "; } ?> >12th</option>
										<option value="13th" <?php if ($every_month_date == "13th") { echo  " selected "; } ?> >13th</option>
										<option value="14th" <?php if ($every_month_date == "14th") { echo  " selected "; } ?> >14th</option>
										<option value="15th" <?php if ($every_month_date == "15th") { echo  " selected "; } ?> >15th</option>
										<option value="16th" <?php if ($every_month_date == "16th") { echo  " selected "; } ?> >16th</option>
										<option value="17th" <?php if ($every_month_date == "17th") { echo  " selected "; } ?> >17th</option>
										<option value="18th" <?php if ($every_month_date == "18th") { echo  " selected "; } ?> >18th</option>
										<option value="19th" <?php if ($every_month_date == "19th") { echo  " selected "; } ?> >19th</option>
										<option value="20th" <?php if ($every_month_date == "20th") { echo  " selected "; } ?> >20th</option>
										<option value="21st" <?php if ($every_month_date == "21st") { echo  " selected "; } ?> >21st</option>
										<option value="22nd" <?php if ($every_month_date == "22nd") { echo  " selected "; } ?> >22nd</option>
										<option value="23rd" <?php if ($every_month_date == "23rd") { echo  " selected "; } ?> >23rd</option>
										<option value="24th" <?php if ($every_month_date == "24th") { echo  " selected "; } ?> >24th</option>
										<option value="25th" <?php if ($every_month_date == "25th") { echo  " selected "; } ?> >25th</option>
										<option value="26th" <?php if ($every_month_date == "26th") { echo  " selected "; } ?> >26th</option>
										<option value="27th" <?php if ($every_month_date == "27th") { echo  " selected "; } ?> >27th</option>
										<option value="28th" <?php if ($every_month_date == "28th") { echo  " selected "; } ?> >28th</option>
										<option value="29th" <?php if ($every_month_date == "29th") { echo  " selected "; } ?> >29th</option>
										<option value="30th" <?php if ($every_month_date == "30th") { echo  " selected "; } ?> >30th</option>
										<option value="31st" <?php if ($every_month_date == "31st") { echo  " selected "; } ?> >31st</option>
									</select><?php _e( 'of every month' , 'wiziq' ); ?>
								</div>
							</td>
						</tr>
					<?php
					if ( $classcon && $classcon['classmethod'] == "single" ) {
						$class = 'wiziq_end_class wiziq_hide';
					} else if ( $classcon && $classcon['classmethod'] == "recurring" ) {
						$class = 'wiziq_end_class ';
					} else {
						$class = 'wiziq_end_class wiziq_hide';
					}
					if ( isset ($classcon['class_end_date'] ) && $classcon ) {
						$classname = 'class_end_date';
						$classnamevalue = $classcon['class_end_date'];
					} else {
						$classname = 'class_occurrence';
						$classnamevalue = $classcon['class_occurrence'];
					}
					if(isset ($classcon['class_occurrence_type']) && $classcon['class_occurrence_type'] == "after_class" ) 
					{		
						$aftrnclass = " checked ";
						$datechecked = "";
					} 
					else if ($classcon['class_occurrence_type'] && $classcon['class_occurrence_type'] == "on_date" )
					{
						$datechecked = " checked ";
						$aftrnclass = "";
					}
					else 
					{
						$aftrnclass = " checked ";
						$datechecked = "";
					}
					?>	
					<tr class = "<?php echo $class; ?>" id= "wiziq_end_class">
						<th><?php _e('Ends', 'wiziq'); ?></th>
						<td>
							<input type="radio" id= "" name="class_occurrence_type" class= "wiziq_class_end" checked="checked" value = "after_class" <?php echo $aftrnclass; ?>>
							<?php _e('After classes', 'wiziq'); ?> 
							<input type = "text" class = "regular-text wiziq_class_end_class" id = "wiziq_class_end_date_in" name = "class_occurrence" value= "<?php echo $classcon['class_occurrence'] ; ?>" /><br>
							<input type="radio" id= "" name="class_occurrence_type" class= "wiziq_class_end" value = "on_date" <?php echo $datechecked ; ?> >
							<?php _e('On date', 'wiziq'); ?>
							<input type = "text" class = "regular-text wiziq_class_end_class" id = "wiziq_class_end_date_occurance" name = "class_end_date" value= "<?php echo $classcon['class_end_date'] ; ?>" />
							<div class = "wiziq_error" id = "class_end_date_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Attendee limit in a class', 'wiziq'); ?></th>
						<td>
							<input maxlength= "10" type = "text" class = "regular-text" id = "class_attendee_limit" name = "attendee_limit" value  = "<?php if (isset($_POST['attendee_limit'])) { echo $_POST['attendee_limit']; } else { echo '10'; } ?>" />
							<div class = "wiziq_error" id = "class_attendee_limit_err" ></div>
						</td>
					</tr>
					<tr class = "wiziq_end_class" id= "wiziq_end_class">
						<th><?php _e('Record this class', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<?php 
							if( isset( $classcon['recordclass'] ) && $classcon['recordclass']  == "true") {
								$truechecked =  ' checked '; 
								$falsechecked =  ' '; 
							}
							else if( isset( $classcon['recordclass'] ) && $classcon['recordclass']  == "false") {
								$truechecked =  ' '; 
								$falsechecked =  ' checked '; 
							} else {
								$truechecked =  ' checked '; 
								$falsechecked =  ' '; 
							}
							?>
							<input type="radio" name="recordclass"  value = "true" <?php echo $truechecked; ?> ><?php _e('Yes', 'wiziq'); ?>
							<input type="radio" name="recordclass" value = "false" <?php echo $falsechecked; ?> ><?php _e('No', 'wiziq'); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Language of instruction', 'wiziq'); ?></th>
						<td>
							<div class = "wiziq_error" id = "class_attendee_limit_err" ></div>
							<?php 
								$language = $wiziq_api_functions->getLanguages();
								$serialized_languages =  maybe_serialize($language);
								update_option ( 'wiziq_languages', $serialized_languages );
							?>
							<select id="class_language" name="language">
							<?php
							foreach ($language as $key => $values) {
							?>
								<option value = "<?php echo $key; ?>"  <?php if(isset ($_POST['language'] ) && $_POST['language'] == $key ) echo ' selected'; elseif ('en-US' == $key ) { echo ' selected' ; } ?> ><?php echo $values; ?></option>
							<?php 
							}
							?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<input type= "hidden" name = "courseid" value = "<?php echo $_REQUEST['course_id']; ?>" />
			<input class= "button button-primary wiziq-button" id = "add_class_wiziq" type = "Submit" name = "add_class_wiziq" value="<?php _e('Schedule and Continue', 'wiziq' ); ?> " /> 
			<a class= "button button-primary wiziq-button" id = "wiziq_cancel_class" href = "<?php echo WIZIQ_CLASS_MENU; ?>&action=view_course&course_id=<?php echo $_REQUEST['course_id']; ?>" ><?php _e('Cancel', 'wiziq' ); ?></a>
		</form>
		<?php
		}
	}// end add class form function
	
	/*
	 * Function to display edit class form
	 * pass nonce, db class id, course id and returnurl
	 * @since 1.0
	 */ 
	function wiziq_edit_class_form ( $nonce, $class_id , $course_id , $returnurl ) {
		/* 
		 * check if valid request
		 */ 
		if ( ! wp_verify_nonce( $nonce , 'edit-class-'.$class_id  ) ) {
		?>
		<script>
			window.location = "<?php echo $returnurl; ?>";
		</script>
		<?php
		}
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$wiziq_api_functions = new wiziq_api_functions;
		$result = $this->wiziq_get_class_by_id ( $class_id );
		// if form submitted
		if(isset ( $_POST['wiziq_edit_class'] )) {
			$classcon = $_POST;
			$api_response = $wiziq_api_functions->updateSingleLiveClass($classcon , $class_id);
			if ( $api_response ) {
				if (isset($data['schedule_now'])) {
					$start_time =  date('m/d/Y H:i:s');     //date('m/d/Y H:i:s', strtotime($data['class_time']));
				} else {
					$start_time = date('m/d/Y', strtotime($classcon['class_time'])) . ' ' . $classcon['hours'] . ':' . $classcon['minutes'];
				}
				
				$updqry = "update $wiziq_classes set class_name = '".$classcon['class_name']."',
				duration = '".$classcon['duration']."',
				class_time  = '".date ("Y-m-d H:i:s" , strtotime($start_time))."',
				classtimezone = '".$classcon['classtimezone']."',
				attendee_limit  = '".$classcon['attendee_limit']."',
				recordclass = '".$classcon['recordclass']."',
				language = '".$classcon['language']."'
				where response_class_id  = '$result->response_class_id'
				";
				$wpdb->query($updqry) ;
						?>
				<script>
					window.location = "<?php echo WIZIQ_CLASS_MENU.'&action=class_detail&class_id='.$class_id.'&course_id='.$course_id; ?>&editsuccess";
				</script>
				<?php
			}
		}
		$result = $this->wiziq_get_class_by_id ( $class_id );

		?>
			<form method = "post" id= "add_class_form" >
			<div class= "wiziq_hide" id = "class_name_wrong" ><?php _e("Class Title can't be empty.", 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_durantion_wrong" ><?php _e('Please enter duration between 30 to 300 minutes.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_start_date_wrong" ><?php _e('Start date required.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_end_date_wrong" ><?php _e('Please enter end date.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_end_date_occurance_wrong" ><?php _e('Please enter number of classes.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_end_occurance_wrong" ><?php _e('You can add upto 60 classes.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_week_days_error" ><?php _e('Please select week days.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_attendee_error" ><?php _e(' Please enter users between 1 and 1999. ', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_attendee_number_error" ><?php _e('Please enter number.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "wiziq_class_repeat_error" ><?php _e('Please select when this class repeats.', 'wiziq'); ?></div>
			<h3><?php _e('Edit Class','wiziq'); ?></h3>
			<table class = "form-table" >
				<tbody>
					<tr>
						<th><?php _e('Class title', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<input value = "<?php echo $result->class_name; ?>" maxlength= "70" type = "text" class = "regular-text" id = "class_name" name = "class_name" />
							<div class = "wiziq_error" id = "class_name_err" ></div>
						</td>
					</tr>
					
					<tr>
						<th><?php _e('Select date', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
						<?php
						$classdate = date("m/d/Y", strtotime($result->class_time) );
						$classhour = date("H", strtotime($result->class_time) );
						$classmin = date("i", strtotime($result->class_time) );
						?>
							<input value= "<?php echo $classdate; ?>" type = "text" class = "regular-text" id = "class_start_date" name="class_time" />
							<div class = "wiziq_error" id = "class_start_date_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Class time', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<?php _e('Hours','wiziq');?>
							<select id="start_time_hours" name="hours" >
								<option value="00" <?php if ($classhour == "00") { echo  ' selected '; } ?>>00</option>
								<option value="01" <?php if ($classhour == "01") { echo  ' selected '; } ?>>01</option>
								<option value="02" <?php if ($classhour == "02") { echo  ' selected '; } ?>>02</option>
								<option value="03" <?php if ($classhour == "03") { echo  ' selected '; } ?>>03</option>
								<option value="04" <?php if ($classhour == "04") { echo  ' selected '; } ?>>04</option>
								<option value="05" <?php if ($classhour == "05") { echo  ' selected '; } ?>>05</option>
								<option value="06" <?php if ($classhour == "06") { echo  ' selected '; } ?>>06</option>
								<option value="07" <?php if ($classhour == "07") { echo  ' selected '; } ?>>07</option>
								<option value="08" <?php if ($classhour == "08") { echo  ' selected '; } ?>>08</option>
								<option value="09" <?php if ($classhour == "09") { echo  ' selected '; } ?>>09</option>
								<option value="10" <?php if ($classhour == "10") { echo  ' selected '; } ?>>10</option>
								<option value="11" <?php if ($classhour == "11") { echo  ' selected '; } ?>>11</option>
								<option value="12" <?php if ($classhour == "12") { echo  ' selected '; } ?>>12</option>
								<option value="13" <?php if ($classhour == "13") { echo  ' selected '; } ?>>13</option>
								<option value="14" <?php if ($classhour == "14") { echo  ' selected '; } ?>>14</option>
								<option value="15" <?php if ($classhour == "15") { echo  ' selected '; } ?>>15</option>
								<option value="16" <?php if ($classhour == "16") { echo  ' selected '; } ?>>16</option>
								<option value="17" <?php if ($classhour == "17") { echo  ' selected '; } ?>>17</option>
								<option value="18" <?php if ($classhour == "18") { echo  ' selected '; } ?>>18</option>
								<option value="19" <?php if ($classhour == "19") { echo  ' selected '; } ?>>19</option>
								<option value="20" <?php if ($classhour == "20") { echo  ' selected '; } ?>>20</option>
								<option value="21" <?php if ($classhour == "21") { echo  ' selected '; } ?>>21</option>
								<option value="22" <?php if ($classhour == "22") { echo  ' selected '; } ?>>22</option>
								<option value="23" <?php if ($classhour == "23") { echo  ' selected '; } ?>>23</option>
							</select>
							<?php _e('Minutes','wiziq');?>
							<select id="start_time_minutes" name="minutes" >
								<?php 
								for ( $i = 0; $i < 60 ; $i++ ){
									if($i<10) {
										$val = "0".$i;
										?>
										<option value="<?php echo  $val ;?>"  <?php if ($classmin == $val) { echo  ' selected '; } ?> ><?php echo $val; ?></option>
										<?php
									}
									else {
										?>
										<option value="<?php echo $i; ?>"  <?php if ($classmin == $i) { echo  " selected "; } ?> ><?php echo $i; ?></option>
										<?php
									}
								}
								?>
							</select>
							<input type="checkbox" id = "class_schedule" name="schedule_now" value="1"><?php _e( 'Schedule right now' , 'wiziq' ); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Class duration', 'wiziq'); ?><span class="description"> (<?php _e('in minutes', 'wiziq' ); ?>)</span><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<input value = "<?php echo $result->duration; ?>" type = "text" class = "regular-text" id = "class_duration" name = "duration" />
							<div id= "class_duration_ms" ><?php _e( 'Minimum 30 min and Maximum 300 min' , 'wiziq' ); ?> </div>
							<div class = "wiziq_error" id = "class_duration_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Time zone', 'wiziq'); ?></th>
						<td>
							<select id="class_timezone" name="classtimezone">
							<?php
							
							$timezone = $wiziq_api_functions->getTimeZone();
							foreach ($timezone as $key => $values) {
							?>
								<option value = "<?php echo $key; ?>" <?php if( $result->classtimezone == $key ) echo ' selected'; ?> ><?php echo $values; ?></option>
							<?php 
							}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php _e('Attendee limit in a class', 'wiziq'); ?></th>
						<td>
							<input value= "<?php echo $result->attendee_limit; ?>" maxlength= "10" type = "text" class = "regular-text" id = "class_attendee_limit" name = "attendee_limit" />
							<div class = "wiziq_error" id = "class_attendee_limit_err" ></div>
						</td>
					</tr>
					<tr class = "wiziq_end_class" id= "wiziq_end_class">
						<th><?php _e('Record this class', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
						<?php 
						$recordclass = $result->recordclass;
						?>
							<input type="radio" name="recordclass" <?php if( $recordclass == "true") echo ' checked'; ?> value = "true"><?php _e('Yes', 'wiziq'); ?>
							<input type="radio" name="recordclass" <?php if( $recordclass == "false") echo ' checked'; ?> value = "false"><?php _e('No', 'wiziq'); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Language of instruction', 'wiziq'); ?></th>
						<td>
							<div class = "wiziq_error" id = "class_attendee_limit_err" ></div>
							<select id="class_language" name="language">
							<?php
							$language = $wiziq_api_functions->getLanguages();
							foreach ($language as $key => $values) {
							?>
								<option <?php if( $result->language == $key ) echo ' selected'; ?> value = "<?php echo $key; ?>"><?php echo $values; ?></option>
							<?php 
							}
							?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<input class= "button button-primary wiziq-button" id = "add_class_wiziq" type = "Submit" name = "wiziq_edit_class" value="<?php _e('Schedule and Continue','wiziq'); ?>" /> 
			<a class= "button button-primary wiziq-button" id = "wiziq_cancel_course" href = "<?php echo $returnurl; ?>" ><?php _e( 'Cancel' , 'wiziq' ); ?></a>
		</form>
	<?php

	}// end edit class form
	
	
	/*
	* Function to display the class details
	* @since 1.0
	*/ 
	function wiziq_view_class_detail ($class_id) {
		$course_id = $_GET['course_id'];
		
		$res = $this->wiziq_get_class_by_id ($class_id);
		$join_class_url = WIZIQ_CLASS_MENU. "&action=class_detail&course_id=".$course_id."&class_id=".$class_id."&subact=join_class";
		?>
		<h2><?php _e('Class Detail','wiziq'); ?></h2>
		<?php		
			// display error if any while viewing the class
			global $myerror;
			if ( is_wp_error( $myerror ) ) {
					$add_error = $myerror->get_error_message('wiziq_add_attendee_error');
					if ( $add_error ) {
						echo $add_error;
					}
			} 
		?>
		<table class = "form-table" >
				<tbody>
					<tr>
						<th><?php _e('Class name', 'wiziq'); ?></th>
						<td>
						<strong>
							<?php echo $res->class_name;?>
						</strong>
						</td>
					</tr>
					<tr>
						<th><?php _e('Teacher', 'wiziq'); ?></th>
						<td>
						<?php 
						$creater_id = $res->created_by;
						$logged_user = get_current_user_id();
						if ($logged_user == $creater_id ) {
							_e( 'You' , 'wiziq' );
						}
						else {
							$user_info = get_userdata( $creater_id );
							echo $user_info->display_name;
						}
						?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Class status', 'wiziq'); ?></th>
						<td>
						<?php 
							$wiziq_util = new Wiziq_Util;
							if($res->status == 'upcoming'){
								$datetime_result = $wiziq_util->wiziq_get_datetime ( $res->class_time, $res->duration, $res->classtimezone );
								if( $datetime_result ){
									_e('Live Class', 'wiziq' );
								}
								else{
									$stat = ucfirst($res->status);
									_e( $stat, 'wiziq' );
								}
							}
							else
							{
								$stat = ucfirst($res->status);
								_e( $stat, 'wiziq' );
							}
						 ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Timing of class', 'wiziq'); ?></th>
						<td>
						<?php echo date( WIZIQ_DATE_TIME_FORMAT, strtotime($res->class_time)); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Time zone', 'wiziq'); ?></th>
						<td>
						<?php echo $res->classtimezone;?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Duration', 'wiziq'); ?><span class="description"> (<?php _e('in minutes', 'wiziq' ); ?>)</span></th>
						<td>
						<?php echo $res->duration;?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Language in classroom', 'wiziq'); ?></th>
						<td>
							<?php 
									$serialized_languages = get_option('wiziq_languages');
									$language = unserialize($serialized_languages);
									foreach ( $language as $key => $values) {
										if ( $key == $res->language ) {
											echo $values;
										}
									}
								?>
						</td>
					</tr>
					<tr>
						<th><?php _e('Recording opted', 'wiziq'); ?></th>
						<td>
						<?php 
						$record =  $res->recordclass;
						if ( $record == 'true' ) {
							_e( 'Yes', 'wiziq' );
						} else {
							_e( 'No', 'wiziq' );
						}
						?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php 
			if ( "upcoming" == $res->status) :
				$current_user = get_current_user_id();
				if ( $current_user == $res->created_by ) :
				?>
					<a class= "button button-primary wiziq-button" target = '_blank'  href = "<?php echo $res->response_presenter_url ; ?>" ><?php _e('Launch Class' , 'wiziq');?> </a>
				<?php else : ?>
					<a target = "_blank" class= "button button-primary wiziq-button" href = "<?php echo $join_class_url ; ?>" ><?php _e( 'Join Class', 'wiziq' );?></a>
				<?php endif; ?>
			<?php elseif ( "completed" == $res->status): ?>
				<?php if ( $res->download_recording && "true" == $res->recordclass ) : ?>
					<a class= "button button-primary wiziq-button" title = "<?php _e('Download' , 'wiziq' ); ?>" href = "<?php echo $res->download_recording; ?>"><?php _e('Download Recording', 'wiziq' ); ?></a>
				<?php endif; ?>
				<?php if ( "completed" == $res->status  &&  "true" == $res->recordclass ) : ?>
					<a class= "button button-primary wiziq-button" target= "_blank" title="<?php _e('View Recording', 'wiziq'); ?>" href="<?php echo $res-> response_recording_url;  ?>" ><?php _e('View Recording', 'wiziq'); ?></a>
				<?php endif; ?>
				<?php if ($res->attendence_report == "available" ) : ?>
					<a class= "button button-primary wiziq-button" title="<?php _e('View List Of Attendees', 'wiziq'); ?>" href="<?php echo WIZIQ_CLASS_MENU ?>&action=view_attendee&response_class_id=<?php echo $res->response_class_id; ?>&course_id=<?php echo $course_id; ?>" ><?php _e('Attendee Report','wiziq'); ?></a>
				<?php endif; ?>
			<?php endif; ?>
			<a class= "button button-primary wiziq-button" id = "wiziq_cancel_class" href = "<?php echo WIZIQ_CLASS_MENU; ?>&action=view_course&course_id=<?php echo $_REQUEST['course_id']; ?>" ><?php _e('Back To Classes', 'wiziq' ); ?></a>
			<?php 
	}// end function to display class detail
	
	
	/*
	 * Function to get recurring classes for listing
	 * pass 1 $pagination if pagination required , else pass 0
	 * @since 1.0
	 */ 
	function wiziq_get_class_recurring_list ( $master_id , $pagination ,$start, $limit ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		if ( $pagination ) {
			$qry = "select *, IF( NOW( ) BETWEEN class_time AND ADDTIME( class_time, SEC_TO_TIME( duration *60 ) )  and status='upcoming' , 'live class', status ) AS live_status from $wiziq_classes where master_id = '$master_id' order by id DESC LIMIT $start ,$limit" ;
			
		} else {
			$qry = "select * from $wiziq_classes where master_id = '$master_id' order by id DESC" ;
		}
		$wiziq_results = $wpdb->get_results( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}// end function to get recurring classes
	
	
	/*
	 * Function to get recurring classes for listing
	 * pass 1 $pagination if pagination required , else pass 0
	 * @since 1.0
	 */ 
	function wiziq_get_class_recurring_list_sorted ( $master_id , $pagination ,$start, $limit, $sort, $order ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		if ( $pagination ) {
			$qry = "select *, IF( NOW( ) BETWEEN class_time AND ADDTIME( class_time, SEC_TO_TIME( duration *60 ) )  and status='upcoming' , 'live class', status ) AS live_status from $wiziq_classes where master_id = '$master_id' order by $sort $order LIMIT $start ,$limit" ;
			
		} else {
			$qry = "select * from $wiziq_classes where master_id = '$master_id' order by id DESC" ;
		}
		$wiziq_results = $wpdb->get_results( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}// end function to get recurring classes
	
	
	/*
	 * function to refresh the recurring classes
	 * @since 1.0
	 */  
	function wiziq_refresh_recurring_classes ( $master_id ) {
		$wiziq_api_functions = new wiziq_api_functions;
		$wiziq_Util = new Wiziq_Util;
		$wiziq_class_res = $this->wiziq_get_class_recurring_list ( $master_id , 0 ,0, 0);
		$total_pages = !empty($wiziq_class_res)?count($wiziq_class_res):0 ;
		$limit = WIZIQ_PAGINATION_LIMIT;
		$adjacents = 3;
		$page = isset($_GET['pageno'])?$_GET['pageno']:'';
		if($page) 
			$start = ($page - 1) * $limit; 			//first item to display on this page
		else
			$start = 0;								//if no page var is given, set start to 0
		$targetpage = "?page=wiziq_class&action=view_course&master_class_id=$master_id&";
		$pagination =  $wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
		$countrow = 0;
		$wiziq_class_result  = $this->wiziq_get_class_recurring_list ( $master_id , '1' , $start , $limit  );
		if ( $wiziq_class_result )  {
			foreach ( $wiziq_class_result as $re ) {
				if ( $re->status == "upcoming" || $re->status == "completed" ) {
					$class_response_id = $re->response_class_id;
					$this->wiziq_live_get_data ( $class_response_id );
				}
			}
		}
		return true;
	}// end function to refresh recurring classes 
	
	/*
	 * Function to view list of recurring classes 
	 * @since 1.0
	 */ 
	 function view_recurring_classes ( $master_id , $course_id ) {
		$this->wiziq_refresh_recurring_classes ( $master_id ) ;
		$wiziq_api_functions = new wiziq_api_functions;
		$add_class_nonce = wp_create_nonce( 'add-class-' . $course_id );
		/*
		 * Pagination functionality
		 */ 
		$wiziq_Util = new Wiziq_Util;
		$wiziq_class_res = $this->wiziq_get_class_recurring_list ( $master_id , 0 ,0, 0);
		$total_pages = !empty($wiziq_class_res)?count($wiziq_class_res):0 ;
		$limit = WIZIQ_PAGINATION_LIMIT;
		//$limit = 1;
		$adjacents = 3;
		$page = isset($_GET['pageno'])?$_GET['pageno']:'';
		if($page) 
			$start = ($page - 1) * $limit; 			//first item to display on this page
		else
			$start = 0;								//if no page var is given, set start to 0
		$targetpage = "?page=wiziq_class&action=view_recurring_class&course_id=$course_id&master_class_id=$master_id&";
		$pagination =  $wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
		$countrow = 0;
		if ( $page ==  "" ) {
			$page = 1;
		}
		//get recurring classes and display
		$wiziq_class_result  = $this->wiziq_get_class_recurring_list ( $master_id , '1' , $start , $limit  );
		$pagetogo = $page;
		
		?>
		<h2><?php _e('WizIQ Classes', 'wiziq'); ?><a class = "add-new-h2"  href= "<?php echo WIZIQ_CLASS_MENU; ?>&action=add_class&course_id=<?php echo $course_id; ?>&wp_nonce=<?php echo $add_class_nonce; ?>" ><?php _e('Add New Class', 'wiziq'); ?></a><a class = "add-new-h2"  href= "<?php echo WIZIQ_CLASS_MENU; ?>&action=view_course&course_id=<?php echo $course_id; ?>" ><?php _e('Back To Classes', 'wiziq'); ?></a><a class = "add-new-h2" href = "<?php echo WIZIQ_COURSES_MENU;?>" ><?php _e('Back To Courses','wiziq');?></a></h2>
		<h3><?php _e('Recurring classes', 'wiziq'); ?></h3>
		<?php
		global $myerror;
			if ( is_wp_error( $myerror ) ) {
				$add_error = $myerror->get_error_message('wiziq_class_delete_error');
				if ( $add_error ) {
					echo $add_error;
				}
				$get_error = $myerror->get_error_message('wiziq_class_get_data_error');
				if ( $get_error ) {
					echo $get_error;
				}
			}
		?>
		<form method = "post" >
			<div class = "tablenav top">
				<div class="alignleft actions bulkactions">
					<select name="multiple_actions" id= "delete_action_class">
						<option value = "-1" ><span><?php _e('Bulk Actions', 'wiziq'); ?></span></option>
						<option value = "1"><span><?php _e('Delete', 'wiziq'); ?></span></option>
					</select>
					<input id="delete_mul_class" class="button action delete-classes" type="submit" value="<?php _e('Apply', 'wiziq'); ?>" name = "delete_classes">
				</div>
			</div>
			<table class= "wp-list-table widefat fixed pages" >
			<thead>
					<tr>
						<th class = "manage-column column-cb check-column" >
							<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All' , 'wiziq' ); ?></label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th id = "course_name" class = "manage-column sortable desc" >
								<span><?php _e('Class Title', 'wiziq'); ?></span>
						</th>
						<th id = "course_description" class = "manage-column" >
							<?php _e('Class Time', 'wiziq'); ?>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Presenter', 'wiziq'); ?>
						</th>
						
						<th class = "manage-column" >
							<?php _e('Status', 'wiziq'); ?>
							<?php if ( ! empty($wiziq_class_res) ) : ?>
							<a href="<?php echo WIZIQ_CLASS_MENU;?>&action=view_recurring_class&course_id=<?php echo $course_id ; ?>&master_class_id=<?php echo $master_id; ?>&pageno=<?php echo $pagetogo; ?>&refresh">
							<?php else : ?>
							<a href="javascript:;">
							<?php endif; ?>
							<img title= "<?php _e('Refresh', 'wiziq'); ?>" class = "classes-images" src= "<?php echo plugins_url( 'images/refresh20.png' , dirname(__FILE__) ) ; ?>" alt ="<?php _e( 'Refresh' , 'wiziq' ); ?>" />
							</a>
						</th>
						<th class = "manage-column" >
							<?php _e('Attendance Report', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('View Recording', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Download Recording', 'wiziq'); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class = "manage-column column-cb check-column" >
							<label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All','wiziq');?></label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th id = "course_name" class = "manage-column sortable desc" >
								<span><?php _e('Class Title', 'wiziq'); ?></span>
						</th>
						<th id = "course_description" class = "manage-column" >
							<?php _e('Class Time', 'wiziq'); ?>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Presenter', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('Status', 'wiziq'); ?>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Attendance Report', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('View Recording', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('Download Recording', 'wiziq'); ?>
						</th>
					</tr>
				</tfoot>
				<tbody>
					<?php 
					$user_permissions  = new Wiziq_User_Permissions;
					if ( $wiziq_class_result )  {
						foreach ( $wiziq_class_result as $res ) {
							$editnonce = wp_create_nonce( 'edit-class-' . $res->id );
							$recoding_status = $user_permissions->wiziq_downaload_recording_class_permission ( $course_id , $res->id );
							
							/*
							 * get status of classes and update the content
							 */ 
							
							
							$countrow++;
							if( "1" == $countrow) 
								{ 
									$row_class = "alternate iedit cclass";
								} 
								else 
								{
									$countrow = "0";
									$row_class = "iedit cclass";
								}
								?>
								<tr id = "cclass-<?php echo $res->id; ?>" class = "<?php echo $row_class; ?>" >
										<th class="check-column" scope="row">
											<label class="screen-reader-text" >Select <?php echo $res->class_name; ?></label>
											<input id="cb-select-<?php echo $res->id; ?>" type="checkbox" value="<?php echo $res->id; ?>" name = "class-checkbox[]" value= "<?php echo $res->id; ?>">
											<div class="locked-indicator"></div>
										</th>
										<td class = "post-title page-title column-title" >
											<strong>
												<a href="<?php echo WIZIQ_CLASS_MENU ?>&action=class_detail&class_id=<?php echo $res->id; ?>&course_id=<?php echo $course_id; ?>">
													<?php echo $res->class_name; ?>
												</a>
											</strong>
											<div class="row-actions">
											<span class="edit">
												<?php if ( "upcoming" == $res->live_status) : ?>
													<a title="<?php _e('Edit this class', 'wiziq'); ?>" href="<?php echo WIZIQ_CLASS_MENU ?>&action=edit_class&class_id=<?php echo $res->id; ?>&course_id=<?php echo $course_id; ?>&wp_nonce=<?php echo $editnonce; ?>"><?php _e('Edit','wiziq'); ?></a>
												<?php else : ?>
													<a title="<?php _e('Edit this class', 'wiziq'); ?>" href="javascript:;"><?php _e( 'Edit' , 'wiziq' ); ?></a>
												<?php endif; ?>
												|
											</span>
											<span class="trash">
												<a id = "<?php echo $res->id;?>" class="submitdelete-class" href="<?php echo WIZIQ_CLASS_MENU ?>&action=delete_class&class_id=<?php echo $res->id; ?>&course_id=<?php echo $course_id; ?>" title="<?php _e('Delete this class', 'wiziq'); ?>"><?php _e('Delete','wiziq'); ?></a>
											</span>
										</div>
										</td>
										<td> 
											<?php echo date( WIZIQ_DATE_TIME_FORMAT, strtotime($res->class_time)); ?>
										</td>
										<td> 
											<?php 
											$presenter_id = $res->created_by;
											$user_info = get_userdata( $presenter_id );
											echo $user_info->display_name;
											?>
										</td>
										<td> 
											<?php 
											$wiziq_util = new Wiziq_Util;
											if($res->live_status == 'upcoming'){
												$datetime_result = $wiziq_util->wiziq_get_datetime ( $res->class_time, $res->duration, $res->classtimezone );
												if( $datetime_result ){
													_e( 'Live Class', 'wiziq' );
												}
												else{
													$stat = ucfirst($res->live_status);
													_e( $stat, 'wiziq' );
												}		
											}
											else
											{
												$stat = ucfirst($res->live_status);
												_e( $stat, 'wiziq' );
											}	
											 ?>
										</td>
										<td>
										<?php if ($res->attendence_report == "available" ) : ?>
											<a title="<?php _e('View list of attendees', 'wiziq'); ?>" href="<?php echo WIZIQ_CLASS_MENU ?>&action=view_attendee&response_class_id=<?php echo $res->response_class_id; ?>&course_id=<?php echo $course_id; ?>" ><?php _e('View','wiziq'); ?></a>
										<?php else : ?>
											---
										<?php endif; ?>
									</td>
									<td>
										<?php if ( "completed" == $res->status  &&  "true" == $res->recordclass ) : ?>
											<a target= "_blank" title="<?php _e('View recording', 'wiziq'); ?>" href="<?php echo $res-> response_recording_url;  ?>" ><?php _e('View', 'wiziq'); ?></a>
										<?php else : ?>
										 ---
										<?php endif; ?>
									</td>
									<td>
										<?php if ($res->download_recording  ) : ?>
										 <a title = "<?php _e('Download' , 'wiziq' ); ?>"  href = "<?php echo $res->download_recording; ?>">Download</a>
										 <?php
										 elseif ( "upcoming" == $res->status  &&  "false" == $res->recordclass  && $recoding_status ) :
											_e('Recording not opted' , 'wiziq');	
										else : 
											echo '---';
										 ?>
										<?php endif; ?>
									</td>
							</tr>
							<?php
						}
					} else {
						echo '<tr id = "course" class = "alternate iedit" >';
							echo '<td colspan = "8">';
								echo __('No classes available for this course','wiziq');
							echo '</td>';
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
			<div class= "tablenav bottom">
			<?php if ( $wiziq_class_result )  { ?>
				<!-- display pagination -->
				<?php echo $pagination ; ?>
			<?php }; ?>
			</div>
			<br class="clear">
			<div class = "wiziq_hide" >
				<span id = "wiziq_are_u_sure" ><?php _e('Are you sure, you want to delete','wiziq');?></span>
				<span id = "wiziq_select_class" ><?php _e('Please select classes to delete','wiziq');?></span>
			</div>
		</form>
		<?php
	 }// end function to display recurring classes
	 
	 /*
	 * Function to add attenddes to a class
	 * @since 1.0
	 */ 
	function wiziq_add_attendees ( $course_id, $class_id ) {
			$wiziq_api_functions = new wiziq_frontend_api_functions;
			$result = $this->wiziq_get_class_by_id ( $class_id );
			$response_class_id = $result->response_class_id;
			$course_id = $result->courseid;
			$language = $result->language;
			if ( 'upcoming' == $result->status ) {
				$wiziq_api_functions->wiziq_addattendee( $course_id, $response_class_id, $language );
			} 
	} // end function to add attendess to a class
	 
	 /*
	  * Function to display list of attendees
	  * pass response class id and course id
	  * @since 1.0
	  */ 
	 function wiziq_display_attendees ( $response_class_cid , $course_id ) {
		//call api function and get list
		$wiziq_api_functions = new wiziq_api_functions;
		$result = $wiziq_api_functions->getAttendance_report ( $response_class_cid );
		 ?>
		 <h2><?php _e('Attendee Detail', 'wiziq'); ?><a class = "back add-new-h2"  href= "#" ><?php _e('Close', 'wiziq'); ?></a></h2>
			<table class= "wp-list-table widefat fixed pages" >
			<thead>
					<tr>
						<th class = "manage-column column-cb check-column" >
						</th>
						<th class = "manage-column sortable desc" >
							<span><?php _e('Attendee Name', 'wiziq'); ?></span>
						</th>
						<th class = "manage-column" >
							<?php _e('Entry Time', 'wiziq'); ?>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Exit Time', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Attended Time', 'wiziq'); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<?php
					?>
					<tr>
						<th class = "manage-column column-cb check-column" >
						</th>
						<th class = "manage-column sortable desc" >
								<span><?php _e('Attendee Name', 'wiziq'); ?></span>
						</th>
						<th class = "manage-column" >
							<?php _e('Entry Time', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('Exit Time', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('Attended Time', 'wiziq'); ?>
						</th>
					</tr>
				</tfoot>
				<tbody>
				<?php 
				$countrow = "0";
				if ( $result ) :
					foreach ($result as $val) 
					{	
							$countrow++;
							if( "1" == $countrow) 
							{ 
							?>
								<tr  class = "alternate iedit " >
								<?php
							} 
							else 
							{
								$countrow = "0";
								?>
								<tr class = "iedit " >
								<?php
							}
							?>
								<td></td>
								<td><?php echo $val->screen_name; ?></td>
								<td><?php echo date( WIZIQ_DATE_TIME_FORMAT, strtotime($val->entry_time)); ?></td>
								<td><?php echo date( WIZIQ_DATE_TIME_FORMAT, strtotime($val->exit_time)); ?></td>
								<td><?php echo $val->attended_minutes; ?></td>
							</tr>
							<?php
					}
				else :
					?>
					<tr>
						<td colspan="4"><?php _e( 'No record found' , 'wiziq' ); ?></td>
					</tr>
					<?php 
				endif;
				?>
				</tbody>
			</table>
		 <?php
	 } // end function to display attendee list
}
