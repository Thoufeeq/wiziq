<?php
/*
 * Class contaning functions for content management on front end
 * @since 1.0
 */ 
class Wiziq_Frontend_Content {
	
	/*
	 * Function to redirect to courses page in case of invalid requests
	 * @since 1.0
	 */ 
	function wiziq_back_home () {
		?>
			<script>
				window.location = "<?php echo get_permalink () ; ?>";
			</script>
		<?php
	}//end wiziq_back_home function
	
	
	/*
	 * Function to redirect to a particular page 
	 * @since 1.0
	 */ 
	function wiziq_frontend_url_redirect ( $wiziq_redirect_url ) {
		?>
			<script>
				window.location = "<?php echo $wiziq_redirect_url ; ?>";
			</script>
		<?php
	} // end redirect function 
	
	/*
	 * Permalink structure function 
	 * @since 1.0
	 */ 
	function wiziq_frontend_permalink () {
		if ( get_option('permalink_structure') ) { 
			$qvarsign = '?'; 
		} else {  
			$qvarsign = '&';  
		}
		return $qvarsign;
	}//end frontend permalink structure
	
	
	/*
	 * Function to get list of content from parent id for frontend and backend
	 * Pass pagination as 1 for paginated result and 0 if not paginated result is required
	 * Pass sorting and order by variables for sorting
	 * @since 1.0
	 */ 
	function wiziq_frontend_get_content ( $parent_id , $pagination ,$start, $limit , $sortby , $orderby ) {
		global $wpdb;
		$wiziq_contents = $wpdb->prefix."wiziq_contents";
		$current_user = get_current_user_id();
		if ( $pagination ) {
			if ( "type" == $sortby ) {
				$qry = "select *,substring_index(uploadingfile, '.', -1) as type from $wiziq_contents where parent = '$parent_id' and ( created_by = '$current_user' || created_by = '0' ) order by $sortby $orderby LIMIT $start ,$limit" ;
			}
			else {
				$qry = "select * from $wiziq_contents where parent = '$parent_id' and ( created_by = '$current_user' || created_by = '0' ) order by $sortby $orderby LIMIT $start ,$limit" ;
			}
			
		} else {
			$qry = "select * from $wiziq_contents where parent = '$parent_id' and ( created_by = '$current_user' || created_by = '0' ) order by id " ;
		}
		$wiziq_results = $wpdb->get_results( $qry );
		if ( !empty($wiziq_results) ) {
			return $wiziq_results;
		} else {
			return false;
		}
	} // end function get content
	
	
	/*
	 * Frontend content breadcrumb
	 * Pass parent id and course id  to genereate breadcrumb 
	 * Course id used to check user permissions
	 * @since 1.0
	 */ 
	function wiziq_frontend_content_breadcrumb ( $parent_id, $course_id ) {
		global $wpdb;
		
		//url structure 
		$courses_url = get_permalink();
		$Wiziq_Util = new Wiziq_Util;
		//url structure
		$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
		$content_url = $courses_url.$qvarsign.'ccaction=view_content';
		$breadcrumbarr = array();
		$wiziq_contents = $wpdb->prefix."wiziq_contents";
		do  {
			$qry = "SELECT id , parent , name FROM $wiziq_contents ";
			if ( $parent_id > 1  ) {
				$wherecondition = $parent_id;
			} else {
				$wherecondition = 1;
			}
			$qry .= " where id = $wherecondition";
			$res = $wpdb->get_row ( $qry );
			$parent_id = $res->parent;
			array_push($breadcrumbarr , $res);
			
		} while ( $parent_id > 0 );
		
		$breadcrumbarr = array_reverse($breadcrumbarr);
		echo '<div class= "wiziq_breadcrumb">';
		foreach ($breadcrumbarr as $val=>$brmenu ) {
			$url = $content_url.'&parent='.$brmenu->id.'&course_id='.$course_id;
			if ( !isset ($brcount) ) {
				$brcount = 1;
				echo '<a href= "'.$url.'" >'.$brmenu->name.'</a>';
			}
			else {
				echo ' > <a href= "'.$url.'" >'.$brmenu->name.'</a>';
			}
		}
		echo '</div>';
	} //end breadcrumb function
	
	/*
	 * Function to delete the frontend content
	 * Check using nonce if request is valid request 
	 * @since 1.0
	 */ 
	function wiziq_frontend_delete_content ( $id , $parent_id , $nonce ) {
		//nonce verification
		if ( ! wp_verify_nonce( $_GET['wp_nonce'] , 'delete-content-'.$id  ) ) {
			$this->wiziq_back_home (); 
		}
		global $wpdb;
		global $current_user;
		$wiziq_api_functions  = new wiziq_api_functions ;
		$wiziq_content = new Wiziq_Content;
		
		//Get details of content and check if it is folder or file, 1 for folder and 0 for file
		$content_result = $wiziq_content->wiziq_get_content_by_id ( $id );
		if ( "1" == $content_result->isfolder) {
			$method = "delete_folder";
				//$requestparameters['presenter_id'] = $current_user->ID;
				$requestparameters['presenter_email'] = $current_user->user_email;
				if(!empty($current_user->user_firstname)){
					//$requestparameters["presenter_name"] = $current_user->user_firstname.' '.$current_user->user_lastname;
				} else	{
					//$requestparameters["presenter_name"] = $current_user->display_name;
				}
				if ( !empty ($content_result) ) {
					$requestparameters["folder_path"] = $content_result->folderpath;
				}
				try {
					//Call to api method and pass request parameters
					$deletexml = $wiziq_api_functions->wiziq_content_method($requestparameters , $method );
					$wiziq_contents = $wpdb->prefix."wiziq_contents";
					$deletexmlstatus = $deletexml->attributes();
					if ($deletexmlstatus == 'ok') {
						//delete from database after deleted from the server
						$qry = "delete from $wiziq_contents where id = '$id'";
						$wpdb->query($qry);
						$cancel_erro = '<div class="updated"><p>Folder deleted successfully</p></div>';
						global $myerror;
						$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
					} else {
						//Generate error messages in case of failed
						$errorcode = $deletexml->error->attributes()->code;
						$errormsg = $deletexml->error->attributes()->msg;
						if ("1039" == $errorcode ||"1053" == $errorcode ) {
							$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
						}
						else {
							$error1 = WIZIQ_COM_CONTENT_ERROR;
						}
						$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong> : '.$error1.'</p></div>';
						global $myerror;
						$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
					}
				}
				catch ( Exception $ex ) {
					//Generate error messages in case of exception
					$error = $ex->getMessage();
					$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'. WIZIQ_COM_CONTENT_ERROR.'</p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
				}
		} else {
			$content_id = $content_result->content_id;
			$method = "delete";
			$requestparameters["content_id"] = $content_id;
			try {
				//Call to api method and pass request parameters
				$deletexml = $wiziq_api_functions->wiziq_content_method($requestparameters , $method );
				$deletexmlstatus = $deletexml->attributes();
				if ($deletexmlstatus == 'ok') {
					$deletexmlch = $deletexml->delete;
					$att = 'status';
					$deletexmlchstatus = $deletexmlch->attributes()->$att;
					if ($deletexmlchstatus == 'true') {
						//delete from database after deleted from the server
						$wiziq_contents = $wpdb->prefix."wiziq_contents";
						$qry = "delete from $wiziq_contents where content_id = '$content_id'";
						$wpdb->query($qry);
						$cancel_erro = '<div class="updated"><p>File deleted successfully</p></div>';
						global $myerror;
						$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
					}
				} else if ($deletexmlstatus == "fail") { 
					//Generate error messages in case of failed
					$errorcode = $deletexml->error->attributes()->code;
					$errormsg = $deletexml->error->attributes()->msg;
					if ("1039" == $errorcode ||"1053" == $errorcode ) {
						$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
					}
					else {
						$error1 = WIZIQ_COM_CONTENT_ERROR;
					}
					$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong> : '.$error1.'</p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
				}
			}
			catch ( Exception $ex ) {
				//Generate error messages in case of exception
				$error = $ex->getMessage();
				$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong> '. WIZIQ_COM_CONTENT_ERROR.'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
			}
		}
	} //end frontend delete content function
	
	/*
	 * Function to display content of a particular parent using parent id
	 * @since 1.0
	 */ 
	function wiziq_frontend_view_content ( $parent_id ) {
			$course_id = $_GET['course_id'];
			
			//Url struccture
			
			$user_permissions  = new Wiziq_User_Permissions;
			$wiziq_frontend_courses = new Wiziq_Frontend_Courses;
			$wiziq_content = new Wiziq_Content;
			$wiziq_courses = new Wiziq_Courses;
			$wiziq_Util = new Wiziq_Util;
			
			//url structure
			$courses_url = get_permalink();
			$qvarsign = $wiziq_Util->wiziq_frontend_url_structure();
			
			/*
			 * check Permission for content
			 */ 
			$content_permission = $user_permissions->wiziq_upload_content_permission ( $course_id );
			if ( ! $content_permission ) {
				$this->wiziq_back_home (); 
			} 
			$wiziq_content->wiziq_refresh_content ( $parent_id );
			//Create urls
			$courses_urls = $courses_url.$qvarsign.'action=courses';
			$content_url = $courses_url.$qvarsign.'ccaction=view_content&course_id='.$course_id;
			$upload_content_url = $courses_url.$qvarsign.'ccaction=add_content&course_id='.$course_id.'&parent='.$parent_id;
			
			
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
			$content_result = $this->wiziq_frontend_get_content ( $parent_id , '0' , '0' , '0' , $sortby , $orderby );
			$total_pages = !empty($content_result)?count($content_result):0 ;
			$limit = WIZIQ_PAGINATION_LIMIT;
			$adjacents = 3;
			$targetpage= "";
			$page = isset($_GET['pageno'])?$_GET['pageno']:'';
			if($page) 
				$start = ($page - 1) * $limit; 			//first item to display on this page
			else
				$start = 0;								//if no page var is given, set start to 0
			$targetpage .= get_permalink().$qvarsign.'ccaction=view_content&parent='.$parent_id.'&course_id='.$course_id."&";
			$homepage = get_permalink().$qvarsign.'ccaction=view_content&parent='.$parent_id.'&course_id='.$course_id."&";
			if ( isset ($_GET['sort-by']) && isset ($_GET['order-by']) ) {
				$targetpage .= 'sort-by='.$sortby.'&order-by='.$orderby.'&';
			}
			$pagination =  $wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
			if ( "" == $page ) {
				$page= 1;
			}
			$pagetogo = $page;
			?>
			<div class="front_wiziq userlogin " id="front_wiziq" >
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
							<a href="<?php echo $content_url; ?>" ><?php _e('Content', 'wiziq'); ?></a>
						</h3>
					</li>
					<li>
						<h3>
							<a href="<?php echo $upload_content_url; ?>" ><?php _e('Upload Content', 'wiziq'); ?></a>
						</h3>	
					</li>
				</ul>
				<div class = "clearfix" ></div>
				<h4><?php _e( 'Content List' , 'wiziq' );?></h4>
				<?php $this->wiziq_frontend_content_breadcrumb ( $parent_id , $course_id); ?>
				<?php 
				/*
				 * Display errors if any while deleting the content
				 */ 
					global $myerror;
					if ( is_wp_error( $myerror ) ) {
							$add_error = $myerror->get_error_message('wiziq_content_delete_error');
							if ( $add_error ) {
								echo $add_error;
							}
					}
					//Display success method
					if ( isset ( $_REQUEST['cfsuccess']) )  {
						echo '<div class = "updated" ><p><strong>'.__('Content uploaded successfully','wiziq').'</strong></p></div>';
					}
				?>
				<table class="list_content" >
						<tr>
							<th>
								<?php
								/***** Sorting functionality *****/ 
								 if ( isset ( $_GET['sort-by'] ) && $_GET [ 'order-by']  ) :
								 
										if ( "name" == $_GET['sort-by'] ) :
											if ( "asc" == $_GET['order-by']) {
												$nameclass = "sorting-up";
												$ordering = "desc";
												$nametitle = __( 'Click to sort by descending order' ,'wiziq');
											} else {
												$nameclass = "sorting-down";
												$ordering = "asc";
												$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
											}
											$typeclass = "sorting-up";
											$fileordering = "asc";
											$filetitle = __( 'Click to sort by ascending order' ,'wiziq');
											
										elseif ( "type" == $_GET['sort-by'] )  :
											if ( "asc" == $_GET['order-by']) {
												$typeclass = "sorting-up";
												$fileordering = "desc";
												$filetitle = __( 'Click to sort by descending order' ,'wiziq');
											} else {
												$typeclass = "sorting-down";
												$fileordering = "asc";
												$filetitle = __( 'Click to sort by ascending order' ,'wiziq');
											}
											$nameclass = "sorting-up";
											$ordering = "asc";
											$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
										endif;
								else :
									$typeclass = "sorting-up";
									$fileordering = "asc";
									$filetitle = __( 'Click to sort by ascending order' ,'wiziq');
									$nameclass = "sorting-up";
									$ordering = "asc";
									$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
									
								endif;
								?>
								<div>
									<a href = "<?php echo $homepage.'sort-by=name&order-by='.$ordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e( 'Content Name' , 'wiziq' ); ?></span>
									<?php if (isset ( $_GET[ 'sort-by' ] ) && "name" == $_GET[ 'sort-by' ] ) : ?>
										<div class = "<?php echo $nameclass; ?>" ></div>
									<?php endif; ?>
									</a>
								</div>
							</th>
							<th>
								<div>
									<a href = "<?php echo $homepage.'sort-by=type&order-by='.$fileordering; ?>" title = "<?php echo $filetitle; ?>" >
										<span><?php _e( 'Type' , 'wiziq' ); ?></span>
										<?php if (isset ( $_GET[ 'sort-by' ] ) && "type" == $_GET[ 'sort-by' ]): ?>
											<div class = "<?php echo $typeclass; ?>" ></div>
										<?php endif; ?>
									</a>
								</div>
							</th>
							<th><span><?php _e( 'Manage Content' , 'wiziq' ); ?></span></th>
							<th><span><?php _e( 'Inside' , 'wiziq' ); ?></span></th>
							<th>
								<span><?php _e( 'Status' , 'wiziq' ); ?></span>
								<a href="<?php echo $targetpage; ?>pageno=<?php echo $pagetogo; ?>&refresh_content" title = "<?php _e('Refresh', 'wiziq'); ?>" >
									<img class ="content_images"  class = "content-images" src= "<?php echo WIZIQ_PLUGINURL_PATH.'images/refresh20.png' ; ?>" alt ="<?php _e( 'Refresh' , 'wiziq' ); ?>" />
								</a>
							</th>
						</tr>
						<?php
						/*
						 * Get content and display
						 */
						$content_result = $this->wiziq_frontend_get_content ( $parent_id , '1' , $start , $limit, $sortby , $orderby );
						if ( !empty ($content_result) ) :
							foreach ( $content_result as $res ) :
								$no_of_folders = $wiziq_content->wiziq_count_folders ( $res->id );
								$no_of_files = $wiziq_content->wiziq_count_files ( $res->id );
								$totalcontent = $no_of_files + $no_of_folders;
								$contentpage = get_permalink().$qvarsign."ccaction=view_content&parent=".$res->id."&course_id=".$course_id;
								$delete_content_nonce = wp_create_nonce( 'delete-content-'.$res->id );
								?>
								<tr>
									<td>
										<?php if ( $res->isfolder ) : ?>
											<a href= "<?php echo $contentpage; ?>" ><?php echo $res->name; ?></a>
										<?php else : ?>
											<?php echo $res->name; ?>
										<?php endif; ?>
									</td>
									<td>
										<?php
											//Check if folder or file and display icon accordingly
											if ( $res->isfolder ) :
												echo '<img class = "content_images" src= "'.WIZIQ_PLUGINURL_PATH.'images/20/folder.png" alt = "'.__('Folder' , 'wiziq').'" />';
											else :
												$filetype = wp_check_filetype($res->uploadingfile);
												$extenstion =  $filetype['ext'];
												echo '<img class = "content_images" src= "'.WIZIQ_PLUGINURL_PATH.'images/20/'.$extenstion.'.png" alt = "'.$extenstion.'" />';
											endif;
										 ?>
									</td>
									<td>
										<a title = "<?php _e( 'Delete' , 'wiziq' ); ?>" class = "wiziq_delete_content" id = "content_<?php echo $res->id; ?>" href="<?php echo $targetpage."&delete_content=".$res->id."&wp_nonce=".$delete_content_nonce; ?>">
											<img class = "content_images" src= "<?php echo WIZIQ_PLUGINURL_PATH.'images/delete15.png'; ?>" alt = "<?php _e( 'Delete' , 'wiziq' ); ?>"/>
										</a>
										<input id = "wiziq_delete_content_hidden_<?php echo $res->id; ?>" type = "hidden" value ="<?php  echo $totalcontent ; ?>" name= "delete_content" />
									</td>
									<td>
										<?php
											if ( $res->isfolder ) :
												echo $no_of_folders.' '.__('Folder(s)', 'wiziq' );
												echo ' , '.$no_of_files.' '. __('File(s)' , 'wiziq' ) ;
											endif;
										?>
									</td>
									<td>
										<?php echo $res->status; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan = "4" ><?php _e( 'No content available' , 'wiziq' ); ?></td>
							</tr>
						<?php endif; ?>
						<tr>
							<th><span><?php _e( 'Content Name' , 'wiziq' ); ?></span></th>
							<th><span><?php _e( 'Type' , 'wiziq' ); ?></span></th>
							<th><span><?php _e( 'Manage Content' , 'wiziq' ); ?></span></th>
							<th><span><?php _e( 'Inside' , 'wiziq' ); ?></span></th>
							<th><span><?php _e( 'Status' , 'wiziq' ); ?></span></th>
						</tr>
				</table>
				<?php echo $pagination; ?>
			</div>
			<div class = "wiziq_hide" >
				<span id = "wiziq_are_u_sure" ><?php _e('Are you sure, you want to delete','wiziq');?></span>
				<span id = "delete_inner_content" ><?php _e('Please delete inner content first','wiziq');?></span>
				<span id = "wiziq_select_content" ><?php _e('Please select content to delete','wiziq');?></span>
			</div>
			</div>
			<?php
	} // end frontend view content function
	
	
	
	/*
	 * Function to upload content frontend
	 * @since 1.0
	 */ 
	function wiziq_frontend_add_content_form ( $parent_id , $course_id ) {
			
			
			/*
			 * check Permission for content
			 */ 
			$user_permissions  = new Wiziq_User_Permissions;
			$wiziq_content = new Wiziq_Content;
			$content_permission = $user_permissions->wiziq_upload_content_permission ( $course_id );
			if ( ! $content_permission ) {
				$this->wiziq_back_home (); 
			} 
			$parent_result = $wiziq_content->wiziq_get_content_by_id ( $parent_id );
			$name = $parent_result->name;
			$nametitle = __( 'Upload content in folder' , 'wiziq' )." : ".$name;
			$course_id = $_GET['course_id'];
			
			//Url structure
			$courses_url = get_permalink();
			$Wiziq_Util = new Wiziq_Util;
		//url structure
			$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
			$content_url = $courses_url.$qvarsign.'ccaction=view_content&course_id='.$course_id.'&parent='.$parent_id;
			
			?>
				<div class = "wiziq_add_content" >
					<h3><?php echo $nametitle; ?></h3>
					<!-- Breadcrumb function -->
					<?php $this->wiziq_frontend_content_breadcrumb ( $parent_id, $course_id ); ?>
					
					
					
					<div class="frontaddform" >
					<?php 
						/*
						 * Display errors if any while uploading the content
						 */ 
						global $myerror;
						if ( is_wp_error( $myerror ) ) {
							$add_error = $myerror->get_error_message('wiziq_content_upload_error');
							if ( $add_error ) {
								echo $add_error;
							}
						} 
					?>
						<form method = "post" enctype="multipart/form-data" >
							<?php 
								$add_content_nonce = 'wiziq-add-content-'.$parent_id.'-'.$course_id;
							?>
							<?php wp_nonce_field($add_content_nonce,'wp_nonce') ?>
							<table class = "form-table" >
									<tr>
										<td class = "wiziq-content-upload">
											<h3><?php _e('Upload Content', 'wiziq'); ?></h3>
											<div class= "left-arrow-wiziq" >
												<img  class = "content_images" src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/arrow-up-32.png' ; ?>"  />
											</div>
											<div class= "right-arrow-wiziq" >
												<img class = "content_images" src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/arrow-down-32.png' ; ?>"  />
											</div>
										</td>
										<td class = "wiziq-content-upload">
											<h3><?php _e('Create Folder', 'wiziq'); ?></h3>
											<div class= "right-arrow-wiziq" >
												<img  class = "content_images" src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/arrow-up-32.png' ; ?>"  />
											</div>
											<div class= "left-arrow-wiziq" >
												<img class = "content_images" src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/arrow-down-32.png' ; ?>"  />
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<div class = "upload-content" >
												<?php _e( 'Content name' , 'wiziq' ); ?>
												<input maxlength= "70" type = "text" class = "regular-text" id = "content_name" name="file_title" />
												<div class = "wiziq_error" id = "content_nae_err" ></div>
											</div>
										</td>
										<td>
											<div class = "folder-content" >
												<?php _e( 'Folder name' , 'wiziq' ); ?><span class = "wiziq_required" >*</span>
												<input maxlength= "70" type = "text" class = "regular-text" id = "folder_name" name="folder_name" />
												<div class = "wiziq_error" id = "content_name_err" ></div>
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<div class = "upload-content" >
												<?php _e( 'Upload file' , 'wiziq' ); ?><span class = "wiziq_required" >*</span>
												<input type="file" name="uploadingfile" id="uploadingfile">
												<div class = "wiziq_error" id = "content_file_err" ></div>
											</div>
										</td>
										<td>
											<div class = "folder-content" >
												&nbsp;
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<div class = "upload-content" >
												<?php _e( 'Allowed file type' , 'wiziq' ); ?>
												<img class = "supported-img content_images" src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/supported.gif' ; ?>"  /> 
											</div>
										</td>
										<td>
											<div class = "folder-content" >
												&nbsp;
											</div>
										</td>
									</tr>
							</table>
							<input type= "hidden" name= "folderpath" value = "<?php echo $parent_result->folderpath ; ?>" /> 
							<input type = "hidden" value = "file" id = "content_type" name = "content_type" />
							<input class= "button button-primary" id = "wiziq_add_content_form" type = "Submit" name = "wiziq_front_add_content" value="<?php _e('Save','wiziq') ?>" /> 
							<a class= "button back button-primary" id = "wiziq_cancel_course" href = "<?php echo $content_url;?>" ><?php _e('Cancel','wiziq') ?></a>
						</form>
						<div class = "wiziq_hide" >
							<span id = "content_folder_wrong" ><?php _e('Please enter folder name.','wiziq');?></span>
							<span id = "content_file_wrong" ><?php _e('Please select file to upload.','wiziq');?></span>
						</div>
					</div>
				</div>
			<?php
	 }
	 
	 /*
	  * Function to add content on front end
	  * @since 1.0 
	  */ 
	function wiziq_frontend_add_content ( $parent_id , $content )  {
		global $wpdb;
		global $current_user;
		$wiziq_content = new Wiziq_Content;
		$Wiziq_Util = new Wiziq_Util;
		$wiziq_api_functions  = new wiziq_api_functions ;
		
		$content_result = $wiziq_content->wiziq_get_content_by_id ( $parent_id );
		
		$course_id = $_GET['course_id'];
		// Url structure to redirect to a page
		$courses_url = get_permalink();
		
		//url structure
		$qvarsign = $Wiziq_Util->wiziq_frontend_url_structure();
		$content_url = $courses_url.$qvarsign.'ccaction=view_content&course_id='.$course_id.'&parent='.$parent_id;
		
		if ( !empty ($content_result) ) {
			$folder_path = $content_result->folderpath;
		} else {
			$folder_path = "";
		}
		//Check folder is being uploaded
		if ( "folder" == $content['content_type']) {
			if ( ! $folder_path ) {
				$requestparameters["folder_path"] = $content['folder_name'];
			} else {
				$requestparameters["folder_path"] = $folder_path. "/" . $content['folder_name'];
			}
			$method = "create_folder";
			//$requestparameters['presenter_id'] = $current_user->ID;
			$requestparameters['presenter_email'] = $current_user->user_email;
			if(!empty($current_user->user_firstname)){
				//$requestparameters["presenter_name"] = $current_user->user_firstname.' '.$current_user->user_lastname;
			} else	{
				//$requestparameters["presenter_name"] = $current_user->display_name;
			}
			$requestparameters["app_version"] = WIZIQ_APP_VERSION;
			try {
				//Call to api method and pass request parameters
				$createfolderxml = $wiziq_api_functions->wiziq_content_method($requestparameters , $method );
				$createfolderxmlstatus = $createfolderxml->attributes();
				if ($createfolderxmlstatus == 'ok') {
					$createfolderxmlch = $createfolderxml->create_folder;
					 $createfolderxmlstatus = (string)$createfolderxmlch->attributes()->status;
				   if ($createfolderxmlstatus == 'true') {
						$response['livestatus'] = 'ok';
						$response['path'] =   $createfolderxmlch->folder->path;
						//Insert into database
						$wiziq_contents = $wpdb->prefix."wiziq_contents";
						$qry = "insert into $wiziq_contents
						( status, created_by , isfolder,
						name, parent, content_id, uploadingfile, folderpath)
						values ( '', '$current_user->ID', '1' ,
						'".$content['folder_name']."', '$parent_id' , '0', '', 
						'".$response['path']."'
						)
						";
						$wpdb->query($qry);
						//Redirect after success
						$wiziq_redirect_url = $content_url."&cfsuccess";
						$this->wiziq_frontend_url_redirect ( $wiziq_redirect_url );
					} else {
					}
				} else {
					$errorcode = $createfolderxml->error->attributes()->code;
					$errormsg = $createfolderxml->error->attributes()->msg;
					if ("1001" == $errorcode ||"1002" == $errorcode || "1013" == $errorcode || "1014" == $errorcode || "1030" == $errorcode || "1031" == $errorcode || "1039" == $errorcode || "1040" == $errorcode || "1045" == $errorcode || "1046" == $errorcode || "1047" == $errorcode || "1048" == $errorcode || "1049" == $errorcode || "1052" == $errorcode || "1054" == $errorcode || "1055" == $errorcode ) {
						$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
					}
					else {
						$error1 = WIZIQ_COM_CONTENT_ERROR;
					}
					$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.$error1.'</p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_content_upload_error', $cancel_erro ); 
				}
			} catch( Exception $e) {
				$error = $e->getMessage();
				$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.'</strong>'. WIZIQ_COM_CONTENT_ERROR.'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_content_upload_error', $cancel_erro ); 
			}
		} 
		// If file is being uploaded
		else {
			$api_result = $wiziq_api_functions->wiziq_content_upload($_POST ,$_FILES , $parent_id);
			if ( $api_result ) {
				$this->wiziq_frontend_url_redirect ( $content_url );
			}
		}
	}// end frontend add content function
}
