<?php
/*
 * Add WiziQ Menu in wordpress
 */ 
 
 

 
function wiziq_menu() {
	add_menu_page( __('WizIQ','wiziq'),__('WizIQ','wiziq'),'manage_options','wiziq','wiziq_admin', plugins_url( 'images/w.png' , dirname(__FILE__) )  ,'21');
	add_submenu_page( 'wiziq', __( 'Courses' , 'wiziq' ), __( 'Courses' , 'wiziq' ), 'manage_options', 'wiziq', 'wiziq_admin' );  
	add_submenu_page( 'wiziq', __( 'Content' , 'wiziq' ), __( 'Content' , 'wiziq' ) , 'manage_options', 'wiziq_content', 'wiziq_content' ); 
	add_submenu_page( 'wiziq', __( 'Teacher' , 'wiziq' ), __( 'Teacher' , 'wiziq' ) , 'manage_options', 'wiziq_teachers', 'wiziq_teacher_callback' ); 
	add_submenu_page( 'wiziq', __( 'Settings' , 'wiziq' ), __( 'Settings' , 'wiziq' ) , 'manage_options', 'wiziq_settings', 'wiziq_settings' ); 
	add_submenu_page( '', '', __('Enroll Users', 'wiziq'), 'manage_options', 'wiziq_enroll', 'wiziq_enroll_users' ); 
	add_submenu_page( '', '', __('WizIQ Class', 'wiziq'), 'manage_options', 'wiziq_class', 'wiziq_class' ); 
}
add_action('admin_menu','wiziq_menu');

/*
 * Display content on Courses page
 * @since 1.0
 */ 

function wiziq_admin(){
	require_once( 'wiziq_courses.php' );
}

/*
 * Display teachers menu
 * @since 1.0
 */ 
function wiziq_teacher_callback() {
	require_once( 'wiziq_teachers.php' );
}

/*
 * Function to enroll users in a course
 * @since 1.0
 */ 
function wiziq_enroll_users () {
	require_once( 'wiziq_courses_enroll_users.php' );
}

/*
 * Display content menu page
 * @since 1.0
 */ 
function wiziq_content () {
	require_once( 'wiziq_content.php');
}


/*
 * Function to add classes in a course
 * @since 1.0
 */ 

function  wiziq_class() {
	require_once( 'wiziq_class.php' );
}




/*
 * function to work on settings
 * @since 1.0
 */ 
function wiziq_settings() {
	echo '<h2>'.__('WizIQ Credentials','wiziq').'</h2>';
	echo '<div class = "wrap" >';
	//Check For Valid Nonce and access key and secret key and then update
	if( isset ( $_POST['wiziq_settings'] )) {
		if ( ! empty( $_POST['access_key'] ) && ! empty( $_POST['secret_key'] ) && check_admin_referer( 'update_settings', 'setting_nonce' ) ) {
			$access_key = trim($_POST['access_key']);
			$secret_key = trim($_POST['secret_key']);
			$obj = new CheckapicredentailsClass();
			$apiresponse = $obj->Checkapicredentails($secret_key,$access_key,get_option('recurring_api_url'));
			if($apiresponse == 1){
				update_option( 'access_key', $access_key );
				update_option( 'secret_key', $secret_key );
				echo '<div class = "updated" ><p><strong>'.__('Settings Updated','wiziq').'</strong></p></div>';
			} else {
				echo '<div class="error"><p><strong>'.__('ERROR','wiziq').'</strong>: '.$apiresponse['code'].' , '.$apiresponse['msg'].'</p></div>';
				echo '<div class="error"><p><strong>'.__('ERROR','wiziq').'</strong>: '.__('Please Enter Correct Access Key & Secret Key','wiziq').'</p></div>';
			}
		} 
	}
	wiziq_settings_form();
	echo '</div>';
}

//settings form 
function wiziq_settings_form() {
	?>
		<form method = "post" id= "api-settings-form" >
			<?php wp_nonce_field('update_settings','setting_nonce'); ?>
			<table class = "form-table" >
				<tbody>
					<tr>
						<th scope = "row" ><label for = "api_url" ><?php _e('Class API URL', 'wiziq'); ?></label></th>
						<td><input class = "regular-text wiziq-text-disable" type = "text"  name= "api_url" disabled = "disabled"  value ="<?php echo get_option( 'api_url' ); ?>"/> </td>
					</tr>
					<tr>
						<th><?php _e('Recurring API URL', 'wiziq'); ?></th>
						<td><input class = "regular-text wiziq-text-disable" type = "text"  name= "recurring_api_url" disabled value ="<?php echo get_option( 'recurring_api_url' ); ?> "/> </td>
					</tr>
					<tr>
						<th><?php _e('Content API URL', 'wiziq'); ?></th>
						<td><input class = "regular-text wiziq-text-disable" type = "text"  name= "content_url" disabled value ="<?php echo get_option( 'content_url' ); ?>"/> </td>
					</tr>
					<tr>
						<th><?php _e('Access Key', 'wiziq'); ?></th>
						<td>
							<input class = "regular-text" type = "text" id= "access_key" name= "access_key" value ="<?php echo get_option( 'access_key' ); ?>"/>
							<div class = "wiziq_error" id = "setting_access_key_err" ></div>
							<div class = "wiziq_hide" id = "setting_access_key_msg" ><?php _e('Please Enter Access Key','wiziq');?></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Secret Key', 'wiziq'); ?></th>
						<td>
							<input class = "regular-text" type = "text"  id= "secret_key" name= "secret_key" value ="<?php echo get_option( 'secret_key' ); ?>"/>
							<div class = "wiziq_error" id = "setting_secret_key_err" ></div>
							<div class = "wiziq_hide" id = "setting_secret_key_msg" ><?php _e('Please Enter Secret Key','wiziq');?></div>
						</td>
					</tr>
					<tr>
						<th><?php _e('Virtual Classroom Language XML', 'wiziq'); ?></th>
						<td><input class = "regular-text wiziq-text-disable" type = "text"  name= "content_language" disabled value ="<?php echo get_option( 'content_language' ); ?> "/> </td>
					</tr>
					<tr>
						<th><?php _e('Time Zone API URL', 'wiziq'); ?></th>
						<td><input class = "regular-text wiziq-text-disable" type = "text"  name= "content_language" disabled value ="<?php echo get_option( 'timezone_api_url' ); ?> "/> </td>
					</tr>
				</tbody>
			</table>
			<input class= "button button-primary" type = "Submit" name = "wiziq_settings" value="<?php _e('Save Changes','wiziq'); ?>" /> 
		</form>
	<?php
}
