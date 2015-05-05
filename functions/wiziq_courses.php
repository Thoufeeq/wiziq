<?php
/*
 * This file contains functions for courses
 * @since 1.0
 */ 

class Wiziq_Courses {
	
	/*
	 * Function to display courses
	 * List function working from wordpress 3.1
	 * @since 1.0
	 */ 
	function  wiziq_view_courses() {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
/*
		if (isset ($_REQUEST['order-by'])) {
			$sortby = $_REQUEST['order-by'];
			if ( "asc" == $sortby )  {
				$sortanchor = "desc";
			} else {
				$sortanchor = "asc";
			}
			$sortid = "fullname";
		} else {
			$sortby = "desc";
			$sortid = "id";
			$sortanchor = "asc";
		}
*/
		
		
		if ( ! isset ( $_GET['sortby'] ) ) {
			$sortby = "id";
		} else {
			$sortby = $_GET['sortby'];
		}
		if ( !isset ($_GET['orderby']) ) {
			$orderby = "desc";
		}else {
			$orderby = $_GET['orderby'];
		}
		?>
		<h2><?php _e('WizIQ Courses', 'wiziq'); ?><a class = "add-new-h2"  href= "<?php echo WIZIQ_COURSES_MENU; ?>&add_course" ><?php _e('Add New Course', 'wiziq'); ?></a></h2>
		<?php
			/*
			 * Messages
			 */ 
			if ( isset ($_GET['success']) && ! isset ( $_POST['multiple_actions']  ) ) {
				echo '<div class = "updated" ><p><strong>'.__('Course created successfully','wiziq').'</strong></p></div>';
			} else if ( isset ($_GET['deleted']) && ! isset ( $_POST['multiple_actions']  )) {
				echo '<div class = "updated" ><p><strong>'.__('Course deleted successfully','wiziq').'</strong></p></div>';
			} else if ( isset ( $_POST['multiple_actions']  ) && $_POST['multiple_actions'] ==  "Delete" ) {
				echo '<div class = "updated" ><p><strong>'.__('Courses deleted successfully','wiziq').'</strong></p></div>';
			}
		
			
			/*
			 * Pagination functioning
			 */ 
			global $wpdb;
			$wiziq_Util = new Wiziq_Util;
			$wiziq_course_res = $this->wiziq_get_courses();
			$total_pages = !empty($wiziq_course_res)?count($wiziq_course_res):0 ;
			$limit = WIZIQ_PAGINATION_LIMIT;
			$adjacents = 3;
			$page = isset($_GET['pageno'])?$_GET['pageno']:'';
			if($page) 
				$start = ($page - 1) * $limit; 			//first item to display on this page
			else
				$start = 0;								//if no page var is given, set start to 0
			$targetpage = "?page=wiziq&";
			if ( isset ($_GET['sortby']) && isset ($_GET['orderby']) ) {
				$targetpage .= 'sortby='.$_GET['sortby'].'&orderby='.$_GET['orderby'].'&';
			}
			$pagination =  $wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
			$wiziq_courses = $wpdb->prefix."wiziq_courses";
			if ( $sortby == 'fullname' || $sortby ==  'id' ) {
				$wiziq_course_result = $wpdb->get_results( "select * from $wiziq_courses order by $sortby $orderby LIMIT $start ,$limit" );
			} elseif ( $sortby == 'count' ) {
				//$countquery = "select * from $wiziq_courses, $wiziq_classes ";
				$wiziq_course_result = $wpdb->get_results("SELECT a.*,count(b.id) cls FROM $wiziq_courses a left join $wiziq_classes b on b.courseid=a.id group by a.id order by cls $orderby LIMIT $start ,$limit");
				///$wiziq_course_result = $wpdb->get_results( "select * from $wiziq_courses order by $sortby  LIMIT $start ,$limit" );
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
				$wiziq_course_result = $wpdb->get_results( $name_query);
			}
			
			 
			$pagetogo = $page;
			
			
			
			/***** Sorting functionality *****/ 
			 if ( isset ( $_GET['sortby'] ) && $_GET [ 'orderby']  ) :
			 
					if ( "fullname" == $_GET['sortby'] ) :
						if ( "asc" == $_GET['orderby']) {
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
					elseif ( "count" == $_GET['sortby'] )  :
						if ( "asc" == $_GET['orderby']) {
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
						
					elseif ( "uname" == $_GET['sortby'] )  :
						if ( "asc" == $_GET['orderby']) {
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
		<form method = "post" >
			<div class = "tablenav top">
				<div class="alignleft actions bulkactions">
					<select name="multiple_actions" id= "delete_action_course" >
						<option value = "-1" ><span><?php _e('Bulk Actions', 'wiziq'); ?></span></option>
						<option value = "1"><span><?php _e('Delete', 'wiziq'); ?></span></option>
					</select>
					<input id="doaction" class="button action delete-courses" type="submit" value="<?php _e('Apply', 'wiziq'); ?>" name="">
				</div>
			</div>
			<table class= "wp-list-table widefat fixed pages" >
				<thead>
					<tr>
						<th class = "manage-column column-cb check-column" >
							<label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All','wiziq'); ?></label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th id = "course_name" class = "manage-column sortable desc" >
							<a href="<?php echo WIZIQ_COURSES_MENU.'&sortby=fullname&orderby='.$ordering; ?>">
								<span><?php _e('Name', 'wiziq'); ?></span>
								<?php if (isset ( $_GET[ 'sortby' ] ) && "fullname" == $_GET[ 'sortby' ] ) : ?>
										<div class = "<?php echo $nameclass; ?>" ></div>
								<?php endif; ?>
							</a>
						</th>
						<th id = "course_description" class = "manage-column" >
							<?php _e('Description', 'wiziq'); ?>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Manage Courses', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<a href="<?php echo WIZIQ_COURSES_MENU.'&sortby=uname&orderby='.$usernameordering; ?>">
							<?php _e('Created By', 'wiziq'); ?>
							<?php if (isset ( $_GET[ 'sortby' ] ) && "uname" == $_GET[ 'sortby' ]): ?>
									<div class = "<?php echo $usernameclass; ?>" ></div>
								<?php endif; ?>
							</a>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<a href="<?php echo WIZIQ_COURSES_MENU.'&sortby=count&orderby='.$countordering; ?>">
								<?php _e('Number of Classes', 'wiziq'); ?>
								<?php if (isset ( $_GET[ 'sortby' ] ) && "count" == $_GET[ 'sortby' ]): ?>
									<div class = "<?php echo $countclass; ?>" ></div>
								<?php endif; ?>
							</a>
						</th>
						<th class = "manage-column" >
							<?php _e('Start Date', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('End Date', 'wiziq'); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class = "manage-column column-cb check-column" scope="row">
							<label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All','wiziq'); ?></label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th id = "course_name" class = "manage-column sortable desc" scope="row">
							<a href="#">
								<span><?php _e('Name', 'wiziq'); ?></span>
							</a>
						</th>
						<th id = "course_description" class = "manage-column sortable desc" >
							<?php _e('Description', 'wiziq'); ?>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Manage Courses', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column sortable desc" scope="row">
							<?php _e('Created By', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('Number of Classes', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('Start Date', 'wiziq'); ?>
						</th>
						<th class = "manage-column" >
							<?php _e('End Date', 'wiziq'); ?>
						</th>
					</tr>
				</tfoot>
				<tbody>
					<?php
				
					if( $wiziq_course_result ) 
					{
						$countrow = 0;
						foreach ($wiziq_course_result as $res) 
						{
							/*
							 * get class for this course
							 */ 
							
							$classesqry = "select count(*) from $wiziq_classes where courseid = '$res->id'"; 
							$classesres = $wpdb->get_var($classesqry);
							
							$nonce = wp_create_nonce( 'edit-course-' . $res->id );
							$deletenonce = wp_create_nonce( 'delete-course-' . $res->id );
							$add_class_nonce = wp_create_nonce( 'add-class-' . $res->id );
							$course_detail_page = WIZIQ_COURSES_MENU."&course_detail&course_id=".$res->id;
							$countrow++;
							if( "1" == $countrow) 
							{ 
								$rowclass = "alternate iedit courses";
							}
							else 
							{
								$countrow = "0";
								$rowclass = "iedit courses";
								
							}
							?>
								<tr id = "course-<?php echo $res->id; ?>" class = "<?php echo $rowclass; ?>" >
									<th class="check-column" scope="row">
										<label class="screen-reader-text" for="cb-select-8">Select <?php echo $res->fullname; ?></label>
										<input id="cb-select-<?php echo $res->id; ?>" type="checkbox" value="<?php echo $res->id; ?>" name = "cousre-checkbox[]" value= "<?php echo $res->id; ?>">
										<div class="locked-indicator"></div>
										<input type = "hidden" value = "<?php echo $classesres; ?>" class = "hidden-classes" id= "hid-class-<?php echo $res->id;?>" />
									</th>
									<td class = "post-title page-title column-title" >
										<strong>
										<a href="<?php echo $course_detail_page; ?>">
											<?php echo $res->fullname; ?>
										</a>
										</strong>
										<div class="row-actions">
											<span class="edit">
												<a title="<?php _e('Edit this course', 'wiziq'); ?>" href="<?php echo WIZIQ_COURSES_MENU; ?>&edit_course&course_id=<?php echo $res->id;?>&wp_nonce=<?php echo $nonce; ?>"><?php _e( 'Edit' , 'wiziq' ); ?></a>
												|
											</span>
											<span class="trash">
												<a id = "<?php echo $res->id;?>" class="submitdelete-course" href="<?php echo WIZIQ_COURSES_MENU; ?>&delete_course&course_id=<?php echo $res->id;?>&wp_nonce=<?php echo $deletenonce; ?>" title="<?php _e('Delete this course', 'wiziq'); ?>"><?php _e( 'Delete' , 'wiziq' ); ?></a>
											</span>
										</div>
									</td>
									<td>
										<?php
										$des = $res->description;
										$desc = $wiziq_Util->shorten_string( $des , 6);
										?>
										<div class = "al_down" title="<?php echo $res->description; ?>" >
										<?php
											echo $desc;
										?>
										</div>
									</td>
									<td>
										<a href= "<?php echo WIZIQ_COURSES_MENU; ?>&edit_course&course_id=<?php echo $res->id;?>&wp_nonce=<?php echo $nonce; ?>" >
											<img title= "<?php _e('Edit', 'wiziq'); ?>" class = "courses-images" src= "<?php echo plugins_url( 'images/edit20.png' , dirname(__FILE__) ) ; ?>" alt ="<?php _e('Edit', 'wiziq'); ?>" />
										</a> 
										<a href= "<?php echo WIZIQ_CLASS_MENU; ?>&action=add_class&course_id=<?php echo $res->id;?>&wp_nonce=<?php echo $add_class_nonce ?>" >
											<img title= "<?php _e('Add new class', 'wiziq'); ?>" class = "courses-images" src= "<?php echo plugins_url( 'images/add20.png' , dirname(__FILE__) ) ; ?>" alt ="<?php _e('Add new class', 'wiziq'); ?>" />
										</a>
										<a href= "<?php echo WIZIQ_ENROLL_MENU; ?>&course_id=<?php echo $res->id;?>" >
											<img title= "<?php _e('Enroll user', 'wiziq'); ?>" class = "courses-images" src= "<?php echo plugins_url( 'images/enroll20.png' , dirname(__FILE__) ) ; ?>" alt ="<?php _e('Enroll user', 'wiziq'); ?>" />
										</a>
									</td>
									<td>
										<?php
										$user_info = get_userdata( $res->created_by ); 
										echo $user_info->display_name;
										?>
									</td>
									<td>
									<a href="<?php echo WIZIQ_CLASS_MENU; ?>&action=view_course&course_id=<?php echo $res->id;?>">
									<?php 
									if ( $classesres ) {
										
										echo $classesres;
									}
									else {
										echo '--';
									}
									?>
									</a>
										
									</td>
									<td>
										<?php 
											
											if ( $res->startdate )
												echo date( WIZIQ_DATE_FORMAT, strtotime($res->startdate));
											else 
												echo '---';
										?>
									</td>
									<td>
										<?php 
											if ($res->enddate)
												echo date( WIZIQ_DATE_FORMAT, strtotime($res->enddate));
											else 
												echo '---';
										?>
									</td>
								</tr>
							<?php
						}
					} 
					else {
						echo '<tr id = "course" class = "alternate iedit" >';
							echo '<td colspan = "8">';
								echo __('No course available','wiziq');
							echo '</td>';
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
			<div class = "wiziq_hide" >
				<span id = "course_delete" ><?php _e('Please delete inner classes first','wiziq');?></span>
				<span id = "wiziq_are_u_sure" ><?php _e('Are you sure, you want to delete','wiziq');?></span>
				<span id = "wiziq_select_course" ><?php _e('Please select courses to delete','wiziq');?></span>
			</div>
			<div class= "tablenav bottom">
				<?php echo $pagination ; ?>
			</div>
			<br class="clear">
		</form>
		<br class="clear">
		<?php
	}// end view courses function
	
	/*
	 * Function to add courses
	 * @since 1.0
	 */ 
	function wiziq_add_course ( $course_content , $returnurl ) {
		global $wpdb;
		$created_by = get_current_user_id();
		$wiziq_courses = $wpdb->prefix."wiziq_courses";
		$course_name = trim($course_content['course_name']);
		$course_short_name = trim($course_content['course_short_name']);
		if( $course_content['course_start_date'] != "" ) {
			$course_start_date = date("Y/m/d", strtotime($course_content['course_start_date']));
		} else {
			$course_start_date = '';
		}
		if( $course_content['course_end_date']  != "" ) {
			$course_end_date = date("Y/m/d", strtotime($course_content['course_end_date']));
		} else {
			$course_end_date = '';
		}
		$course_descirption =  trim($course_content['course_descirption']);
		if ($course_start_date ) {
			$wpdb->query("insert into $wiziq_courses (created_by,fullname,startdate,enddate,description) values ('$created_by','$course_name', '$course_start_date' , '$course_end_date' ,'$course_descirption')");
		}
		else {
			$wpdb->query("insert into $wiziq_courses (created_by,fullname,description) values ('$created_by','$course_name','$course_descirption')");
		}
		?>
			<script>
				window.location = "<?php echo $returnurl; ?>";
			</script>
		<?php
	}// end function to add a course
	
	/*
	 * Function to display courses form
	 * @since 1.0
	 */ 
	function  wiziq_add_course_form() {
		?>
		<h3><?php _e('Add Course','wiziq'); ?></h3>
		<form method = "post" id= "add_course_form" >
			<?php wp_nonce_field('add_course','add_course_nonce'); ?>
			<div class = "wiziq_hide" id = "course_name_msg" ><?php _e('Please enter course name.', 'wiziq'); ?></div>
			<div class = "wiziq_hide" id = "course_start_date_msg" ><?php _e('Start date can not be empty.', 'wiziq'); ?></div>
			<div class = "wiziq_hide" id = "course_end_date_msg" ><?php _e('End date can not be empty.', 'wiziq'); ?></div>
			<div class = "wiziq_hide" id = "course_end_date_greater_msg" ><?php _e('End date should be one day greater than start date.', 'wiziq'); ?></div>
			<table class = "form-table" >
				<tbody>
					<tr>
						<th><?php _e('Course name', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<input maxlength= "70" type = "text" class = "regular-text" id = "course_name" name="course_name" />
							<div class = "wiziq_error" id = "course_name_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Start date', 'wiziq'); ?></th>
						<td>
							<input type = "text" class = "regular-text" id = "course_start_date" name="course_start_date" />
							<a id = 'course_start_date_id' href= "javascript:void(0);" onclick = "display_settings('course_start_date_id','course_start_date');" class = "date_remove_button wiziq_hide" ><?php _e('Clear','wiziq');?></a>
							<div class = "wiziq_error" id = "course_start_date_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('End date', 'wiziq'); ?></th>
						<td>
							<input type = "text" class = "regular-text" id = "course_end_date" name="course_end_date" />
							<a id = 'course_end_date_id' href= "javascript:void(0);" onclick = "display_settings('course_end_date_id','course_end_date');" class = "date_remove_button wiziq_hide" ><?php _e('Clear','wiziq');?></a>
							<div class = "wiziq_error" id = "course_end_date_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Description', 'wiziq'); ?></th>
						<td>
							<textarea maxlength= "1000" id="course_descirption" cols="40" rows="5" name="course_descirption"></textarea>
							<p class= "wiziq_limit description"><?php _e('You can enter upto 1000 characters.','wiziq');?></p>
							<div class = "wiziq_error" id = "course_descirption_err" ></div>
						</td>
					</tr>
				</tbody>
			</table>
			<input class= "button button-primary wiziq-button" id = "wiziq_add_course" type = "Submit" name = "wiziq_add_course" value="<?php _e('Save','wiziq') ?>" /> 
			<a class= "button button-primary wiziq-button" id = "wiziq_cancel_course" href = "<?php echo WIZIQ_COURSES_MENU; ?>" ><?php _e('Cancel','wiziq') ?></a>
		</form>
		<?php
	}// end add course form function


	/*
	 * Function to display the edit form
	 * @since 1.0
	 */ 
	function wiziq_edit_course_form ( $nonce, $course_id , $returnurl ) {
		
		/*
		 * Check for valid nonce
		 */ 
		if ( ! wp_verify_nonce( $nonce , 'edit-course-'.$course_id  ) ) {
			?>
			<script>
				window.location = "<?php echo $returnurl; ?>";
			</script>
		<?php
		}
		$course_res = $this->wiziq_get_single_courses ($course_id);
		?>
		<h3><?php _e('Edit Course','wiziq');?></h3>
		<form method = "post" id= "add_course_form" >
			<div class = "wiziq_hide" id = "course_name_msg" ><?php _e('Please enter course name.', 'wiziq'); ?></div>
			<div class = "wiziq_hide" id = "course_start_date_msg" ><?php _e('Start date can not be empty.', 'wiziq'); ?></div>
			<div class = "wiziq_hide" id = "course_end_date_msg" ><?php _e('End date can not be empty.', 'wiziq'); ?></div>
			<div class = "wiziq_hide" id = "course_end_date_greater_msg" ><?php _e('End date should be one day greater than start date.', 'wiziq'); ?></div>
			<table class = "form-table" >
				<tbody>
					<tr>
						<th><?php _e('Course name', 'wiziq'); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span></th>
						<td>
							<input type = "text" maxlength= "70" class = "regular-text" id = "course_name" name="course_name" value = "<?php echo $course_res->fullname; ?>" />
							<div class = "wiziq_error" id = "course_name_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Start date', 'wiziq'); ?></th>
						<td>
							<input type = "text" class = "regular-text" id = "course_start_date" name="course_start_date" value = "<?php if($course_res->startdate) { echo date( 'm/d/Y',strtotime($course_res->startdate)); }  ?>" />
							<?php if ( $course_res->enddate) : ?>
							<a id = 'course_start_date_id' href= "javascript:void(0);" onclick = "display_settings('course_start_date_id','course_start_date');" class = "date_remove_button wiziq_show" ><?php _e('Clear','wiziq');?></a>
							<?php else : ?>
							<a id = 'course_start_date_id' href= "javascript:void(0);" onclick = "display_settings('course_start_date_id','course_start_date');" class = "date_remove_button wiziq_hide" ><?php _e('Clear','wiziq');?></a>
							<?php endif; ?>
							<div class = "wiziq_error" id = "course_start_date_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('End date', 'wiziq'); ?></th>
						<td>
							<input type = "text" class = "regular-text" id = "course_end_date" name="course_end_date" value = "<?php if($course_res->enddate) { echo date( 'm/d/Y', strtotime($course_res->enddate)) ; } ?>" />
							<?php if ( $course_res->enddate) : ?>
							<a id = 'course_end_date_id' href= "javascript:void(0);" onclick = "display_settings('course_end_date_id','course_end_date');" class = "date_remove_button wiziq_show" ><?php _e('Clear','wiziq');?></a>
							<?php else : ?>
							<a id = 'course_end_date_id' href= "javascript:void(0);" onclick = "display_settings('course_end_date_id','course_end_date');" class = "date_remove_button wiziq_hide" ><?php _e('Clear','wiziq');?></a>
							<?php endif; ?>
							<div class = "wiziq_error" id = "course_end_date_err" ></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Description', 'wiziq'); ?></th>
						<td>
							<textarea id="course_descirption" maxlength= "1000" cols="40" rows="5" name="course_descirption"><?php echo $course_res->description; ?></textarea>
							<p class= "wiziq_limit description"><?php _e('You can enter upto 1000 characters.','wiziq');?></p>
							<div class = "wiziq_error" id = "course_descirption_err" ></div>
						</td>
					</tr>
				</tbody>
			</table>
			<input class= "button button-primary wiziq-button" id = "wiziq_add_course" type = "Submit" name = "wiziq_edit_course" value="<?php _e('Save', 'wiziq' ); ?>" /> 
			<a class= "button button-primary wiziq-button" id = "wiziq_cancel_course" href = "<?php echo WIZIQ_COURSES_MENU; ?>" ><?php _e('Cancel', 'wiziq' ); ?></a>
		</form>
		<?php
	}// end edit course form function
	
	/*
	 * Function to edit courses
	 * @since 1.0
	 */ 
	function wiziq_edit_course( $course_id, $course_content, $returnurl ) {
		global $wpdb;
		$created_by = get_current_user_id();
		$wiziq_courses = $wpdb->prefix."wiziq_courses";
		$course_name = trim($course_content['course_name']);
		if( $course_content['course_start_date'] != "" ) {
			$course_start_date = date("Y/m/d", strtotime($course_content['course_start_date']));
		} else {
			$course_start_date = "";
		}
		if( $course_content['course_end_date'] != "") {
			$course_end_date = date("Y/m/d", strtotime($course_content['course_end_date']));
		} else {
			$course_end_date = "";
		}
		$course_descirption =  trim($course_content['course_descirption']);
		if ($course_start_date ) :
		$qry = "update  $wiziq_courses set fullname = '$course_name',
		startdate = '$course_start_date',
		enddate = '$course_end_date',
		description = '$course_descirption'
		where id= '$course_id'
		 ";
		$wpdb->query($qry);
		else :
		$qry = "update  $wiziq_courses set fullname = '$course_name',
		description = '$course_descirption',
		startdate = NULL,
		enddate = NULL
		where id= '$course_id'
		 ";
		$wpdb->query($qry);
		endif;
		?>
			<script>
				window.location = "<?php echo $returnurl; ?>";
			</script>
		<?php
	}// end edit class function
	
	/*
	 * Function to a single delete courses
	 * @since 1.0
	 */ 
	 function wiziq_delete_course ( $nonce, $course_id , $returnurl) {
		 /*
		 * Check for valid nonce
		 */ 
		if ( wp_verify_nonce( $nonce , 'delete-course-'.$course_id) ) {
			global $wpdb;
			$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
			$classesqry = "select count(*) from $wiziq_classes where courseid = '$course_id'"; 
			$classesres = $wpdb->get_var($classesqry);
			if ( ! $classesres ) {
				$wiziq_courses = $wpdb->prefix."wiziq_courses";
				$wpdb->query( "delete from $wiziq_courses where id = '$course_id'" );
			}
			if( isset ( $returnurl ) ) {
				?>
				<script> 
					window.location = "<?php echo $returnurl; ?>";
				</script>
				<?php
			}
				
		} 
	 }// end delete a single course functiion
	 
	 /*
	  * Function to delete multiple courses
	  * @since 1.0
	  */ 
	function wiziq_delete_multiple_course( $course_content ) {
		if (isset ($course_content ['cousre-checkbox']) ) { 
			global $wpdb;
			$wiziq_courses = $wpdb->prefix."wiziq_courses";
			$courses = $course_content ['cousre-checkbox'];
			foreach ( $courses as $course_id ) {
				if ( ! isset ($c_id) ) {
					$c_id = $course_id;
				} else {
					$c_id .= ",".$course_id;
				}
			}
			$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
			$classesqry = "select count(*) from $wiziq_classes where courseid = '$course_id'"; 
			$classesres = $wpdb->get_var($classesqry);
			if ( ! $classesres ) {
				$wpdb->query("delete from $wiziq_courses where id IN ($c_id)");
			}
		}
		else {
			return;
		}
	}// end delete multiple courses function
	 
	 
	 /*
	  * Function to get all the courses
	  * @since 1.0
	  */ 
	function wiziq_get_courses () {
		global $wpdb;
		$wiziq_courses = $wpdb->prefix."wiziq_courses";
		$wiziq_results = $wpdb->get_results( "select * from $wiziq_courses order by id DESC" );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	}// end functon to get list of courses
	  
	  /*
	   * Function to get a single course
	   * @since 1.0
	   */ 
	function wiziq_get_single_courses ($course_id) {
		global $wpdb;
		$wiziq_courses = $wpdb->prefix."wiziq_courses";
		$wiziq_results = $wpdb->get_row( "select * from $wiziq_courses where id = '$course_id' " );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		  } else {
			return false;
		  }
	}// end function to get a single course result
	
	/*
	 * Function to view the course detail
	 */ 
	function wiziq_view_course_detail ( $course_id, $return_url ) {
		$res = $this->wiziq_get_single_courses ($course_id);
		?>
		<h2><?php _e('WizIQ Courses', 'wiziq'); ?></h2>
			<table class = "form-table" >
				<tbody>
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
					<tr>
						<td><a class= "button button-primary " title="<?php _e('Back To Courses', 'wiziq'); ?>" href="<?php echo $return_url; ?>" ><?php _e('Back To Courses','wiziq'); ?></a></td>
						<td><a class= "button button-primary" title="<?php _e('View Classes', 'wiziq'); ?>" href="<?php echo WIZIQ_CLASS_MENU ?>&action=view_course&course_id=<?php echo $course_id; ?>" ><?php _e('View Classes','wiziq'); ?></a></td>
					</tr>
				</tbody>
			</table>
		<?php
	}
}
