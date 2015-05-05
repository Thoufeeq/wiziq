<?php
/*
 * Content functions
 * @since 1.0
 */ 
 
class Wiziq_Content {
	
	
	/*
	 * Function to get content of a file or folder by id for frontend and backend
	 * @since 1.0
	 */
	function wiziq_get_content_by_id ( $id ) {
		global $wpdb;
		$wiziq_contents = $wpdb->prefix."wiziq_contents";
		$qry = "select * from $wiziq_contents where id = '$id' " ;
		return $wpdb->get_row($qry);
	}//end function get content by id
	
	/*
	 * Function to get list of content from parent id for frontend and backend
	 * Pass pagination as 1 for paginated result and 0 if not paginated result is required
	 * Pass sorting and order by variables for sorting
	 * @since 1.0
	 */ 
	function wiziq_get_content ( $parent_id , $pagination ,$start, $limit , $sortby , $orderby ) {
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
	 * Function to count folders for a parent for frontend and backend
	 * pass parent id
	 * @since 1.0
	 */ 
	function wiziq_count_folders ( $parent_id) {
		global $wpdb;
		$wiziq_contents = $wpdb->prefix."wiziq_contents";
		$qry = "select count( isfolder ) from $wiziq_contents where isfolder = '1' and parent = '$parent_id' ";
		$res = $wpdb->get_var($qry);
		if ( $res ) {
			return $res;
		}
		else {
			return 0;
		}
	} //end function count folders
	
	
	/*
	 * Function to count files for a parent for frontend and backend
	 * pass parent id
	 * @since 1.0
	 */ 
	function wiziq_count_files ( $parent_id ) {
		global $wpdb;
		$wiziq_contents = $wpdb->prefix."wiziq_contents";
		$qry = "select count( isfolder ) from $wiziq_contents where isfolder = '0' and parent = '$parent_id' ";
		$res = $wpdb->get_var($qry);
		if ( $res ) {
			return $res;
		}
		else {
			return 0;
		}
	} //end count files function 
	
	
	/*
	 * Function to add content for back end only
	 * pass parent id and post array
	 * @since 1.0
	 */
	function wiziq_add_content ( $parent_id , $content )  {
		global $wpdb;
		global $current_user;
		$wiziq_api_functions  = new wiziq_api_functions ;
		$content_result = $this->wiziq_get_content_by_id ( $parent_id );
		if ( !empty ($content_result) ) {
			$folder_path = $content_result->folderpath;
		} else {
			$folder_path = "";
		}
		//Check if folder is uploaded or file
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
				//call to api function and pass request parameters
				$createfolderxml = $wiziq_api_functions->wiziq_content_method($requestparameters , $method );
				$createfolderxmlstatus = $createfolderxml->attributes();
				if ($createfolderxmlstatus == 'ok') {
					$createfolderxmlch = $createfolderxml->create_folder;
					 $createfolderxmlstatus = (string)$createfolderxmlch->attributes()->status;
				   if ($createfolderxmlstatus == 'true') {
						$response['livestatus'] = 'ok';
						$response['path'] =   $createfolderxmlch->folder->path;
						//echo $response['path']." is the folder path" ;
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
						?>
							<script>
								window.location = "<?php echo WIZIQ_CONTENT_MENU."&parent=".$parent_id ?>&fsuccess";
							</script>
						<?php
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
					$cancel_erro = '<div class="error"><p><strong>ERROR </strong>'.$error1.'</p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_content_upload_error', $cancel_erro ); 
				}
			} catch( Exception $e) {
				$cancel_erro = '<div class="error"><p><strong>ERROR</strong>'. WIZIQ_COM_CATCH.'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_content_upload_error', $cancel_erro ); 
			}
		} else {
			//File uploading
			$api_result = $wiziq_api_functions->wiziq_content_upload($_POST ,$_FILES , $parent_id);
			if ( $api_result ) {
				//redirect if file uploaded successfully
				?>
					<script>
						window.location = "<?php echo WIZIQ_CONTENT_MENU.'&parent='.$parent_id; ?>";
					</script>
				<?php
			}
		}
	} //end add content function for back end
	
	
	/*
	 * Function to delete content for backend only
	 * Pass post array and parent id to delete 
	 * @since 1.0
	 */  
	function wiziq_delete_content ( $content , $parent_id ) {
		global $wpdb;
		global $current_user;
		$wiziq_api_functions  = new wiziq_api_functions ;
		foreach ( $content['content-checkbox'] as $checked ) {
			//Get details of content and check if it is folder or file, 1 for folder and 0 for file
			$content_result = $this->wiziq_get_content_by_id ( $checked );
			if ( "1" == $content_result->isfolder) {
				$no_of_folders = $this->wiziq_count_folders ( $checked );
				$no_of_files = $this->wiziq_count_files ( $checked );
				$totalcontent = $no_of_files + $no_of_folders;
				//check if folder 
				if ( ! $totalcontent ) 
				{
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
							$qry = "delete from $wiziq_contents where id = '$checked'";
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
							$cancel_erro = '<div class="error"><p><strong>ERROR</strong> : '.WIZIQ_COM_CATCH.'</p></div>';
							global $myerror;
							$myerror = new WP_Error( 'wiziq_content_upload_error', $cancel_erro ); 
						}
					}
					catch ( Exception $ex ) {
						//Generate error messages in case of failed
						$error = $ex->getMessage();
						$cancel_erro = '<div class="error"><p><strong>ERROR</strong>'. WIZIQ_COM_CATCH.'</p></div>';
						global $myerror;
						$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
					}
				}
			} else {
				// Delete files
				$content_result = $this->wiziq_get_content_by_id ( $checked );
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
							$wiziq_contents = $wpdb->prefix."wiziq_contents";
							$qry = "delete from $wiziq_contents where content_id = '$content_id'";
							$wpdb->query($qry);
							$cancel_erro = '<div class="updated"><p>File deleted successfully</p></div>';
							global $myerror;
							$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
						}
					} else if ($deletexmlstatus == "fail") { 
						$errorcode = $deletexml->error->attributes()->code;
						$errormsg = $deletexml->error->attributes()->msg;
						if ("1039" == $errorcode ||"1053" == $errorcode ) {
							$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
						}
						else {
							$error1 = WIZIQ_COM_CONTENT_ERROR;
						}
						$cancel_erro = '<div class="error"><p><strong>ERROR</strong> : '.$error1.'</p></div>';
						global $myerror;
						$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
					}
				}
				catch ( Exception $ex ) {
					$error = $ex->getMessage();
					$cancel_erro = '<div class="error"><p><strong>ERROR</strong>'. WIZIQ_COM_CATCH.'</p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_content_delete_error', $cancel_erro ); 
				}
			}
		}
	} // end delete content function
	
	
	/*
	 * Breadcrumb function for backend only
	 * pass parent id
	 * @since 1.0
	 */ 
	function wiziq_content_breadcrumb ( $parent_id ) {
		global $wpdb;
		$breadcrumbarr = array();
		$wiziq_contents = $wpdb->prefix."wiziq_contents";
		do  {
			$qry = "SELECT id , parent , name FROM $wiziq_contents where id = $parent_id ";
			$res = $wpdb->get_row ( $qry );
			$parent_id = $res->parent;
			array_push($breadcrumbarr , $res);
		} while ( $parent_id > 0 );
		
		$breadcrumbarr = array_reverse($breadcrumbarr);
		echo '<div class= "wiziq_breadcrumb">';
		foreach ($breadcrumbarr as $brmenu ) {
			if ( !isset ($brcount) ) {
				$brcount = 1;
				echo '<a href= "'.WIZIQ_CONTENT_MENU.'&parent='.$brmenu->id.'" >'.$brmenu->name.'</a>';
			}
			else {
				echo ' > <a href= "'.WIZIQ_CONTENT_MENU.'&parent='.$brmenu->id.'" >'.$brmenu->name.'</a>';
			}
		}
		echo '</div>';
	} // end wiziq content breadcrumb
	
	
	/*
	 * Function to refresh the content for frontend and backend
	 * @since 1.0
	 */
	function wiziq_refresh_content ( $parent_id ) {
		
		global $wpdb;
		global $current_user;
		$wiziq_contents = $wpdb->prefix."wiziq_contents";
		$wiziq_Util = new Wiziq_Util;
		
		$totalfiles = $this->wiziq_count_files ( $parent_id );
		$wiziq_api_functions  = new wiziq_api_functions ;
		$content_result = $this->wiziq_get_content_by_id ( $parent_id );
		$method = "list";
		if ( !empty ($content_result) ) {
			$requestparameters["folder_path"] = $content_result->folderpath;
		}
		$requestparameters["page_size"] = $totalfiles;
		$requestparameters['presenter_id'] = $current_user->ID;
		if(!empty($current_user->user_firstname)){
			$requestparameters["presenter_name"] = $current_user->user_firstname.' '.$current_user->user_lastname;
		} else	{
			$requestparameters["presenter_name"] = $current_user->display_name;
		}
		 try {
			 //Call to method to refresh the status of files
			$contentlistxml = $wiziq_api_functions->wiziq_content_method($requestparameters , $method );
			$contentlistxmlstatus = $contentlistxml->attributes();
			if ($contentlistxmlstatus == 'ok') {
                    $recordlist = $contentlistxml->list->record_list;
                    foreach ($recordlist->children() as $value) {
                        $status =  $value->status;
                        $valuecontentid =  $value->content_id;
                        if ( "available" == $status )  {
							//Update the status if available
							$qry = "update $wiziq_contents set status = '$status' where content_id = '$valuecontentid' ";
							$wpdb->query($qry);
						} else if ( "failed" == $status ) {
							//delete if status if failed 
							$qry = "delete from $wiziq_contents where where content_id = '$valuecontentid' ";
							$wpdb->query($qry);
						}
					}
				}
		}
		catch (Exception $ex) {
			//Generate error message
			$error = $ex->getMessage();
			$cancel_erro = '<div class="error"><p><strong>ERROR </strong>'. WIZIQ_COM_CATCH.'</p></div>';
			global $myerror;
			$myerror = new WP_Error( 'wiziq_content_upload_error', $cancel_erro ); 
		}
		
	}  
	
	/*
	 * Function to view content for backend only
	 * @since 1.0
	 */ 
	
	function wiziq_view_content ( $parent_id ) {
		
		if ( isset( $_POST ) && isset ( $_POST['delete-content'] ) && wp_verify_nonce( $_POST['delete-content'] , 'delete-content-'.$parent_id  ) ) {
			$this->wiziq_delete_content ( $_POST , $parent_id);
			
		}
		/* Sorting method */
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
		
		$this->wiziq_refresh_content ( $parent_id );
		$add_content_nonce = wp_create_nonce( 'add-content-'.$parent_id );
		$wiziq_Util = new Wiziq_Util;
		$content_result = $this->wiziq_get_content ( $parent_id , '0' , '0' , '0' , $sortby , $orderby );
		$total_pages = !empty($content_result)?count($content_result):0 ;
		$limit = WIZIQ_PAGINATION_LIMIT;
		$adjacents = 3;
		$page = isset($_GET['pageno'])?$_GET['pageno']:'';
		if($page) 
			$start = ($page - 1) * $limit; 			//first item to display on this page
		else
			$start = 0;								//if no page var is given, set start to 0
		if ( 1 == $parent_id ) {
			$targetpage = "?page=wiziq_content&";
		}
		else {
			$targetpage = "?page=wiziq_content&parent=$parent_id&";
		}
		if ( isset ($_GET['sortby']) && isset ($_GET['orderby']) ) {
			$targetpage .= 'sortby='.$_GET['sortby'].'&orderby='.$_GET['orderby'].'&';
		}
		$pagination =  $wiziq_Util->custom_pagination($page,$total_pages,$limit,$adjacents,$targetpage);
		
		$result = $this->wiziq_get_content ( $parent_id , '1' , $start , $limit, $sortby , $orderby );
		
		?>
		<h2><?php _e('WizIQ Content', 'wiziq'); ?><a class = "add-new-h2"  href= "<?php echo WIZIQ_CONTENT_MENU; ?>&contentact=add_content&parent=<?php echo $parent_id;?>&wp_nonce=<?php echo $add_content_nonce; ?>" ><?php _e('Upload Content', 'wiziq'); ?></a></h2>
		<?php
		if ( isset( $_GET['fsuccess'] ) ) {
			echo '<div class = "updated" ><p><strong>'.__('Folder created successfully','wiziq').'</strong></p></div>';
		}
		
		
		//display if any errors
			global $myerror;
			if ( is_wp_error( $myerror ) ) {
				$add_error = $myerror->get_error_message('wiziq_content_delete_error');
				if ( $add_error ) {
					echo $add_error;
				}
			} 
			
			
		$pagetogo = $page;
		if ( $parent_id  ) :
		
			$parent_result = $this->wiziq_get_content_by_id ( $parent_id );
			$name = $parent_result->name;
			
		endif;
		?>
		<?php
		//Call to breadcrumb function
		$this->wiziq_content_breadcrumb ( $parent_id );
		?>
		<form method = "post" >
			<?php wp_nonce_field( 'delete-content-'.$parent_id, 'delete-content' ); ?>
			<div class = "tablenav top">
				<div class="alignleft actions bulkactions">
					<select name="multiple_actions" id= "delete_action_content" >
						<option value = "-1" ><span><?php _e('Bulk Actions', 'wiziq'); ?></span></option>
						<option value = "1" ><span><?php _e('Delete', 'wiziq'); ?></span></option>
					</select>
					<input id="delete-content" class="button action delete-content" type="submit" value="<?php _e('Apply', 'wiziq'); ?>" name="">
				</div>
			</div>
			<table class= "wp-list-table widefat fixed pages" >
				<thead>
					<tr>
						<th class = "manage-column column-cb check-column" >
							<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th id = "content_name" class = "manage-column sortable desc" >
							
							<?php
							/***** Sorting functionality *****/ 
							 if ( isset ( $_GET['sortby'] ) && $_GET [ 'orderby']  ) :
							 
									if ( "name" == $_GET['sortby'] ) :
										if ( "asc" == $_GET['orderby']) {
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
									elseif ( "type" == $_GET['sortby'] )  :
										if ( "asc" == $_GET['orderby']) {
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
								$fileordering = "asc";
								$filetitle = __( 'Click to sort by ascending order' ,'wiziq');
								$nameclass = "sorting-up";
								$ordering = "asc";
								$nametitle = __( 'Click to sort by ascending order' ,'wiziq');
								
							endif;
							?>
							<div class = "" >
								<a href = "<?php echo WIZIQ_CONTENT_MENU.'&parent='.$parent_id.'&sortby=name&orderby='.$ordering; ?>" title = "<?php echo $nametitle; ?>" >
									<span><?php _e('Content Name', 'wiziq'); ?></span>
									<?php if (isset ( $_GET[ 'sortby' ] ) && "name" == $_GET[ 'sortby' ] ) : ?>
										<div class = "<?php echo $nameclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</div>
						</th>
						<th id = "content_type" class = "manage-column sortable" >
							<div>
								<a href = "<?php echo WIZIQ_CONTENT_MENU.'&parent='.$parent_id.'&sortby=type&orderby='.$fileordering; ?>" title = "<?php echo $filetitle; ?>" >
									<?php _e('Type', 'wiziq'); ?>
									<?php if (isset ( $_GET[ 'sortby' ] ) && "type" == $_GET[ 'sortby' ]): ?>
										<div class = "<?php echo $typeclass; ?>" ></div>
									<?php endif; ?>
								</a>
							</div>
						</th>
						<th id = "course_manage_courses" class = "manage-column" >
							<?php _e('Inside', 'wiziq'); ?>
						</th>
						<th id = "course_created_by" class = "manage-column" >
							<?php _e('Status', 'wiziq'); ?>
							<a href="<?php echo WIZIQ_CONTENT_MENU;?>&parent=<?php echo $parent_id ; ?>&pageno=<?php echo $pagetogo; ?>&refresh">
								<img title= "<?php _e('Refresh', 'wiziq'); ?>" class = "content-images" src= "<?php echo plugins_url( 'images/refresh20.png' , dirname(__FILE__) ) ; ?>" alt ="<?php _e( 'Refresh' , 'wiziq' ); ?>" />
							</a>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class = "manage-column column-cb check-column" >
							<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All' , 'wiziq' ); ?></label>
							<input id="cb-select-all-1" type="checkbox">
						</th>
						<th id = "content_name" class = "manage-column sortable desc" >
							<span><?php _e('Content Name', 'wiziq'); ?></span>
						</th>
						<th id = "content_type" class = "manage-column" >
							<?php _e('Type', 'wiziq'); ?>
						</th>
						<th id = "" class = "manage-column" >
							<?php _e('Inside', 'wiziq'); ?>
						</th>
						<th id = "" class = "manage-column" >
							<?php _e('Status', 'wiziq'); ?>
						</th>
					</tr>
				</tfoot>
				<tbody>
				<?php
					
					if ( $result ) :
						$countrow = 0;
						foreach ( $result as $res )  :
							$no_of_folders = $this->wiziq_count_folders ( $res->id );
							$no_of_files = $this->wiziq_count_files ( $res->id );
							$totalcontent = $no_of_files + $no_of_folders;
							$countrow++;
							if( "1" == $countrow) 
							{ 
								$rowclass = "alternate iedit content";
							} 
							else 
							{
								$countrow = "0";
								$rowclass = "iedit content";
							}
							?>
								<tr id = "content-<?php echo $res->id; ?>" class = "<?php echo $rowclass; ?>" >
									<th class="check-column" scope="row">
										<label class="screen-reader-text" ><?php _e( 'Select' , 'wiziq' );?> <?php echo $res->name; ?></label>
										<input id="cb-select-<?php echo $res->id; ?>" type="checkbox" value="<?php echo $res->id; ?>" name = "content-checkbox[]" value= "<?php echo $res->id; ?>">
										<div class="locked-indicator"></div>
										<input type = "hidden" value = "<?php echo $totalcontent; ?>" class = "hidden-content" id= "hid-content-<?php echo $res->id;?>" />
									</th>
									<td>
										<?php if ( $res->isfolder ) : ?>
										<a href = "<?php echo WIZIQ_CONTENT_MENU ?>&parent=<?php echo $res->id; ?>" >
											<?php echo $res->name; ?>
										</a>
										<?php else : ?>
											<?php echo $res->name; ?>
										<?php endif; ?>
									</td>
									<td>
										<?php
											
											if ( $res->isfolder ) :
												echo '<img src= "'.WIZIQ_PLUGINURL_PATH.'images/20/folder.png" alt = "'.__('Folder' , 'wiziq').'" />';
											else :
												$filetype = wp_check_filetype($res->uploadingfile);
												$extenstion =  $filetype['ext'];
												echo '<img src= "'.WIZIQ_PLUGINURL_PATH.'images/20/'.$extenstion.'.png" alt = "'.$extenstion.'" />';
											endif;
										 ?>
									</td>
									<td>
										<?php
											if ( $res->isfolder ) :
												echo $no_of_folders.' '.__('Folder(s)', 'wiziq' );
												echo ' , '.$no_of_files.' '. __('File(s)' , 'wiziq' ) ;
											endif;
										?>
									</td>
									<td><?php echo _e("$res->status" , 'wiziq' ); ?></td>
								</tr>
							<?php
						endforeach;
					else :
						?>
							<tr>
								<td colspan = "4" > <?php _e('No Content Available', 'wiziq'); ?> </td>
							</tr>
						<?php
					endif; 
				?>
				</tbody>
			</table>
			<div class= "tablenav bottom">
				<?php 
					if ( $result ) {
						echo $pagination;
					}
				?>
			</div>
			<div class = "wiziq_hide" >
				<span id = "wiziq_are_u_sure" ><?php _e('Are you sure, you want to delete','wiziq');?></span>
				<span id = "delete_inner_content" ><?php _e('Please delete inner content first','wiziq');?></span>
				<span id = "wiziq_select_content" ><?php _e('Please select content to delete','wiziq');?></span>
			</div>
		</form>
		<?php
	}
	
	/*
	 * Form to add content for backend only
	 * @since 1.0
	 */ 
	function wiziq_add_content_form () {
		$parent_id = $_GET['parent'];
		if ( ! wp_verify_nonce( $_GET['wp_nonce'] , 'add-content-'.$parent_id  ) ) {
			?>
			<script>
				window.location = "<?php echo WIZIQ_CONTENT_MENU; ?>";
			</script>
		<?php
		}
		$parent_result = $this->wiziq_get_content_by_id ( $parent_id );
		$name = $parent_result->name;
		echo '<h2> '.__('Upload content in folder', 'wiziq').' : '.$name.'</h2>';
		if ( $parent_id > 1 ) {
			//Breadcrumb function
			$this->wiziq_content_breadcrumb ( $parent_id );
		}
		
		?>
		<?php 
			//display if any errors while uploading the form
			global $myerror;
			if ( is_wp_error( $myerror ) ) {
					$add_error = $myerror->get_error_message('wiziq_content_upload_error');
					if ( $add_error ) {
						echo $add_error;
					}
			} 
		?>
		<form method = "post" enctype="multipart/form-data" class = "add-content-form" >
			<table class = "form-table" >
				<tbody>
					<tr>
						<td class = "wiziq-content-upload" >
							<h3><?php _e('Upload Content', 'wiziq'); ?></h3>
							<div class= "left-arrow-wiziq" >
								<img  src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/arrow-up-32.png' ; ?>"  />
							</div>
							<div class= "right-arrow-wiziq" >
								<img src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/arrow-down-32.png' ; ?>"  />
							</div>
						</td>
						<td class = "wiziq-content-upload">
							<h3><?php _e('Create Folder', 'wiziq'); ?></h3>
							<div class= "right-arrow-wiziq" >
								<img  src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/arrow-up-32.png' ; ?>"  />
							</div>
							<div class= "left-arrow-wiziq" >
								<img src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/arrow-down-32.png' ; ?>"  />
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
								<?php _e( 'Folder name' , 'wiziq' ); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span>
								<input maxlength= "70" type = "text" class = "regular-text" id = "folder_name" name="folder_name" />
								<div class = "wiziq_error" id = "content_name_err" ></div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class = "upload-content" >
								<?php _e( 'Upload file' , 'wiziq' ); ?><span class="description"> (<?php _e('required', 'wiziq' ); ?>)</span>
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
							<div class = "upload-content supported" >
								<span class = "supported-text">
									<?php _e( 'Allowed file type' , 'wiziq' ); ?>
								</span>
								<img class = "supported-img" src = "<?php echo WIZIQ_PLUGINURL_PATH . 'images/supported.gif' ; ?>"  />
							</div>
						</td>
						<td>
							<div class = "folder-content" >
								&nbsp;
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class = "wiziq_hide" >
				<span id = "content_folder_wrong" ><?php _e('Please enter folder name.','wiziq');?></span>
				<span id = "content_file_wrong" ><?php _e('Please select file to upload.','wiziq');?></span>
			</div>
			<input type= "hidden" name= "folderpath" value = "<?php echo $parent_result->folderpath ; ?>" /> 
			<input type = "hidden" value = "file" id = "content_type" name = "content_type" />
			<input class= "button button-primary wiziq-button" id = "wiziq_add_content_form" type = "Submit" name = "wiziq_add_content" value="<?php _e('Save','wiziq') ?>" /> 
			<a class= "button button-primary wiziq-button" id = "wiziq_cancel_course" href = "<?php echo WIZIQ_CONTENT_MENU."&parent=".$parent_id; ?>" ><?php _e('Cancel','wiziq') ?></a>
		</form>
		<?php
	} 
	
}

