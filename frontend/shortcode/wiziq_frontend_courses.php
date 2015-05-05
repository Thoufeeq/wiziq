<?php
	
	/*
	 * Wiziq frontend courses file
	 */
	 
	class Wiziq_Frontend_Courses {
		
		
		/*
		 * Function to display course detail
		 */ 
		function wiziq_frontend_view_detail ( $course_id ) {
			$courses_url = get_permalink();
			$Wiziq_Util = new Wiziq_Util;
			
			//url structure
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			
			
			
			$wiziq_courses = new Wiziq_Courses;
			$res = $wiziq_courses->wiziq_get_single_courses ($course_id);
			$view_classes = $courses_url.$qvarsign.'caction=view_classes'.'&course_id='.$res->id;
			?>
			<div class="front_wiziq userlogin " id="front_wiziq" >
				<div class="wiziq_left" >
				</div>
				<div class="wiziq_right" >
					<ul class = "wiziq_front_menu">
						<li><h3><a href="<?php echo $courses_url; ?>" ><?php _e('Courses', 'wiziq'); ?></a></h3></li>
					</ul>
					<div class = "clearfix" ></div>
					<table class = "form-table" >
							<tr>
								<th><?php _e('Course name', 'wiziq'); ?></th>
								<td>
								<strong>
									<?php echo $res->fullname;?>
								</strong>
								</td>
							</tr>
							<tr>
								<th><?php _e('Start date', 'wiziq'); ?></th>
								<td>
									<?php   
									if ( $res->startdate )
										echo date( WIZIQ_DATE_FORMAT, strtotime($res->startdate));
									else 
										_e( 'Start date not mentioned' , 'wiziq' );
									?>
								</td>
							</tr>
							<tr>
								<th><?php _e('End date', 'wiziq'); ?></th>
								<td>
								<?php 
									if ($res->enddate)
										echo date( WIZIQ_DATE_FORMAT, strtotime($res->enddate));
									else 
										_e('End date not mentioned');
								?>
								</td>
							</tr>
							<tr>
								<th><?php _e('Description', 'wiziq'); ?></th>
								<td>
								<?php 
									if ( $res->description )
										echo $res->description;
									else 
										_e( 'Description not added' , 'wiziq' );
								?>
								</td>
							</tr>
					</table>
					<div class= "frontend_buttons courses_frontend_buttons" >
						<a class= "button button-primary" title="<?php _e('Back To Courses', 'wiziq'); ?>" href="<?php echo $courses_url; ?>" ><?php _e('Back To Courses','wiziq'); ?></a>
						<a class= "button button-primary" title="<?php _e('View Classes', 'wiziq'); ?>" href="<?php echo $view_classes; ?>" ><?php _e('View Classes','wiziq'); ?></a>
					</div>
				</div>
			</div>
			<?php
		} 
		
		/*
		 * Function to display courses on front end to logged in users
		 * @since 1.0
		 */ 
		function wiziq_frontend_view_courses_logged () {
			global $wpdb;
			$user_permissions  = new Wiziq_User_Permissions;
			
			$Wiziq_Util = new Wiziq_Util;
			
			//url structure
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			
			$courses_url = get_permalink();
			$wiziq_frontend_courses = new Wiziq_Frontend_Courses;
			$courses_urls = $courses_url.$qvarsign.'action=courses';
			$add_curl = $courses_url.$qvarsign.'action=addcourse';
			$edit_curli = $courses_url.$qvarsign.'action=editcourse';
			$delete_curl = $courses_url.$qvarsign.'action=deletecourse';
			$content_url = $courses_url.$qvarsign.'ccaction=view_content';
			$wiziq_courses = new Wiziq_Courses;
			
			$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
			
			?>
			<div class="front_wiziq userlogin " id="front_wiziq" >
			<div class="wiziq_left" >
			</div>
			<div class="wiziq_right" >
				<?php 
				global $current_user;
				get_currentuserinfo();
				?>
					<ul class = "wiziq_front_menu">
						<li>
							<h3>
								<a href="<?php echo $courses_url; ?>" ><?php _e('Courses', 'wiziq'); ?></a>
							</h3>
						</li>
						<?php 	if(is_user_administrator()){ ?>
							<li>
								<h3>
									<a href="<?php echo $add_curl; ?>" ><?php _e('Add New Course', 'wiziq'); ?></a>
								</h3>
							</li>
						<?php } ?>
					</ul>
				<div class = "clearfix" ></div>
			<h4><?php _e( 'Courses List' , 'wiziq' );?></h4>
			<?php
			/*
			 * Sorting logic
			 */ 
			if ( ! isset ( $_GET['sort-by'] ) ) {
				$sortby = "id";
			} else {
				$sortby = $_GET['sort-by'];
			}
			if ( !isset ($_GET['order-by']) ) {
				$orderby = "desc";
			}else {
				$orderby = $_GET['order-by'];
			}
			/*
			 * Pagination functionality 
			 */ 
			$wiziq_courses = $wpdb->prefix."wiziq_courses";
			$courses = $wpdb->get_results( "select * from $wiziq_courses" ); 
			$total_pages = !empty($courses)?count($courses):0 ;
			$limit = WIZIQ_PAGINATION_LIMIT;
			$adjacents = 3;
			$page = isset($_GET['pageno'])?$_GET['pageno']:'';
			if($page) 
				$start = ($page - 1) * $limit; 			//first item to display on this page
			else
				$start = 0;								//if no page var is given, set start to 0
			$targetpage = "";
			$homepage = get_permalink().$qvarsign;
			$targetpage .= get_permalink().$qvarsign;
			if ( isset ($_GET['sort-by']) && isset ($_GET['order-by']) ) {
				$targetpage .= 'sort-by='.$sortby.'&order-by='.$orderby.'&';
			}
			$pagination =  $Wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
			
			
			$wiziq_courses = $wpdb->prefix."wiziq_courses";
			if ( $sortby == 'fullname' || $sortby ==  'id' ) {
				$wiziq_course_res = $wpdb->get_results( "select * from $wiziq_courses order by $sortby $orderby LIMIT $start ,$limit" );
			} elseif ( $sortby == 'count' ) {
				$wiziq_course_res = $wpdb->get_results("SELECT a.*,count(b.id) cls FROM $wiziq_courses a left join $wiziq_classes b on b.courseid=a.id group by a.id order by cls $orderby LIMIT $start ,$limit");
			} elseif ( $sortby == 'uname' ) {
				$users = $wpdb->prefix."users";
				$usersmeta = $wpdb->prefix."usermeta";
				$name_query = "SELECT
								c.*,
								if ( m1.meta_value = '', u1.display_name, CONCAT(m1.meta_value,' ',m2.meta_value)) as name
								FROM $wiziq_courses c
								JOIN $users u1 ON (c.created_by = u1.id )
								JOIN $usersmeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
								JOIN $usersmeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name') order by name $orderby LIMIT $start ,$limit
							";
				$wiziq_course_res = $wpdb->get_results( $name_query);
			}
				/***** Sorting functionality *****/ 
			 if ( isset ( $_GET['sort-by'] ) && $_GET [ 'order-by']  ) :
			 
			 
					if ( "fullname" == $_GET['sort-by'] ) :
					
						if ( "asc" == $_GET['order-by']) {
							$nameclass = "sorting-up";
							$ordering = "desc";
							$nametitle = __( 'Click to sort by descending order' ,'wiziq');
						} else {
							$nameclass = "sorting-down";
							$ordering = "asc";
							$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
						}
						$countclass = "sorting-up";
						$countordering = "asc";
						$counttitle = __( 'Click to sort by ascending order' ,'wiziq');
						$usernameclass = "sorting-down";
						$usernameordering = "asc";
						$usernametitle = __( 'Click to sort by ascending order' ,'wiziq');
					elseif ( "count" == $_GET['sort-by'] )  :
						if ( "asc" == $_GET['order-by']) {
							$countclass = "sorting-up";
							$countordering = "desc";
							$counttitle = __( 'Click to sort by descending order' ,'wiziq');
						} else {
							$countclass = "sorting-down";
							$countordering = "asc";
							$counttitle = __( 'Click to sort by ascending order' ,'wiziq');
						}
						$nameclass = "sorting-up";
						$ordering = "asc";
						$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
						$usernameclass = "sorting-down";
						$usernameordering = "asc";
						$usernametitle = __( 'Click to sort by ascending order' ,'wiziq');
						
					elseif ( "uname" == $_GET['sort-by'] )  :
						if ( "asc" == $_GET['order-by']) {
								$usernameclass = "sorting-up";
								$usernameordering = "desc";
								$usernametitle = __( 'Click to sort by descending order' ,'wiziq');
							} else {
								$usernameclass = "sorting-down";
								$usernameordering = "asc";
								$usernametitle = __( 'Click to sort by ascending order' ,'wiziq');
							}
							$nameclass = "sorting-up";
							$ordering = "asc";
							$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
							$countclass = "sorting-up";
							$countordering = "asc";
							$counttitle = __( 'Click to sort by ascending order' ,'wiziq');
					endif;
			else :
				$countordering = "asc";
				$counttitle = __( 'Click to sort by ascending order' ,'wiziq');
				$nameclass = "sorting-up";
				$ordering = "asc";
				$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
				$usernameclass = "sorting-down";
				$usernameordering = "asc";
				$usernametitle = __( 'Click to sort by ascending order' ,'wiziq');
			endif;
		
			if(is_user_administrator()){ ?>
				<table class="list_courses" id="list_courses" >
						<tr>
							<th>
								<a href = "<?php echo $homepage.'sort-by=fullname&order-by='.$ordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Course Name','wiziq') ;?>
										<?php if (isset ( $_GET[ 'sort-by' ] ) && "fullname" == $_GET[ 'sort-by' ] ) : ?>
											<div class = "<?php echo $nameclass; ?>" ></div>
										<?php endif; ?>
									</span>
								</a>
							</th>
							<th>
								<span><?php _e('Description','wiziq');?></span>
							</th>
							<th>
								<span><?php _e('Manage Course','wiziq');?></span>
							</th>
							<th>
								<a href = "<?php echo $homepage.'sort-by=uname&order-by='.$usernameordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Created By','wiziq') ;?></span>
									<?php if (isset ( $_GET[ 'sort-by' ] ) && "uname" == $_GET[ 'sort-by' ]): ?>
										<div class = "<?php echo $usernameclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</th>
							<th>
								<a href = "<?php echo $homepage.'sort-by=count&order-by='.$countordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Number of Classes','wiziq') ;?></span>
									<?php if (isset ( $_GET[ 'sort-by' ] ) && "count" == $_GET[ 'sort-by' ]): ?>
										<div class = "<?php echo $countclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</th>
						</tr>
					<?php
					$courses = get_front_courses();
					if($courses){
						
						//Get courses and display
						
						foreach($wiziq_course_res as $course){
							$wiziq_class_permission = $user_permissions->wiziq_front_class_permission($course->id);
							$view_classes = $courses_url.$qvarsign.'caction=view_classes'.'&course_id='.$course->id;
							$course_detail = $courses_url.$qvarsign.'action=course_desc&course_id='.$course->id;
							$deletenonce = wp_create_nonce( 'delete-course-' . $course->id );
							$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
							$classesqry = "select count(*) from $wiziq_classes where courseid = '$course->id'"; 
							$classesres = $wpdb->get_var($classesqry);
							$add_class_nonce = wp_create_nonce( 'add-class-' . $course->id );
							$add_curl = $courses_url.$qvarsign.'caction=add_class&course_id='.$course->id.'&wp_nonce='.$add_class_nonce;
							$edit_curl = wp_nonce_url( $edit_curli, 'edit-course-' . $course->id , 'wn');
							?>
								<input type = "hidden" value = "<?php echo $classesres; ?>" class = "hidden-classes" id= "hid-class-<?php echo $course->id;?>" />
							<?php
							echo '<tr>';
							echo '<td>';
							echo '<a href="'.$course_detail.'">';
							$coursename = $course->fullname;
							$courselen = strlen($coursename); 
							if ( $courselen > 10 )
								echo substr($coursename,0,10).'..';
							else 
								echo $course->fullname;
							echo '</a>';
							echo '</td>';
							echo '<td>';
							//Display description upto 6 words and rest on hover
							echo '<div class = "al_down" title="'.$course->description.'">';
							$des = $course->description;
							$desc = $Wiziq_Util->shorten_string( $des , 6);
							echo $desc;
							echo '</div>';
							echo '</td>';
							echo '<td class="icons">';
							echo '<a  title="'.__('Edit this course', 'wiziq').'" href="'.$edit_curl.'&course_id='.$course->id.'" ><img alt = "'.__('Edit', 'wiziq').'" class = "classes-images" src="'.WIZIQ_PLUGINURL_PATH.'images/edit15.png" /></a>';
							if ( $wiziq_class_permission ) :
								echo '<a title="'.__('Add new class', 'wiziq').'" href="'.$add_curl.'" ><img alt = "'.__('Add new class', 'wiziq').'" src="'.WIZIQ_PLUGINURL_PATH.'images/add15.png" /></a>';
							else :
								echo '<a title="'.__('Add new class', 'wiziq').'" href="#" ><img alt = "'.__('Add new class', 'wiziq').'" src="'.WIZIQ_PLUGINURL_PATH.'images/add15.png" /></a>';
							endif;
							echo '<a id = "'.$course->id.'" title="'.__('Delete this course', 'wiziq').'" class = "classes-images submitdelete" href="'.$delete_curl.'&course_id='.$course->id.'&wn='.$deletenonce.'" ><img class = "classes-images" src="'.WIZIQ_PLUGINURL_PATH.'images/delete15.png" /></a>';
							echo '</td>';
							echo '<td>';
							//get user data and display name who created the class
							$user_info = get_userdata( $course->created_by ); 
							echo $user_info->display_name;
							echo '</td>';
							echo '<td>';
							if ( $classesres ) {
								echo '<a href="'.$view_classes.'">';
								echo $classesres;
								echo '</a>';
							}
							else {
								echo '---';
							}
							echo '</td>'; 
							?>
							<?php 
							echo '</tr>';
						 } // end foreach
					}
					else {
						echo '<tr><td colspan="5">'.__('No course available','wiziq').'</td></tr>';
					} // else no course available
					?>
					<tr>
					<th>
					<span><?php _e('Course Name','wiziq') ;?></span>
					</th>
					<th>
					<span><?php _e('Description','wiziq');?></span>
					</th>
					<th>
					<span><?php _e('Manage Course','wiziq');?></span>
					</th>
					<th>
					<span><?php _e('Created By','wiziq') ;?></span>
					</th>
					<th>
					<span><?php _e('Number of Classes','wiziq') ;?></span>
					</th>
					</tr>
				</table>
					<?php 
					//display pagination
					echo $pagination;
			} else {
			//user logged in but not administrator
			 ?>
				<table class="list_courses" id="list_courses" >
						<tr>
							<th>
								<a href = "<?php echo $homepage.'sort-by=fullname&order-by='.$ordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Course Name','wiziq') ;?></span>
									<?php if (isset ( $_GET[ 'sort-by' ] ) && "fullname" == $_GET[ 'sort-by' ] ) : ?>
										<div class = "<?php echo $nameclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</th>
							<th>
								<span><?php _e('Description','wiziq');?></span>
							</th>
							<th>
								<a href = "<?php echo $homepage.'sort-by=uname&order-by='.$usernameordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Created By','wiziq') ;?></span>
									<?php if (isset ( $_GET[ 'sort-by' ] ) && "uname" == $_GET[ 'sort-by' ]): ?>
										<div class = "<?php echo $usernameclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</th>
							<th>
								<a href = "<?php echo $homepage.'sort-by=count&order-by='.$countordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Number of Classes','wiziq') ;?></span>
									<?php if (isset ( $_GET[ 'sort-by' ] ) && "count" == $_GET[ 'sort-by' ]): ?>
										<div class = "<?php echo $countclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</th>
						</tr>
					<?php
					$courses = get_front_courses();
					if($courses){
						global $wpdb;
						$Wiziq_Util = new Wiziq_Util;						
						foreach($wiziq_course_res as $course){
							$course_detail = $courses_url.$qvarsign.'action=course_desc&course_id='.$course->id;
							$view_classes = $courses_url.$qvarsign.'caction=view_classes'.'&course_id='.$course->id;
							$wiziq_classes = $wpdb->prefix."wiziq_wclasses";      
							$classesqry = "select count(*) from $wiziq_classes where courseid = '$course->id'"; 
							$classesres = $wpdb->get_var($classesqry);
							echo '<tr>';
							echo '<td>';
							echo '<a href="'.$course_detail.'">';
							$coursename = $course->fullname;
							$courselen = strlen($coursename); 
							if ( $courselen > 10 )
								echo substr($coursename,0,10).'..';
							else 
								echo $course->fullname;
							echo '</a>';
							echo '</td>';
							echo '<td>';
							//Display description upto 6 words and rest on hover
							echo '<div class = "al_down" title="'.$course->description.'">';
							$des = $course->description;
							$desc = $Wiziq_Util->shorten_string( $des , 6);
							echo $desc;
							echo '</div>';
							echo '</td>';
							echo '<td>';
							$user_info = get_userdata( $course->created_by ); 
							echo $user_info->display_name;
							echo '</td>';
							echo '<td>';
							if ( $classesres ) {
								echo '<a href="'.$view_classes.'">';
								echo $classesres;
								echo '</a>';
							}
							else {
								echo '---';
							}
							echo '</td>';  
							echo '</tr>';
						 } // end foreach
					} else {
						echo '<tr><td colspan="4">'.__('No course available','wiziq').'</td></tr>';
					} // else no course available
					echo '<tr>';
					echo '<th>';
					echo '<span>'.__('Course Name','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Description','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Created By','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Number of Classes','wiziq').'</span>';
					echo '</th>';
					echo '</tr>';
					?>
				</table>
			<?php echo $pagination;
			} ?>
				</div><!-- .close ./wiziq_right -->

			</div><!-- front_wiziq -->
			<div class = "wiziq_hide" >
				<span id = "course_delete" ><?php _e('Please delete inner classes first','wiziq');?></span>
				<span id = "wiziq_are_u_sure" ><?php _e('Are you sure, you want to delete','wiziq');?></span>
				<span id = "wiziq_select_course" ><?php _e('Please select courses to delete','wiziq');?></span>
			</div>
			<?php
		}//end of wiziq display courses function
		
		
		/*
		 * Function to add a course
		 * @since 1.0
		 */ 
		function  wiziq_frontend_add_courses_form () {
			?>
			<div class="front_addcourse" >
				<h3><?php _e('Add Course', 'wiziq'); ?></h3>
				<div class="frontaddform" >
					<form name="add_course_form" id="add_course_form" action="<?php echo get_permalink(); ?>" method="post" >
						<div class = "wiziq_hide" id = "course_name_msg" ><?php _e('Please enter course name.', 'wiziq'); ?></div>
						<div class = "wiziq_hide" id = "course_start_date_msg" ><?php _e('Start date can not be empty.', 'wiziq'); ?></div>
						<div class = "wiziq_hide" id = "course_end_date_msg" ><?php _e('End date can not be empty.', 'wiziq'); ?></div>
						<div class = "wiziq_hide" id = "course_end_date_greater_msg" ><?php _e('End date should be one day greater than start date.', 'wiziq'); ?></div>
						<table>
							<tr>
								<th><?php _e('Course name', 'wiziq'); ?><span class="required">*</span></th>
								<td>
									<input maxlength= "70" name="course_name" id="course_name"  />
									<div class="wiziq_error" id="course_name_err"></div>
								</td>
							</tr>
							<tr>
								<th>
									<?php _e('Start date', 'wiziq'); ?>
								</th>
								<td>
									<input name="course_start_date" id="course_start_date"  />
									<a id = 'course_start_date_id' href= "javascript:void(0);" onclick = "display_settings('course_start_date_id','course_start_date');" class = "date_remove_button wiziq_hide" ><?php _e('Clear' , 'wiziq' ); ?></a>
									<div class="wiziq_error" id="course_start_date_err"></div>
								</td>
							</tr>
							<tr>
								<th><?php _e('End date', 'wiziq'); ?></th>
								<td>
									<input name="course_end_date" id="course_end_date"  />
									<a id = 'course_end_date_id' href= "javascript:void(0);" onclick = "display_settings('course_end_date_id','course_end_date');" class = "date_remove_button wiziq_hide" ><?php _e('Clear' , 'wiziq' ); ?></a>
									<div class="wiziq_error" id="course_end_date_err"></div>
								</td>
							</tr>
							<tr>
								<th><?php _e('Description', 'wiziq'); ?></th>
								<td>
									<textarea maxlength= "1000" name="course_descirption" id="course_descirption"  ></textarea>
									<div class= "wiziq_limit"><?php _e('You can enter upto 1000 characters.','wiziq');?></div>
								</td>
							</tr>
						</table>
						<input type="submit" name="wiziq_addfront_course" id="wiziq_add_course" value="<?php _e('Save', 'wiziq'); ?>" />
						<input type="submit" name="wiziq_editfront_course_cancel" id="wiziq_cancel_course" value= "<?php _e('Cancel', 'wiziq'); ?>" />
					</form>
				</div>
			</div>
	
			<?php
		}// end add course form function
		
		/*
		 * Wiziq edit class form
		 * @since 1.0
		 */ 
		function wiziq_frontend_edit_courses_form () {
			$nonce = $_REQUEST['wn'];
			$course_id = $_REQUEST['course_id'];
			//check for valid request
			if ( ! wp_verify_nonce( $nonce , 'edit-course-'.$course_id  ) ) {
					?>
					<script>
						window.location = "<?php echo $returnurl; ?>";
					</script>
					<?php
			}
			$wiziq_courses_class = new Wiziq_Courses();
			$course_res = $wiziq_courses_class->wiziq_get_single_courses ($course_id);
			?>
			<div class="front_addcourse" >
			<h3><?php _e('Edit Course', 'wiziq'); ?></h3>
			<div class="frontaddform" >
				<form name="edit_course_form" id="add_course_form" action="<?php echo get_permalink(); ?>" method="post" >
					<div class = "wiziq_hide" id = "course_name_msg" ><?php _e('Please enter course name.', 'wiziq'); ?></div>
					<div class = "wiziq_hide" id = "course_start_date_msg" ><?php _e('Start date can not be empty.', 'wiziq'); ?></div>
					<div class = "wiziq_hide" id = "course_end_date_msg" ><?php _e('End date can not be empty.', 'wiziq'); ?></div>
					<div class = "wiziq_hide" id = "course_end_date_greater_msg" ><?php _e('End date should be one day greater than start date.', 'wiziq'); ?></div>
					<table>
						<tr>
							<th><?php _e('Course name', 'wiziq'); ?><span class="required">*</span></th>
							<td>
								<input maxlength= "70" name="course_name" id="course_name" value = "<?php echo $course_res->fullname; ?>" />
								<div class="wiziq_error" id="course_name_err"></div>
							</td>
						</tr>
						<tr>
							<th><?php _e('Start date', 'wiziq'); ?></th>
							<td>
								<input name="course_start_date" id="course_start_date"  value = "<?php if($course_res->startdate) { echo date( 'm/d/Y',strtotime($course_res->startdate)); }  ?>" />
								<?php if ( $course_res->enddate) : ?>
									<a id = 'course_start_date_id' href= "javascript:void(0);" onclick = "display_settings('course_start_date_id','course_start_date');" class = "date_remove_button wiziq_show" ><?php _e('Clear' , 'wiziq' ); ?></a>
								<?php else : ?>
									<a id = 'course_start_date_id' href= "javascript:void(0);" onclick = "display_settings('course_start_date_id','course_start_date');" class = "date_remove_button wiziq_hide" ><?php _e('Clear' , 'wiziq' ); ?></a>
								<?php endif; ?>
								<div class="wiziq_error" id="course_start_date_err"></div>
							</td>
						</tr>
						<tr>
							<th><?php _e('End date', 'wiziq'); ?></th>
							<td>
								<input name="course_end_date" id="course_end_date"  value = "<?php if($course_res->enddate) { echo date( 'm/d/Y', strtotime($course_res->enddate)) ; } ?>" />
								<?php if ( $course_res->enddate) : ?>
									<a id = 'course_end_date_id' href= "javascript:void(0);" onclick = "display_settings('course_end_date_id','course_end_date');" class = "date_remove_button wiziq_show" ><?php _e('Clear' , 'wiziq' ); ?></a>
								<?php else : ?>
									<a id = 'course_end_date_id' href= "javascript:void(0);" onclick = "display_settings('course_end_date_id','course_end_date');" class = "date_remove_button wiziq_hide" ><?php _e('Clear' , 'wiziq' ); ?></a>
								<?php endif; ?>
								<div class="wiziq_error" id="course_end_date_err"></div>
							</td>
						</tr>
						<tr>
							<th><?php _e('Description', 'wiziq'); ?></th>
							<td>
								<textarea maxlength= "1000" name="course_descirption" id="course_descirption"  ><?php echo $course_res->description; ?></textarea>
								<div class= "wiziq_limit"><?php _e('You can enter upto 1000 characters.','wiziq');?></div>
							</td>
						</tr>
					</table>
					<input type = "hidden" name= "course_id" value = "<?php echo $_GET['course_id']; ?>"  />
					<input type="submit" name="wiziq_editfront_course" id="wiziq_add_course" value = "<?php _e('Update', 'wiziq'); ?>" />
					<input type="submit" name="wiziq_editfront_course_cancel" id="wiziq_cancel_course" value = "<?php _e('Cancel', 'wiziq'); ?>" />					
			</form>
		</div>
	</div>
	<?php
	}// end course edit form function
		
		/*
		 * Function to display courses on front end to not logged users
		 * @since 1.0
		 */ 
		function wiziq_frontend_view_courses_not_logged () 
		{
			global $wpdb;
			//url structure
			$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
			$courses_url = get_permalink();
			$wiziq_frontend_courses = new Wiziq_Frontend_Courses;
			$wiziq_courses = new Wiziq_Courses;
			$Wiziq_Util = new Wiziq_Util;
			
			//url structure
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			
			$courses_urls = $courses_url.$qvarsign.'action=courses';
			$add_curl = $courses_url.$qvarsign.'action=addcourse';
			$edit_curl = $courses_url.$qvarsign.'action=editcourse';
			$delete_curl = $courses_url.$qvarsign.'action=deletecourse';
			/*
			 * Sorting logic
			 */ 
			if ( ! isset ( $_GET['sort-by'] ) ) {
				$sortby = "id";
			} else {
				$sortby = $_GET['sort-by'];
			}
			if ( !isset ($_GET['order-by']) ) {
				$orderby = "desc";
			}else {
				$orderby = $_GET['order-by'];
			}	
			
			/*
			 * Pagination functionality 
			 */ 
			$wiziq_courses = $wpdb->prefix."wiziq_courses";
			$courses = $wpdb->get_results( "select * from $wiziq_courses" ); 
			$total_pages = !empty($courses)?count($courses):0 ;
			$limit = WIZIQ_PAGINATION_LIMIT;
			$adjacents = 3;
			$page = isset($_GET['pageno'])?$_GET['pageno']:'';
			if($page) 
				$start = ($page - 1) * $limit; 			//first item to display on this page
			else
				$start = 0;								//if no page var is given, set start to 0
			$targetpage = "";
			$homepage = get_permalink().$qvarsign;
			$targetpage .= get_permalink().$qvarsign;
			if ( isset ($_GET['sort-by']) && isset ($_GET['order-by']) ) {
				$targetpage .= 'sort-by='.$sortby.'&order-by='.$orderby.'&';
			}
			$pagination =  $Wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
			
			
			$wiziq_courses = $wpdb->prefix."wiziq_courses";
			if ( $sortby == 'fullname' || $sortby ==  'id' ) {
				$wiziq_course_res = $wpdb->get_results( "select * from $wiziq_courses order by $sortby $orderby LIMIT $start ,$limit" );
			} elseif ( $sortby == 'count' ) {
				$wiziq_course_res = $wpdb->get_results("SELECT a.*,count(b.id) cls FROM $wiziq_courses a left join $wiziq_classes b on b.courseid=a.id group by a.id order by cls $orderby LIMIT $start ,$limit");
			} elseif ( $sortby == 'uname' ) {
				$users = $wpdb->prefix."users";
				$usersmeta = $wpdb->prefix."usermeta";
				$name_query = "SELECT
								c.*,
								if ( m1.meta_value = '', u1.display_name, CONCAT(m1.meta_value,' ',m2.meta_value)) as name
								FROM $wiziq_courses c
								JOIN $users u1 ON (c.created_by = u1.id )
								JOIN $usersmeta m1 ON (m1.user_id = u1.id AND m1.meta_key = 'first_name')
								JOIN $usersmeta m2 ON (m2.user_id = u1.id AND m2.meta_key = 'last_name') order by name $orderby LIMIT $start ,$limit
							";
				//$wiziq_course_result = $wpdb->get_results("SELECT a.*,count(b.id) cls FROM $wiziq_courses a left join $wiziq_classes b on b.courseid=a.id group by a.id order by name $orderby LIMIT $start ,$limit");
				$wiziq_course_res = $wpdb->get_results( $name_query);
			}
				/***** Sorting functionality *****/ 
			 if ( isset ( $_GET['sort-by'] ) && $_GET [ 'order-by']  ) :
			 
			 
					if ( "fullname" == $_GET['sort-by'] ) :
					
						if ( "asc" == $_GET['order-by']) {
							$nameclass = "sorting-up";
							$ordering = "desc";
							$nametitle = __( 'Click to sort by descending order' ,'wiziq');
						} else {
							$nameclass = "sorting-down";
							$ordering = "asc";
							$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
						}
						$countclass = "sorting-up";
						$countordering = "asc";
						$counttitle = __( 'Click to sort by ascending order' ,'wiziq');
						$usernameclass = "sorting-down";
						$usernameordering = "asc";
						$usernametitle = __( 'Click to sort by ascending order' ,'wiziq');
					elseif ( "count" == $_GET['sort-by'] )  :
						if ( "asc" == $_GET['order-by']) {
							$countclass = "sorting-up";
							$countordering = "desc";
							$counttitle = __( 'Click to sort by descending order' ,'wiziq');
						} else {
							$countclass = "sorting-down";
							$countordering = "asc";
							$counttitle = __( 'Click to sort by ascending order' ,'wiziq');
						}
						$nameclass = "sorting-up";
						$ordering = "asc";
						$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
						$usernameclass = "sorting-down";
						$usernameordering = "asc";
						$usernametitle = __( 'Click to sort by ascending order' ,'wiziq');
						
					elseif ( "uname" == $_GET['sort-by'] )  :
						if ( "asc" == $_GET['order-by']) {
								$usernameclass = "sorting-up";
								$usernameordering = "desc";
								$usernametitle = __( 'Click to sort by descending order' ,'wiziq');
							} else {
								$usernameclass = "sorting-down";
								$usernameordering = "asc";
								$usernametitle = __( 'Click to sort by ascending order' ,'wiziq');
							}
							$nameclass = "sorting-up";
							$ordering = "asc";
							$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
							$countclass = "sorting-up";
							$countordering = "asc";
							$counttitle = __( 'Click to sort by ascending order' ,'wiziq');
					endif;
			else :
				$countordering = "asc";
				$counttitle = __( 'Click to sort by ascending order' ,'wiziq');
				$nameclass = "sorting-up";
				$ordering = "asc";
				$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
				$usernameclass = "sorting-down";
				$usernameordering = "asc";
				$usernametitle = __( 'Click to sort by ascending order' ,'wiziq'); 
			endif;
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
				<div class="clearfix"></div>
				<h4><?php _e( 'Courses List' , 'wiziq' );?></h4>
				<table class="list_courses" id="list_courses" >
						<tr>
							<th>
								<a href = "<?php echo $homepage.'sort-by=fullname&order-by='.$ordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Course Name','wiziq') ;?></span>
									<?php if (isset ( $_GET[ 'sort-by' ] ) && "fullname" == $_GET[ 'sort-by' ] ) : ?>
										<div class = "<?php echo $nameclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</th>
							<th>
								<span><?php _e('Description','wiziq');?></span>
							</th>
							<th>
								<a href = "<?php echo $homepage.'sort-by=uname&order-by='.$usernameordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Created By','wiziq') ;?></span>
									<?php if (isset ( $_GET[ 'sort-by' ] ) && "uname" == $_GET[ 'sort-by' ]): ?>
										<div class = "<?php echo $usernameclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</th>
							<th>
								<a href = "<?php echo $homepage.'sort-by=count&order-by='.$countordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Number of Classes','wiziq') ;?></span>
									<?php if (isset ( $_GET[ 'sort-by' ] ) && "count" == $_GET[ 'sort-by' ]): ?>
										<div class = "<?php echo $countclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</th>
						</tr>
					<?php
					$courses = get_front_courses();
					if($courses){
						foreach($wiziq_course_res as $course){
							$course_detail = $courses_url.$qvarsign.'action=course_desc&course_id='.$course->id;
							$view_classes = $courses_url.$qvarsign.'caction=view_classes&course_id='.$course->id;
							$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
							$classesqry = "select count(*) from $wiziq_classes where courseid = '$course->id'"; 
							$classesres = $wpdb->get_var($classesqry);
							echo '<tr>';
							echo '<td>';
							echo '<a href="'.$course_detail.'">';
							$coursename = $course->fullname;
							$courselen = strlen($coursename); 
							if ( $courselen > 10 )
								echo substr($coursename,0,10).'..';
							else 
								echo $course->fullname;
							echo '</a>';
							echo '</td>';
							echo '<td>';
							//Display description upto 6 words and rest on hover
							echo '<div class = "al_down" title="'.$course->description.'">';
							$des = $course->description;
							$desc = $Wiziq_Util->shorten_string( $des , 6);
							echo $desc;
							echo '</div>';
							echo '</td>';
							echo '<td>';
							$user_info = get_userdata( $course->created_by ); 
							echo $user_info->display_name;
							echo '</td>';
							echo '<td>';
							if ( $classesres ) {
								echo '<a href="'.$view_classes.'">';
								echo $classesres;
								echo '</a>';
							}
							else {
								echo '---';
							}
							echo '</td>'; 
							echo '</tr>';
						 } // end foreach
					} else {
						echo '<tr><td colspan="4">'.__('No course available','wiziq').'</td></tr>';
					} // else no course available
					echo '<tr>';
					echo '<th>';
					echo '<span>'.__('Course Name','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Description','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Created By','wiziq').'</span>';
					echo '</th>';
					echo '<th>';
					echo '<span>'.__('Number of Classes','wiziq').'</span>';
					echo '</th>';
					echo '</tr>';
					?>
				</table>
				<!-- Display pagination -->
				<?php echo $pagination; ?>
				</div>
			</div>	
				<?php
		}//end view courses function
	} 
	
