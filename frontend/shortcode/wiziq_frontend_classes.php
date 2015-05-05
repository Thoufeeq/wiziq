<?php
	
	
	/*
	 * Frontend classes functions
	 * @since 1.0
	 */ 
	class Wiziq_Frontend_Classes { 
		
		/*
		 * Function for displaying classes on front end
		 * @since 1.0
		 */ 
		function wiziq_frontend_view_classes ( $course_id ) {
			global $wpdb;
			$Wiziq_Util = new Wiziq_Util;
			$user_permissions  = new Wiziq_User_Permissions;
			$wiziq_class_function = new Wiziq_Classes;
			$wiziq_api_functions = new wiziq_api_functions;
			$wiziq_frontend_courses = new Wiziq_Frontend_Courses;
			
			//url structure
			$courses_url = get_permalink();
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			
			/*
			 * Permissions
			 */ 
			
			$wiziq_class_permission = $user_permissions->wiziq_front_class_permission($course_id);
			$view_classes_permission = $user_permissions->wiziq_user_view_course_permission ( $course_id );
			$content_permission = $user_permissions->wiziq_upload_content_permission ( $course_id );
			
			/*
			 * End of permissions
			 */ 
			
			/*
			* Method for updating the database with recurring classes
			*/ 
			$recurring_classes = $wiziq_class_function->wiziq_get_recurring_classes ( $course_id );
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
				if ( $Wiziq_Util->wiziq_table_exist_check( $wiziq_classes ,$_GET['sort-by'] ) && ('desc' == $_GET['order-by'] || 'asc' == $_GET['order-by']) ) {
					$sortby = $_GET['sort-by'];
					$orderby = $_GET['order-by'];
					$refresh_url = $courses_url.$qvarsign.'caction=view_classes&course_id='.$course_id.'&sort-by='.$sortby.'&order-by='.$orderby.'&refresh';
				}else {
					$sortby = "id";
					$orderby = "desc";
					$refresh_url = $courses_url.$qvarsign.'caction=view_classes&course_id='.$course_id.'&refresh';
				}
			} else {
				$sortby = "id";
				$orderby = "desc";
				$refresh_url = $courses_url.$qvarsign.'caction=view_classes&course_id='.$course_id.'&refresh';
			}
			//create up or down image to display and ordering value
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
			else :
				$nameclass = "sorting-up";
				$ordering = "asc";
				$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
			endif;// end of sorting functionality
			
			
			/*
			 * Refresh status of classes on page load
			 */ 
			$wiziq_class_function->wiziq_refresh_classes ( $course_id, $sortby, $orderby ) ;
			
			
			
			$page = isset($_GET['pageno'])?$_GET['pageno']:'';
			if ( $page == '' )  {
				$page = 1;
			}
			$pagetogo = $page;
			
			/*
			 * Create urls
			 */ 
			$add_class_nonce = wp_create_nonce( 'add-class-' . $course_id );
			$refresh_url .= '&pageno='.$pagetogo;
			
			$courses_urls = $courses_url.$qvarsign.'action=courses';
			$add_curl = $courses_url.$qvarsign.'caction=add_class&course_id='.$course_id.'&wp_nonce='.$add_class_nonce;
			$content_url = $courses_url.$qvarsign.'ccaction=view_content&course_id='.$course_id;
			$wiziq_courses = new Wiziq_Courses;
			
			
			
			
			$wiziq_course = new Wiziq_Courses;
			$course_result = $wiziq_course->wiziq_get_single_courses ($course_id);
			$course_name = $course_result->fullname;
			
			
			/*
			 * Pagination functionality
			 */ 
			$result = $wiziq_class_function->wiziq_get_class ( $course_id , 0 , 0, 0 );
			
			$total_pages = !empty($result)?count($result):0 ;
			$limit = WIZIQ_PAGINATION_LIMIT;
			$adjacents = 3;
			$page = isset($_GET['pageno'])?$_GET['pageno']:'';
			if($page) 
				$start = ($page - 1) * $limit; 			//first item to display on this page
			else
				$start = 0;								//if no page var is given, set start to 0
			$targetpage = get_permalink().$qvarsign."caction=view_classes&course_id=".$course_id."&";
			$homepage = get_permalink().$qvarsign."caction=view_classes&course_id=".$course_id."&";
			if ( isset ($_GET['sort-by']) && isset ($_GET['order-by']) ) {
				$targetpage .= 'sort-by='.$_GET['sort-by'].'&order-by='.$_GET['order-by'].'&';
			}
			$pagination =  $Wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
			
			
			?>
			
		<div class="front_wiziq" id="front_wiziq" >
			<div class="wiziq_left" >
			</div>
			<div class="wiziq_right" >
				<ul class = "wiziq_front_menu">
					<li>
						<h3>
							<a href="<?php echo $courses_url; ?>" ><?php _e('Courses', 'wiziq'); ?></a>
						</h3>
					</li>
					<?php if ( $wiziq_class_permission ) : ?>
						<li>
							<h3>
								<a href="<?php echo $add_curl; ?>" ><?php _e('Add New Class', 'wiziq'); ?></a>
							</h3>	
						</li>
					<?php endif; ?>
					<?php if ( $content_permission ) : ?>
						<li>
							<h3>
								<a href="<?php echo $content_url; ?>" ><?php _e('Content', 'wiziq'); ?></a>
							</h3>
						</li>
					<?php endif; ?>
				</ul>
				<div class= "clearfix" ></div>
				<h4><?php _e( 'Course', 'wiziq' ); ?><?php echo " : ".$course_name ; ?></h4>
				<?php 
				/*
				 * Check for delete request 
				 */
				if ( isset ($_GET['class_id'] ) && isset ($_GET['subact']) ) {
					$this->wiziq_frontend_delete_single_class ( $_GET['class_id'], $course_id );
				}
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
				
				<table class="list_courses" id="list_courses" >
					<?php 
					echo '<tr>';
					echo '<th>';
					?>
					<a href = "<?php echo $homepage.'sort-by=class_name&order-by='.$ordering; ?>" title = "<?php echo $nametitle; ?>" 
					<?php
					echo '<span>'.__('Class Title','wiziq').'</span>';
					
					if (isset ( $_GET[ 'sort-by' ] ) && "class_name" == $_GET[ 'sort-by' ] ) : ?>
						<div class = "<?php echo $nameclass; ?>" ></div>
					<?php endif;
					echo '</th>';
					echo '<th>';
					?>
					<span><?php _e('Class Time','wiziq'); ?></span>
					<?php
					echo '</a></th>';
					echo '<th>';
					echo '<span>'.__('Presenter','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Status','wiziq');
					echo '<a href="'.$refresh_url.'">';
					?>
						<img title= "<?php _e('Refresh', 'wiziq'); ?>" class = "classes-images" src= "<?php echo WIZIQ_PLUGINURL_PATH.'images/refresh20.png'; ?>" alt ="<?php _e( 'Refresh' , 'wiziq' ); ?>" />
					<?php
					echo '</a></span></th>';  
					echo '<th>';
					echo '<span>'.__('Manage Class','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('Attendance Report','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('View Recording','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('Download Recording','wiziq').'</span>';
					echo '</th>';  
					echo '</tr>';
					if( $view_classes_permission && $result ){
						
						$wiziq_class_res = $wiziq_class_function->wiziq_get_class_sorted ( $course_id , '1' , $start , $limit , $sortby , $orderby  );;
						foreach($wiziq_class_res as $res){ 
							if (!$page) {
								$pageno = 0;
							}
							/*
							 * Individual urls and nonces
							 */ 
							$editnonce = wp_create_nonce( 'edit-class-' . $res->id );
							$deletenonce = wp_create_nonce( 'delete-class-' . $res->id );
							$attendee_nonce = wp_create_nonce( 'attend-class-' . $res->response_class_id );
							$edit_curl = $courses_url.$qvarsign.'caction=edit_class';   
							$recurring_url = $courses_url.$qvarsign.'caction=view_recurring_classes';   
							$attendee_url = $courses_url.$qvarsign.'caction=view_attendee&response_class_id='.$res->response_class_id.'&course_id='.$course_id;   
							$delete_url = $courses_url.$qvarsign.'caction=view_classes&course_id='.$course_id.'&class_id='.$res->id.'&subact=delete&wp_nonce='.$deletenonce;
							$class_detail = $courses_url.$qvarsign.'caction=view_front_class&class_id='.$res->id.'&course_id='.$course_id;   
							/*
							 * Pemissions
							 */ 
							$edit_class_permission = $user_permissions->wiziq_edit_class_permission( $course_id , $res->id ); 
							$delete_class_permission = $user_permissions->wiziq_delete_class_permission ( $course_id , $res->id );
							$view_attendee_list = $user_permissions->wiziq_view_attendee_class_permission ( $course_id , $res->id );
							$download_recording_permission = $user_permissions->wiziq_downaload_recording_class_permission ( $course_id , $res->id );
							$view_recording_permission = $user_permissions->wiziq_view_recording_class_permission ( $course_id , $res->id ); 
							
							//End permissions
							
							echo '<tr>';
							echo '<td>';
							echo '<a href="'. $class_detail .'">';
							echo $res->class_name;
							echo '</a>';
							echo '</td>';
							echo '<td>';
							echo date( WIZIQ_DATE_TIME_FORMAT, strtotime($res->class_time));
							echo '</td>';
							echo '<td>';
							$presenter_id = $res->created_by;
							$user_info = get_userdata( $presenter_id );
							echo $user_info->display_name;
							echo '</td>';
							echo '<td>';
							
								$wiziq_util = new Wiziq_Util;
								if($res->live_status == 'upcoming'){
									//call
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
							
							echo '</td>';  
							echo '<td>';
							if ( "upcoming" == $res->live_status) :
								if ( $wiziq_class_permission && $edit_class_permission ) :
								?>
									<a title="<?php _e('Edit this class', 'wiziq'); ?>" href="<?php echo $edit_curl ?>&class_id=<?php echo $res->id; ?>&course_id=<?php echo $course_id; ?>&wp_nonce=<?php echo $editnonce; ?>"><img title= "<?php _e('Edit this class', 'wiziq'); ?>" class = "classes-images" src= "<?php echo WIZIQ_PLUGINURL_PATH.'images/edit15.png' ; ?>" alt ="<?php _e( 'Edit' , 'wiziq' ); ?>" /></a>
								<?php
								endif;
							endif;
							
							if ( $wiziq_class_permission && $delete_class_permission ) :
								echo '<a title="'.__('Delete this vlass', 'wiziq').'" class= "submitdelete-class" href="'.$delete_url.'" ><img alt = "'.__( 'Delete' , 'wiziq' ).'" class = "classes-images" src="'.WIZIQ_PLUGINURL_PATH.'images/delete15.png" /></a>';
							endif;
								?>
								<?php if ( $res->is_recurring == "True")  :	?>
									<a title="<?php _e('View complete schedule', 'wiziq'); ?>" href="<?php echo $recurring_url; ?>&master_class_id=<?php echo $res->master_id; ?>&course_id=<?php echo $course_id; ?>" ><img title= "<?php _e('View complete schedule', 'wiziq'); ?>" class = "classes-images" src= "<?php echo WIZIQ_PLUGINURL_PATH.'images/list15.png' ; ?>" alt = "<?php _e('View' , 'wiziq' ); ?>" /></a>
								<?php endif;
							
							if ( $res->is_recurring == "False" && !$delete_class_permission ) :
								echo '---';
							endif;
							echo '</td>';  
							echo '<td>';
							
							if ($res->attendence_report == "available" ) :
								if ( $view_attendee_list):
									?>
									<a title="<?php _e('View list of attendees', 'wiziq'); ?>" href="<?php echo $attendee_url ?>&wp_nonce=<?php echo $attendee_nonce; ?>" ><?php _e( 'View' , 'wiziq' ) ; ?></a>
								<?php 
								else :
									echo '---';
								endif;
							else :
								echo '---';
							endif;
							echo '</td>';  
							echo '<td>';
							if ( "completed" == $res->status  &&  "true" == $res->recordclass ) : 
							
								if ( $view_recording_permission ) :
									?>
									<a target= "_blank" title="<?php _e('View Recording', 'wiziq'); ?>" href="<?php echo $res-> response_recording_url;  ?>" ><?php _e( 'View' , 'wiziq' ); ?></a>
									<?php 
								else :
									echo '---';
								endif;
							
							else :
							echo '----';
							endif;
							echo '</td>';  
							
							echo '<td>';
							if ($res->download_recording  ) :
							
								if ( $download_recording_permission ) :
									?>
									<a target= "_blank" title="<?php _e('Download recording', 'wiziq'); ?>" href="<?php echo $res-> download_recording;  ?>" ><?php _e( 'Download' , 'wiziq' ); ?></a>
									<?php
								else :
									echo '---';
								endif;
							elseif ( "upcoming" == $res->status  &&  "false" == $res->recordclass  && $download_recording_permission ) :
								_e( 'Recording not opted', 'wiziq' );	
							else :
								echo '----';
							endif;
							echo '</td>';  
							
							echo '</tr>';
						 } // end foreach
					} elseif ( ! $view_classes_permission ) {
						echo '<tr><td colspan="8">'.__('You are not enroll in this course, please contact your administrator','wiziq').'</td></tr>';
					}
					else {
						echo '<tr><td colspan="8">'.__('No class available','wiziq').'</td></tr>';
					} 
					echo '<tr>';
					echo '<th>';
					echo '<span>'.__('Class Title','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Class Time','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Presenter','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Status','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('Manage Class','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('Attendance Report','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('View Recording','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('Download Recording','wiziq').'</span>';
					echo '</th>';  
					echo '</tr>';
					?>
				</table>
				<!-- Display pagination -->
				<?php echo $pagination; ?>
			</div>
			<div class = "wiziq_hide" >
				<span id = "wiziq_are_u_sure" ><?php _e('Are you sure, you want to delete','wiziq');?></span>
				<span id = "wiziq_select_class" ><?php _e('Please select classes to delete','wiziq');?></span>
			</div>
		</div>
			<?php
		} // end view classes functions
		
		/*
		 * Function to display recurring classes
		 * Display recurring using the master id and course id
		 * @since 1.0
		 */ 
		function wiziq_frontend_view_recurring_classes ( $course_id, $master_id ) {
			$wiziq_courses = new Wiziq_Courses;
			$wiziq_class_function = new Wiziq_Classes;
			$user_permissions  = new Wiziq_User_Permissions;
			$wiziq_frontend_courses = new Wiziq_Frontend_Courses;
			$Wiziq_Util = new Wiziq_Util;
			/*
			 * Permissions
			 */ 
			
			$wiziq_class_permission = $user_permissions->wiziq_front_class_permission($course_id);
			$view_classes_permission = $user_permissions->wiziq_user_view_course_permission ($course_id);
			$wiziq_class_permission = $user_permissions->wiziq_front_class_permission($course_id);
			/*
			 * End of permissions
			 */ 
			 
			 //url structure
			$courses_url = get_permalink();
			
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			$page = isset($_GET['pageno'])?$_GET['pageno']:'';
			if ( $page == '' ) {
				$page = 0;
			}
			$pagetogo = $page;
			$add_class_nonce = wp_create_nonce( 'add-class-' . $course_id );
			$courses_urls = $courses_url.$qvarsign.'action=courses';
			$add_curl = $courses_url.$qvarsign.'caction=add_class&course_id='.$course_id.'&wp_nonce='.$add_class_nonce;
			$classes_url = $courses_url.$qvarsign.'caction=view_classes&course_id='.$course_id;
			$refresh_url = $courses_url.$qvarsign.'caction=view_recurring_classes&master_class_id='.$master_id.'&course_id='.$course_id."&subact=refresh&pageno=".$pagetogo;
			
			/*
			 * Function to refresh the status of classes
			 */ 
			$wiziq_class_function->wiziq_refresh_recurring_classes ( $master_id ) ;
			
			if ( isset ($_GET['subact']) && "refresh" == $_GET['subact']  ) {
				$wiziq_class_function->wiziq_refresh_classes($course_id , '0', '0');
			}
			
			$wiziq_course = new Wiziq_Courses;
			$course_result = $wiziq_course->wiziq_get_single_courses ($course_id);
			$course_name = $course_result->fullname;
			
			?>
			<div class="front_wiziq notlogin" id="front_wiziq" >
			<div class="wiziq_left" >
			</div>
			<div class="wiziq_right" >
				<ul class = "wiziq_front_menu">
					<li>
						<h3>
							<a href="<?php echo $courses_url; ?>" ><?php _e('Courses', 'wiziq'); ?></a>
						</h3>
					</li>
					<li>
						<h3>
							<a href="<?php echo $classes_url; ?>" ><?php _e('Classes', 'wiziq'); ?></a>
						</h3>
					</li>
					<?php if ( $wiziq_class_permission ) : ?>
						<li>
							<h3>
								<a href="<?php echo $add_curl; ?>" ><?php _e('Add New Class', 'wiziq'); ?></a>
							</h3>
						</li>
					<?php endif; ?>
				</ul>
				<div class = "clearfix" ></div>
				<h4><?php _e( 'Course' , 'wiziq' ); ?><?php echo " : ".$course_name ; ?></h>
				
				<?php 
				/*
				 * Check for delete request 
				 */
				if ( isset ($_GET['class_id'] ) && isset ($_GET['subact']) ) {
					$this->wiziq_frontend_delete_single_class ( $_GET['class_id'], $course_id );
				}
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
				
				<table class="list_courses" id="list_courses" >
					<?php 
					echo '<tr>';
					echo '<th>';
					echo '<span>'.__('Class Title','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Class Time','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Presenter','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Status','wiziq').'';
					echo '<a href="'.$refresh_url.'">';
					?>
					<img title= "<?php _e('Refresh', 'wiziq'); ?>" class = "classes-images" src= "<?php echo WIZIQ_PLUGINURL_PATH.'images/refresh20.png'; ?>" alt ="<?php _e( 'Refresh' , 'wiziq' ); ?>" />
					<?php
					echo '</a></span></th>';  
					echo '<th>';
					echo '<span>'.__('Manage Class','wiziq').'</span>';
					echo '</th>'; 
					echo '<th>';
					echo '<span>'.__('Attendance Report','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('View Recording','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('Download Recording','wiziq').'</span>';
					echo '</th>';  
					echo '</tr>';
					$result = $wiziq_class_function->wiziq_get_class_recurring_list ( $master_id , 0 , 0, 0 );
					if( $view_classes_permission && $result ){
						/*
						 * Pagination functionality
						 */ 
						
						$total_pages = !empty($result)?count($result):0 ;
						$limit = WIZIQ_PAGINATION_LIMIT;
						$adjacents = 3;
						if($page) 
							$start = ($page - 1) * $limit; 			//first item to display on this page
						else
							$start = 0;								//if no page var is given, set start to 0
						$targetpage = get_permalink().$qvarsign.'caction=view_recurring_classes&master_class_id='.$master_id.'&course_id='.$course_id."&";
						$pagination =  $Wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
						
						//Get recurring classes and display classes
						$wiziq_class_res = $wiziq_class_function->wiziq_get_class_recurring_list ( $master_id , 1 , $start, $limit );
						foreach($wiziq_class_res as $res){ 
							/*
							 * Individual urls and nonces
							 */ 
							$editnonce = wp_create_nonce( 'edit-class-' . $res->id );  
							$deletenonce = wp_create_nonce( 'delete-class-' . $res->id );
							$attendee_nonce = wp_create_nonce( 'attend-class-' . $res->response_class_id );
							$edit_curl = $courses_url.$qvarsign.'caction=edit_class';   
							$recurring_url = $courses_url.$qvarsign.'caction=view_recurring_classes';   
							$attendee_url = $courses_url.$qvarsign.'caction=view_attendee&response_class_id='.$res->response_class_id.'&course_id='.$course_id;   
							$delete_url = $courses_url.$qvarsign.'caction=view_recurring_classes&course_id='.$course_id.'&master_class_id='.$master_id.'&class_id='.$res->id.'&subact=delete&wp_nonce='.$deletenonce;
							$class_detail = $courses_url.$qvarsign.'caction=view_front_class&class_id='.$res->id.'&course_id='.$course_id;   
							/*
							 * Edit and delete permissin
							 */ 
							$edit_class_permission = $user_permissions->wiziq_edit_class_permission( $course_id , $res->id ); 
							$delete_class_permission = $user_permissions->wiziq_delete_class_permission ( $course_id , $res->id );
							$view_attendee_list = $user_permissions->wiziq_view_attendee_class_permission ( $course_id , $res->id );
							$download_recording_permission = $user_permissions->wiziq_downaload_recording_class_permission ( $course_id , $res->id );
							$view_recording_permission = $user_permissions->wiziq_view_recording_class_permission ( $course_id , $res->id ); 
							//End permissions
							echo '<tr>';
							echo '<td>';
							echo '<a href="'. $class_detail .'">';
							echo $res->class_name;
							echo '</a>';
							echo '</td>';
							echo '<td>';
							echo date( WIZIQ_DATE_TIME_FORMAT, strtotime($res->class_time));
							echo '</td>';
							echo '<td>';
							$presenter_id = $res->created_by;
							$user_info = get_userdata( $presenter_id );
							echo $user_info->display_name;
							echo '</td>';
							echo '<td>';
							
								$wiziq_util = new Wiziq_Util;
								if($res->live_status == 'upcoming'){
									$datetime_result = $wiziq_util->wiziq_get_datetime ( $res->class_time, $res->duration, $res->classtimezone );
									if( $datetime_result ){
										_e( 'Live Class' , 'wiziq' );
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
							
							echo '</td>';  
							echo '<td>';
								if ( "upcoming" == $res->live_status) :
								
									if ( $wiziq_class_permission && $edit_class_permission ) :
									?>
										<a title="<?php _e('Edit this class', 'wiziq'); ?>" href="<?php echo $edit_curl ?>&class_id=<?php echo $res->id; ?>&course_id=<?php echo $course_id; ?>&wp_nonce=<?php echo $editnonce; ?>"><img title= "<?php _e('Edit this class', 'wiziq'); ?>" class = "classes-images" src= "<?php echo WIZIQ_PLUGINURL_PATH.'images/edit15.png' ; ?>" alt ="<?php _e( 'Edit' , 'wiziq' ); ?>" /></a>
									<?php
									endif;
								endif;
								
								if ( $wiziq_class_permission && $delete_class_permission ) :
									echo '<a title="'.__('Delete this class', 'wiziq').'" class= "submitdelete-class" href="'.$delete_url.'" ><img class = "classes-images" src="'.WIZIQ_PLUGINURL_PATH.'images/delete15.png" /></a>';
								endif;
								
								if ( !$delete_class_permission ) :
									echo '---';
								endif;
							
							if ( !$delete_class_permission ) :
								echo '---';
							endif;
							
							echo '</td>';  
							echo '<td>';
							if ($res->attendence_report == "available" ) :
								if ( $view_attendee_list ) :
									?>
									<a title="<?php _e('View list of attendees', 'wiziq'); ?>" href="<?php echo $attendee_url; ?>&wp_nonce=<?php echo $attendee_nonce; ?>" ><?php _e( 'View' , 'wiziq' ); ?></a>
									<?php 
								else :
									echo '---';
								endif;
							else :
								echo '---';
							endif;
							echo '</td>';  
							echo '<td>';
							if ( "completed" == $res->status  &&  "true" == $res->recordclass ) : 
							
								if ( $view_recording_permission ) :
								?>
									<a target= "_blank" title="<?php _e('View recording', 'wiziq'); ?>" href="<?php echo $res-> response_recording_url;  ?>" ><?php _e( 'View' , 'wiziq' ); ?></a>
								<?php 
								else :
									echo '---';
								endif;
							
							else :
							echo '----';
							endif;
							echo '</td>';  
							echo '<td>';
							if ( $res->download_recording  ) :
							
								if ( $download_recording_permission ) :
								?>
									<a target= "_blank" title="<?php _e('View recording', 'wiziq'); ?>" href="<?php echo $res-> download_recording;  ?>" ><?php _e( 'Download' , 'wiziq' ); ?></a>
								<?php
								else :
									echo '---';
								endif;
							elseif ( "upcoming" == $res->status  &&  "false" == $res->recordclass  && $download_recording_permission ) :
								_e('Recording not opted' , 'wiziq');
							else :
								echo '---';
							endif;
							echo '</td>';  
							echo '</tr>';
					 }
					} // end foreach
					//Check for permission
					elseif (! $view_classes_permission ) {
						echo '<tr><td colspan="8">'.__('You do not have permissions to access this class','wiziq').'</td></tr>';
					} else {
						echo '<tr><td colspan="8">'.__('No class available','wiziq').'</td></tr>';
					} // else no course available
					echo '<tr>';
					echo '<th>';
					echo '<span>'.__('Class Title','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Class Time','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Presenter','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Status','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('Manage Class','wiziq').'</span>';
					echo '</th>'; 
					echo '<th>';
					echo '<span>'.__('Attendance Report','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('View Recording','wiziq').'</span>';
					echo '</th>';  
					echo '<th>';
					echo '<span>'.__('Download Recording','wiziq').'</span>';
					echo '</th>';  
					echo '</tr>';
					?>
				</table>
				<!-- Display pagination -->
				<?php echo $pagination; ?>
			</div>
			</div>
			<div class = "wiziq_hide" >
				<span id = "wiziq_are_u_sure" ><?php _e('Are you sure, you want To delete','wiziq');?></span>
				<span id = "wiziq_select_class" ><?php _e('Please select classes to delete','wiziq');?></span>
			</div>
			
			<?php
		}
		
		/*
		 * Function to display attendees on front end
		 * Pass response class id
		 * @since 1.0
		 */ 
		function wiziq_frontend_view_attendee ( $response_class_id ,$course_id ) {
			//Url structure
			
			$Wiziq_Util = new Wiziq_Util;
			$wiziq_api_functions = new wiziq_api_functions;
			
			//url structure
			$courses_url = get_permalink();
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			
			$courses_urls = $courses_url.$qvarsign.'action=courses';
			$classes_url = $courses_url.$qvarsign.'caction=view_classes&course_id='.$course_id;
			
			//call to api method
			$result = $wiziq_api_functions->getAttendance_report ( $response_class_id );
			if ( ! wp_verify_nonce( $_GET['wp_nonce'] , 'attend-class-'.$response_class_id  ) ) {
				?>
				<script>
					window.location = "<?php echo $courses_url; ?>";
				</script>
				<?php
			}
			?>
			<div class="front_wiziq" id="front_wiziq" >
				<div class="wiziq_left" >
				</div>
				<div class="wiziq_right" >
					<ul class = "wiziq_front_menu">
						<li>
							<h3>
								<a href="<?php echo $courses_url; ?>" ><?php _e('Courses', 'wiziq'); ?></a>
							</h3>
						</li>
						<li>
							<h3>
								<a href="<?php echo $classes_url; ?>" ><?php _e('Classes', 'wiziq'); ?></a>
							</h3>	
						</li>
					</ul>
				<div class= "clearfix"></div>
				<h4><?php _e( 'Attendee Report' , 'wiziq' );?></h4>
				<table class="list_courses" id="list_courses" >
						<tr>
							<th>
								<span><?php _e('Attendee Name', 'wiziq'); ?></span>
							</th>
							<th>
								<?php _e('Entry Time', 'wiziq'); ?>
							</th>
							<th>
								<?php _e('Exit Time', 'wiziq'); ?>
							</th>
							<th>
								<?php _e('Attended Time', 'wiziq'); ?>
							</th>
						</tr>
						<?php
						?>
						<tr>
							<th >
									<span><?php _e('Attendee Name', 'wiziq'); ?></span>
							</th>
							<th>
								<?php _e('Entry Time', 'wiziq'); ?>
							</th>
							<th>
								<?php _e('Exit Time', 'wiziq'); ?>
							</th>
							<th>
								<?php _e('Attended Time', 'wiziq'); ?>
							</th>
						</tr>
						<?php 
						$countrow = "0";
						if ( $result ) {
							foreach ($result as $val) 
							{	
								?>
									<tr>
										<td><?php echo $val->screen_name; ?></td>
										<td><?php echo date( WIZIQ_DATE_TIME_FORMAT, strtotime($val->entry_time)); ?></td>
										<td><?php echo date( WIZIQ_DATE_TIME_FORMAT, strtotime($val->exit_time)); ?></td>
										<td><?php echo $val->attended_minutes; ?></td>
									</tr>
									<?php
							}
						}
						else
						{
							?>
							<tr>
								<td colspan = "4" ><?php _e( 'No record found' , 'wiziq' ); ?></td>
							</tr>
							<?php
						}
						?>
				</table>
				</div>
			</div>
		 <?php
			
		}//end display attendde function
		
		/*
		 * Function to delete a class
		 * @since 1.0
		 */ 
		 
		function wiziq_frontend_delete_single_class ( $class_id, $course_id ) {
			$wiziq_api_functions = new wiziq_frontend_api_functions;
			global $wpdb;
			$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
			$qry = "select status from $wiziq_classes where id = '$class_id'";
			$status = $wpdb->get_row($qry);
			if ( $status ){
				$statusres = $status->status;
				if ( "expired" == $statusres || "cancelled" == $statusres || "completed" == $statusres )
				{			
					$deletqry = $wpdb->query("delete from $wiziq_classes where id = '$class_id'");
					$returnmsg = "<div class='wiziq_front_error'>Deleted successfully</div>";
				}
				else
				{
					$return = $wiziq_api_functions->wiziq_cancel( $class_id );
					$returnmsg = '<div class="wiziq_front_success" >'.$return.'</div>';
				}
				return $returnmsg;
			}
			
		}
		
		/*
		 * Function for not logged in users view class
		 * @since 1.0
		 */ 
		function wiziq_frontend_login_to_view ( ) {
			
			$Wiziq_Util = new Wiziq_Util;
			$wiziq_frontend_courses = new Wiziq_Frontend_Courses;
			$wiziq_courses = new Wiziq_Courses;
			$course_id = $_REQUEST['course_id'];
			
			//url structure
			$courses_url = get_permalink();
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			
			$courses_urls = $courses_url.$qvarsign.'action=courses';
			$add_curl = $courses_url.$qvarsign.'action=addcourse';
			$edit_curl = $courses_url.$qvarsign.'action=editcourse';
			$delete_curl = $courses_url.$qvarsign.'action=deletecourse';
			
			$course_result = $wiziq_courses->wiziq_get_single_courses ($course_id);
			$course_name = $course_result->fullname;
			?>
			<div class="front_wiziq notlogin" id="front_wiziq" >
				<div class="wiziq_left" >
				</div>
				<div class="wiziq_right" >
					<ul class = "wiziq_front_menu">
						<li>
							<h3>
								<a href="<?php echo $courses_url; ?>" ><?php _e('Courses', 'wiziq'); ?></a>
							</h3>
						</li>
					</ul>
					<div class= "clearfix"></div>
					<h4><?php _e( 'Course' , 'wiziq' ); ?><?php echo " : ".$course_name ; ?></h4>
						<?php 
						_e('Please login to view classes ', 'wiziq');
						?>
				</div>
			</div>
			<?php
		}// end not logged in users view class
		
		/*
		 * Edit class form for frontend
		 * parameters class id and course id
		 * @since 1.0
		 */ 
		function wiziq_frontend_edit_class_form (  $nonce, $class_id , $course_id ) {
			$courses_url = get_permalink();
			$Wiziq_Util = new Wiziq_Util;
			
			//url structure
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			$returnurl = $courses_url.$qvarsign."caction=view_classes&course_id=".$course_id;
			if ( ! wp_verify_nonce( $nonce , 'edit-class-'.$class_id  ) ) {
			?>
				<script>
					window.location = "<?php echo $returnurl; ?>";
				</script>
			<?php
			}else {
				global $wpdb;
				
				$classes_url = $courses_url.$qvarsign.'caction=view_classes&course_id='.$course_id;
				$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
				$wiziq_class = new Wiziq_Classes;
				$wiziq_api_functions = new wiziq_api_functions;
				$result = $wiziq_class->wiziq_get_class_by_id ( $class_id );
				
				}
				?>
				<div class="front_wiziq" id="front_wiziq" >
					<div class="wiziq_left" ></div>
					<div class="wiziq_right" >
						<ul class = "wiziq_front_menu">
							<li>
								<h3>
									<a href="<?php echo $courses_url; ?>" ><?php _e('Courses', 'wiziq'); ?></a>
								</h3>
							</li>
							<li>
								<h3>
									<a href="<?php echo $classes_url; ?>" ><?php _e('Classes', 'wiziq'); ?></a>
								</h3>
							</li>
						</ul>
					</div>
					<div class = "clearfix" ></div>
				</div>
				<div class = "clearfix" ></div>
				<h4><?php _e('Edit Class','wiziq'); ?></h4>
				<div class="frontaddform" >
				<?php 
				//Display errors if any while editing the class
				global $myerror;
				if ( is_wp_error( $myerror ) ) {
						$add_error = $myerror->get_error_message('wiziq_edit_error');
						if ( $add_error ) {
							echo $add_error;
						}
				} 
				?>
				
					<form method = "post" id= "add_class_form" >
						<table>
							<tr>
								<th>
									<?php _e('Class title', 'wiziq'); ?><span class="required">*</span>
								</th>
								<td>
									<input value = "<?php echo $result->class_name; ?>" maxlength= "70" type = "text" class = "regular-text" id = "class_name" name = "class_name" />
									<div class = "wiziq_error" id = "class_name_err" ></div>
								</td>
							</tr>
							<tr>
								<th>
									<?php _e('Select date', 'wiziq'); ?><span class="required">*</span>
								</th>
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
								<th>
									<?php _e('Class time', 'wiziq'); ?><span class="required">*</span>
								</th>
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
										<input type="checkbox" id = "class_schedule" name="schedule_now" value="1"><?php _e( 'Schedule right now', 'wiziq' ); ?>
									</select>
								</td>
							</tr>
							<tr>
								<th>
									<?php _e('Class duration (in minutes )', 'wiziq'); ?><span class="required">*</span>
								</th>
								<td>
									<input value = "<?php echo $result->duration; ?>" type = "text" class = "regular-text" id = "class_duration" name = "duration" />
									<div id= "class_duration_ms" ><?php _e( 'Minimum 30 min and maximum 300 min.' , 'wiziq' );?> </div>
									<div class = "wiziq_error" id = "class_duration_err" ></div>
								</td>
							</tr>
							<tr>
								<th>
									<?php _e('Time zone', 'wiziq'); ?>
								</th>
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
								<th>
									<?php _e('Attendee limit in a class', 'wiziq'); ?>
								</th>
								<td>
									<input value= "<?php echo $result->attendee_limit; ?>" maxlength= "10" type = "text" class = "regular-text" id = "class_attendee_limit" name = "attendee_limit" />
									<div class = "wiziq_error" id = "class_attendee_limit_err" ></div>
								</td>
							</tr>
							<tr>
								<th>
									<?php _e('Record this class', 'wiziq'); ?><span class="required">*</span>
								</th>
								<td>
									<?php 
										$recordclass = $result->recordclass;
									?>
									<input type="radio" name="recordclass" <?php if( $recordclass == "true") echo ' checked'; ?> value = "true"><?php _e('Yes', 'wiziq'); ?>
									<input type="radio" name="recordclass" <?php if( $recordclass == "false") echo ' checked'; ?> value = "false"><?php _e('No', 'wiziq'); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php _e('Language of instruction', 'wiziq'); ?>
								</th>
								<td>
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
						</table>
						
						<input class= "button button-primary" id = "add_class_wiziq" type = "Submit" name = "wiziq_edit_class" value="<?php _e('Schedule and Continue','wiziq');?>" /> 
						<a class= "button button-primary" id = "wiziq_cancel_course" href = "<?php echo $returnurl; ?>" ><?php _e('Cancel','wiziq');?></a>
					</form>
					<div class= "wiziq_hide" id = "class_name_wrong" ><?php _e("Class title can't be empty.", 'wiziq'); ?></div>
					<div class= "wiziq_hide" id = "class_durantion_wrong" ><?php _e('Please enter duration between 30 to 300 minutes.', 'wiziq'); ?></div>
					<div class= "wiziq_hide" id = "class_start_date_wrong" ><?php _e('Start date required.', 'wiziq'); ?></div>
					<div class= "wiziq_hide" id = "class_end_date_wrong" ><?php _e('Please enter end date.', 'wiziq'); ?></div>
					<div class= "wiziq_hide" id = "class_end_date_occurance_wrong" ><?php _e('Please enter number of classes.', 'wiziq'); ?></div>
					<div class= "wiziq_hide" id = "class_end_occurance_wrong" ><?php _e('You can add upto 60 classes.', 'wiziq'); ?></div>
					<div class= "wiziq_hide" id = "class_week_days_error" ><?php _e('Please select week days.', 'wiziq'); ?></div>
					<div class= "wiziq_hide" id = "class_attendee_error" ><?php _e(' Please enter users between 1 and 1000.', 'wiziq'); ?></div>
					<div class= "wiziq_hide" id = "class_attendee_number_error" ><?php _e('Please enter number.', 'wiziq'); ?></div>
					<div class= "wiziq_hide" id = "wiziq_class_repeat_error" ><?php _e('Please select when this class repeats.', 'wiziq'); ?></div>
				</div>
				<?php
			} //end edit class form function 
	
		
		/*
		* Function to display the class details
		* Pass class id
		* @since 1.0
		*/ 
		function wiziq_frontend_view_class_detail ( $class_id , $course_id ) {
			$wiziq_class = new Wiziq_Classes;
			$Wiziq_Util = new Wiziq_Util;
			$user_permissions  = new Wiziq_User_Permissions;
			
			//url structure
			
			$courses_url = get_permalink();
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			$cancel_url = $courses_url. $qvarsign. 'caction=view_classes&course_id='.$course_id ; 

			$course_id = $_GET['course_id'];
			$res = $wiziq_class->wiziq_get_class_by_id ( $class_id );
			$res->response_class_id;
			$attendee_nonce = wp_create_nonce( 'attend-class-' . $res->response_class_id );
			?>
			<h2><?php _e('Class Detail','wiziq'); ?></h2>
			<?php		
			//display if any errors
			global $myerror;
			if ( is_wp_error( $myerror ) ) {
					$add_error = $myerror->get_error_message('wiziq_add_attendee_error');
					if ( $add_error ) {
						echo $add_error;
					}
			} 
		?>
			<table class = "form-table" >
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
								_e('You' , 'wiziq' );
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
										_e ( 'Live Class' , 'wiziq' );
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
							<th><?php _e('Duration (in Minutes)', 'wiziq'); ?></th>
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
								_e('Yes', 'wiziq' );
							} else {
								_e('No', 'wiziq' );
							}
							?>
							</td>
						</tr>
				</table>
				<?php 
				/*
				 * Permissions functionality
				 */ 
				
				$view_attendee_list = $user_permissions->wiziq_view_attendee_class_permission ( $course_id , $class_id );
				$view_recoding_status = $user_permissions->wiziq_view_recording_class_permission ( $course_id , $class_id );
				$download_recoding_status = $user_permissions->wiziq_downaload_recording_class_permission ( $course_id , $class_id );
				$join_class_url = $courses_url.$qvarsign. "caction=view_front_class&course_id=".$course_id."&class_id=".$class_id."&subact=join_class";
				$attendee_url = $courses_url.$qvarsign.'caction=view_attendee&response_class_id='.$res->response_class_id.'&course_id='.$course_id."&wp_nonce=".$attendee_nonce; 
				if ( "upcoming" == $res->status) :
					$current_user = get_current_user_id();
					if ( $current_user == $res->created_by ) :
					?>
						<a class= "button button-primary" target = '_blank'  href = "<?php echo $res->response_presenter_url ; ?>" ><?php _e('Launch Class' , 'wiziq');?> </a>
					<?php else : ?>
							<a class= "button button-primary" target="_blank" href = "<?php echo $join_class_url ; ?>" ><?php _e( 'Join Class', 'wiziq' );?></a>
						<?php endif; ?>
				<?php elseif ( "completed" == $res->status): ?>
					<?php if ( $res->download_recording && "true" == $res->recordclass && $download_recoding_status ) : ?>
						<a class= "button button-primary" title = "<?php _e('Download' , 'wiziq' ); ?>" href = "<?php echo $res->download_recording; ?>"><?php _e('Download Recording', 'wiziq' ); ?></a>
					<?php endif; ?>
					<?php if ( "completed" == $res->status  &&  "true" == $res->recordclass && $view_recoding_status ) : ?>
						<a class= "button button-primary" target= "_blank" title="<?php _e('View Recording', 'wiziq'); ?>" href="<?php echo $res-> response_recording_url;  ?>" ><?php _e('View Recording', 'wiziq'); ?></a>
					<?php endif; ?>
					<?php if ($res->attendence_report == "available" && $view_attendee_list) : ?>
						<a class= "button button-primary" title="<?php _e('View List Of Attendees', 'wiziq'); ?>" href="<?php echo $attendee_url ?>" ><?php _e('Attendee Report','wiziq'); ?></a>
					<?php endif; ?>
				<?php endif; ?>
				<a class= "button button-primary" id = "wiziq_cancel_class" href = "<?php echo $cancel_url; ?>" ><?php _e('Back To Classes', 'wiziq' ); ?></a>
				<?php 
		}
	
		/*
		 * Function of add class form on front end
		 * @since 1.0
		 */ 
		function wiziq_add_classes_form_front ( $nonce, $course_id ) {
			//url structure
			$wiziq_class = new Wiziq_Classes;
			$wiziq_api_functions = new wiziq_api_functions;
			$Wiziq_Util = new Wiziq_Util;
			
			//url structure
			$courses_url = get_permalink();
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			$returnurl = $courses_url. $qvarsign. 'caction=view_classes&course_id='.$course_id ; 
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
				$wiziq_teachers = new Wiziq_Teachers;
				$current_user = get_current_user_id();
				
				if ( isset ($_POST ['add_class_wiziq'] ) ) {
					$classcon = $_POST;
				} else {
					$classcon = 0;
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
				<div class="front_wiziq userlogin " id="front_wiziq" >
			<div class="wiziq_left" >
			</div>
			<div class="wiziq_right" >
				<ul class = "wiziq_front_menu">
					<li>
						<h3>
							<a href="<?php echo $courses_url; ?>" ><?php _e('Courses', 'wiziq'); ?></a>
						</h3>
					</li>
					<li>
						<h3>
							<a href="<?php echo $returnurl; ?>" ><?php _e('Classes', 'wiziq'); ?></a>
						</h3>
					</li>
				</ul>
				<div class = "clearfix" ></div>
				<h4><?php _e('Add New Class','wiziq'); ?></h4>
				<?php 
				
				$chec_teacher = $wiziq_class->wiziq_check_teacher_course( $course_id );
				if ( empty ( $chec_teacher ) ) {
					echo __( 'Please enroll/add teacher first to schedule the class.' , 'wiziq' );
					return;
				}
				
				//display errors while adding a class if any
				global $myerror;
				if ( is_wp_error( $myerror ) ) {
						$add_error = $myerror->get_error_message('wiziq_add_error');
						if ( $add_error ) {
							echo $add_error;
						}
				} 
				?>
				<div id = 'wiziq-add-front-error' >
				</div>
				<form method = "post" id= "add_class_form" name= "add_class_form" >
					<table class = "form-table" >
						<tr>
							<th><?php _e('Class title', 'wiziq'); ?><span class="required">*</span></th>
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
						<?php if (current_user_can('manage_options')) { ?>
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
						<?php } else {
							?>
							<input type = "hidden" name = "wiziq_teacher" value = "0" />
							<?php
						}
						
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
									<?php _e('Class schedule', 'wiziq'); ?><span class="required">*</span>
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
										<option value="0" <?php if ($repeat == "0") { echo  " selected "; } ?>  ><?php _e( 'Select when class repeats' , 'wiziq' ) ;?></option>
										<option value="1" <?php if ($repeat == "1") { echo  " selected "; } ?> ><?php _e( 'Daily (all 7 Days)' , 'wiziq' ); ?></option>
										<option value="2" <?php if ($repeat == "2") { echo  " selected "; } ?> ><?php _e( '6 Days (Mon-Sat)' , 'wiziq' ); ?></option>
										<option value="3" <?php if ($repeat == "3") { echo  " selected "; } ?> ><?php _e( '5 Days (Mon-Fri)' , 'wiziq' );?></option>
										<option value="4" <?php if ($repeat == "4") { echo  " selected "; } ?> ><?php _e( 'Weekly' , 'wiziq' ); ?></option>
										<option value="5" <?php if ($repeat == "5") { echo  " selected "; } ?> ><?php _e( 'Monthly' , 'wiziq' ); ?></option>
									</select>
								</div>
								<div class = "wiziq_error" id = "wiziq_class_repeat_err" ></div>
							</td>
						</tr>
						<tr>
							<th><?php _e('Select date', 'wiziq'); ?><span class="required">*</span></th>
							<td>
								<input type = "text" class = "regular-text" id = "class_start_date" name="class_time" value = "<?php if (isset($_POST['class_time'])) echo $_POST['class_time']; ?>" /> 
								<div class = "wiziq_error" id = "class_start_date_err" ></div>
							</td>
						</tr>
						</tr>
						<tr>
							<th><?php _e('Class time', 'wiziq'); ?><span class="required">*</span></th>
							<td>
								<?php _e('Hours', 'wiziq'); ?>
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
								<?php _e('Minutes', 'wiziq'); ?>
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
								<input type="checkbox" id = "class_schedule" name="schedule_now" value="1"><?php _e( 'Schedule right now', 'wiziq' ); ?><br>
							</td>
						</tr>
						<tr>
							<th><?php _e('Class duration (in minutes )', 'wiziq'); ?><span class="required">*</span></th>
							<td>
								<input type = "text" class = "regular-text" id = "class_duration" name = "duration" value = "<?php if (isset($_POST['duration'])) { echo $_POST['duration']; } else { echo '60'; } ?>" />
								<div id= "class_duration_ms" ><?php _e( 'Minimum 30 min and maximum 300 min.' , 'wiziq' );?> </div>
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
								<th><?php _e('Repeat every week', 'wiziq'); ?><span class="required">*</span></th>
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
										<li><input class= "week_days_check" type="checkbox" name="days_of_week[]" value="tuesday" <?php if ( $classcon && $tue) echo "checked"; ?>><span class = "weekly_class" >T</span></li>
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
										<option value="Last" <?php if ($monthday == "Last") { echo  " selected "; } ?> ><?php _e( 'Last' , 'wiziq' ); ?></option>
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
										<option value="monday" >Mon</option>
										<option value="tuesday" <?php if ($monthday_day == "tuesday") { echo  " selected "; } ?> >Tue</option>
										<option value="wednesday" <?php if ($monthday_day == "wednesday") { echo  " selected "; } ?> >Wed</option>
										<option value="thursday" <?php if ($monthday_day == "thursday") { echo  " selected "; } ?> >Thr</option>
										<option value="friday" <?php if ($monthday_day == "friday") { echo  " selected "; } ?> >Fri</option>
										<option value="saturday" <?php if ($monthday_day == "saturday") { echo  " selected "; } ?> >Sat</option>
										<option value="sunday" <?php if ($monthday_day == "sunday") { echo  " selected "; } ?> >Sun</option>
									</select><?php _e('of every month','wiziq');?>
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
										</select><?php _e('of every month','wiziq');?>
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
							<th><?php _e('Record this class', 'wiziq'); ?><span class="required">*</span></th>
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
								<select id="class_language" name="language">
								<?php
								$language = $wiziq_api_functions->getLanguages();
								foreach ($language as $key => $values) {
								?>
									<option value = "<?php echo $key; ?>"  <?php if(isset ($_POST['language'] ) && $_POST['language'] == $key ) echo ' selected'; elseif ('en-US' == $key ) { echo ' selected' ; } ?> ><?php echo $values; ?></option>
								<?php 
								}
								?>
								</select>
							</td>
						</tr>
				</table>
				<input type= "hidden" name = "courseid" value = "<?php echo $_REQUEST['course_id']; ?>" />
				<input class= "button button-primary" id = "add_class_wiziq" type = "Submit" name = "add_class_wiziq" value="<?php _e('Schedule and Continue','wiziq');?>" /> 
				<a class= "button button-primary" id = "wiziq_cancel_class" href = "<?php echo $returnurl; ?>" ><?php _e( 'Cancel' , 'wiziq' ); ?></a>
			</form>
			<div class= "wiziq_hide" id = "class_name_wrong" ><?php _e("Class title can't be empty.", 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_durantion_wrong" ><?php _e('Please enter duration between 30 to 300 minutes.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_start_date_wrong" ><?php _e('Start date required.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_end_date_wrong" ><?php _e('Please enter end date.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_end_date_occurance_wrong" ><?php _e('Please enter number of classes.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_end_occurance_wrong" ><?php _e('You can add upto 60 classes.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_week_days_error" ><?php _e('Please select week days.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_attendee_error" ><?php _e(' Please enter users between 1 and 1000.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "class_attendee_number_error" ><?php _e('Please enter number.', 'wiziq'); ?></div>
			<div class= "wiziq_hide" id = "wiziq_class_repeat_error" ><?php _e('Please select when this class repeats.', 'wiziq'); ?></div>
			</div>
			<?php
			}
		}// end add class from function 
		
		/*
		 *Function to add a class on front end 
		 * pass post array
		 * @since 1.0
		 */
		 function wiziq_frontend_add_class ($data) {
			 $Wiziq_Util = new Wiziq_Util;
			 $wiziq_api_functions = new wiziq_frontend_api_functions;
			
			global $current_user;
			//url structure
			
			$courses_url = get_permalink();
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			
			$teacher_id = $data['wiziq_teacher'];
			if ( $teacher_id  == 0 ) {
				$teacher_data = $current_user->ID;
			} else {
				$teacher_data = $teacher_id;
			}
			$XMLReturn = $wiziq_api_functions->addLiveClass_Frontend($data);
			if (!empty($XMLReturn)) {
				try {
					$objDOM = new DOMDocument();
					$objDOM->loadXML($XMLReturn);
				} catch (Exception $e) {
					$api_error = $e->getMessage();
				}
				$status = $objDOM->getElementsByTagName("rsp")->item(0);
				$attribNode = $status->getAttribute("status");
			}
			$abc = new SimpleXMLElement($XMLReturn);
			
			if ($attribNode == 'ok') 
			{
				
				global $wpdb;
				global $current_user;
				$response['livestatus'] = 'ok';
				$methodTag = $objDOM->getElementsByTagName("method");
				$response['response_method'] = $methodTag->item(0)->nodeValue;
				if (isset($data['schedule_now'])) {
					$requestParameters["start_time"] =  date('m/d/Y H:i:s');     //date('m/d/Y H:i:s', strtotime($data['class_time']));
				} else {
					$requestParameters["start_time"] = date('m/d/Y', strtotime($data['class_time'])) . ' ' . $data['hours'] . ':' . $data['minutes'];
				}
				$response['classtime'] = $requestParameters["start_time"];
				//if recurring class
				if ($response['response_method'] == 'create_recurring') {
					$return_url = $courses_url. $qvarsign. 'caction=view_classes&course_id='.$_GET['course_id'] ;
					
					$presenter_urlTag = $objDOM->getElementsByTagName("class_master_id");
					$response['master_id']  = $presenter_urlTag->item(0)->nodeValue;
					$data['master_id']      = $response['master_id'];

					$class_idTag = $objDOM->getElementsByTagName("class_id");
					$response['response_class_id'] = $class_idTag->item(0)->nodeValue;
					$data['response_class_id'] = $response['response_class_id'];
					$recording_urlTag = $objDOM->getElementsByTagName("recording_url");
					$response['response_recording_url'] = $recording_urlTag->item(0)->nodeValue;
					$data['response_recording_url'] = $response['response_recording_url'];

					$presenter_urlTag = $objDOM->getElementsByTagName("presenter_url");
					$response['response_presenter_url'] = $presenter_urlTag->item(0)->nodeValue;
					$data['response_presenter_url'] = $response['response_presenter_url'];
					$data['get_detail'] = '0';
					$data['is_recurring'] = 'True';               
					$data['created_by'] = $requestParameters["presenter_id"];
				   // save the data in the database.
					$presenter_urlTag = $objDOM->getElementsByTagName("recurring_summary");
					$response['recurring_summary'] = $presenter_urlTag->item(0)->nodeValue;
					
					$wiziq_wclasses = $wpdb->prefix."wiziq_wclasses";
					$insertqry = "insert into $wiziq_wclasses 
					(
					created_by, 
					class_name, 
					class_time, 
					duration, 
					courseid, 
					classtimezone, 
					language, 
					recordclass, 
					attendee_limit, 
					response_class_id, 
					response_recording_url, 
					response_presenter_url ,
					status, 
					master_id, 
					attendence_report, 
					get_detail, 
					download_recording, 
					is_recurring
					  )
				   values ('".$teacher_data."', 
				   '".$data['class_name']."' , 
					'".date ("Y-m-d H:i:s" , strtotime($response['classtime']))."' ,
					'".$data['duration']."',
					'".$data['courseid']."' ,
				   '".$data['classtimezone']."',
					'".$data['language']."',
					'".$data['recordclass']."',
					'".$data['attendee_limit']."',
					'".$response['response_class_id']."',
				   '".$response['response_recording_url']."',
					'".$response['response_presenter_url']."',
					'upcoming',
					'".$response['master_id']."',
				   '',
				   '0' ,
					'',
					'True')  
					  ";
					$wpdb->query($insertqry);
					?>
						<script>
							window.location = "<?php echo $return_url; ?>";
						</script>
					<?php
				} 
				//if single class
				else
				{
					$class_idTag = $objDOM->getElementsByTagName("class_id");
					$response['response_class_id'] = $class_idTag->item(0)->nodeValue;
					$recording_urlTag = $objDOM->getElementsByTagName("recording_url");
					$response['response_recording_url'] = $recording_urlTag->item(0)->nodeValue;
					$presenter_urlTag = $objDOM->getElementsByTagName("presenter_url");
					$response['response_presenter_url'] = $presenter_urlTag->item(0)->nodeValue;
					$wiziq_wclasses = $wpdb->prefix."wiziq_wclasses";
					
					$insertqry = "insert into $wiziq_wclasses 
					(
					created_by, 
					class_name, 
					class_time, 
					duration, 
					courseid, 
					classtimezone, 
					language, 
					recordclass, 
					attendee_limit, 
					response_class_id, 
					response_recording_url, 
					response_presenter_url ,
					status, 
					master_id, 
					attendence_report, 
					get_detail, 
					download_recording, 
					is_recurring
					  )
				   values ('".$teacher_data."', 
				   '".$data['class_name']."' , 
					'".date ("Y-m-d H:i:s" , strtotime($response['classtime']))."' ,
					'".$data['duration']."',
					'".$data['courseid']."' ,
				   '".$data['classtimezone']."',
					'".$data['language']."',
					'".$data['recordclass']."',
					'".$data['attendee_limit']."',
					'".$response['response_class_id']."',
				   '".$response['response_recording_url']."',
					'".$response['response_presenter_url']."',
					'upcoming', 
					'',
				   '',
				   '1' ,
					'',
					'False')  
					  ";
					$wpdb->query($insertqry);
					$lastid = $wpdb->insert_id;
					$class_url = $courses_url. $qvarsign. 'caction=view_front_class&class_id='.$lastid.'&course_id='.$_GET['course_id'] ;
					?>
						<script>
							window.location = "<?php echo $class_url; ?>";
						</script>
					<?php
				}
				
			} else if ($attribNode == "fail") 
			{
				$error = $objDOM->getElementsByTagName("error")->item(0);
				$errorcode = $error->getAttribute("code");
				$errormsg = $error->getAttribute("msg");
				if ( "1004" == $errorcode || "1005" == $errorcode || "1017" == $errorcode) {
					$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
				}
				else {
					$error1 = WIZIQ_COM_COMMAN_MESSAGE;
				}
				$class_add_error = '<div class = "error " id = "add-front-class-error" ><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_add_error', $class_add_error );
				
			} else {
				$class_add_error = '<div class = "error " id = "add-front-class-error" ><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'api_error', $class_add_error, 'Page Data' );
			}
			
		 } // end add class function

		/*
		 * Function to edit and update a class on front end
		 * pass post array and class id to update
		 * @since 1.0
		 */
		 function wiziq_frontend_update_class  ( $data , $class_id ) {
			global $wpdb;
			$Wiziq_Util = new Wiziq_Util;
			$wiziq_api_functions = new wiziq_frontend_api_functions;
			
			//url structure
			
			$courses_url = get_permalink();
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			$classcon = $data;
			
			$return_url = $courses_url. $qvarsign. 'caction=view_classes&course_id='.$_GET['course_id'] ;
			//Call to api function
			$XMLReturn = $wiziq_api_functions->updateSingleLiveClass($classcon , $class_id);
			if (!empty($XMLReturn)) {
				try {
					$objDOM = new DOMDocument();
					$objDOM->loadXML($XMLReturn);
				} catch (Exception $e) {
					$api_error .= $e->getMessage();
				}
				$status = $objDOM->getElementsByTagName("rsp")->item(0);
				$attribNode = $status->getAttribute("status");
			}//end if	
			if (isset($data['schedule_now'])) {
				$start_time =  date('m/d/Y H:i:s');     //date('m/d/Y H:i:s', strtotime($data['class_time']));
			} else {
				$start_time = date('m/d/Y', strtotime($classcon['class_time'])) . ' ' . $classcon['hours'] . ':' . $classcon['minutes'];
			}
			if ($attribNode == 'ok') {
					$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
					$resqry = "select response_class_id,created_by from $wiziq_classes where id = '$class_id'";
					$resres = $wpdb->get_row($resqry);
					$response_class_id = $resres->response_class_id;
					$returnurl = 
					$updqry = "update $wiziq_classes set class_name = '".$classcon['class_name']."',
					duration = '".$classcon['duration']."',
					class_time  = '".date ("Y-m-d H:i:s" , strtotime($start_time))."',
					classtimezone = '".$classcon['classtimezone']."',
					attendee_limit  = '".$classcon['attendee_limit']."',
					recordclass = '".$classcon['recordclass']."',
					language = '".$classcon['language']."'
					where response_class_id  = '$response_class_id'
					";
					$wpdb->query($updqry) ;
					$class_url = $courses_url. $qvarsign. 'caction=view_front_class&class_id='.$class_id.'&course_id='.$_GET['course_id'] ;
					?>
					<script>
						window.location = "<?php echo $class_url; ?>";
					</script>
					<?php
				
			} else if ($attribNode == "fail") {
				$xml = new SimpleXMLElement($XMLReturn);
				$error = $objDOM->getElementsByTagName("error")->item(0);
				$errorcode = $error->getAttribute("code");
				$errormsg = $error->getAttribute("msg");
				if ( "1004" == $errorcode || "1005" == $errorcode || "1017" == $errorcode) {
					$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
				}
				else {
					$error1 = WIZIQ_COM_COMMAN_MESSAGE;
				}
				$class_edit_error = '<div class="error"><p><strong>ERROR </strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_edit_error', $class_edit_error );
			} else {
				$class_edit_error =  '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.'</strong>'. __(WIZIQ_COM_CATCH , 'wiziq') .'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_edit_error', $class_edit_error );
			}
		}// end update class function
	}
	
	 
