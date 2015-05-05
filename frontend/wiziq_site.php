<?php 
/*
 * Add shortcode for frontend.
 * @since 1.0
 */ 

add_shortcode('WizIQ','wiziq_shortcode');

function wiziq_shortcode(){

include 'shortcode/shortcode_wiziq.php';

}



/*
 * Add css in front end 
 * @since 1.0
 */
add_action('wp_head','add_css_wiziqfront');

function add_css_wiziqfront(){
 wp_register_style( 'frontendstylesheet', WIZIQ_PLUGINURL_PATH.'stylesheet/frontend.css' );
 wp_enqueue_style( 'frontendstylesheet' );
 wp_register_style('jquery-ui-customsite-css', WIZIQ_PLUGINURL_PATH . 'stylesheet/jquery-ui-1.10.3.css');
 wp_enqueue_style( 'jquery-ui-customsite-css' ); 
 wp_enqueue_script('jquery');
 wp_enqueue_style( 'dashicons' );
 wp_enqueue_script('jquery-ui-tooltip');
 wp_enqueue_script('jquery-ui-datepicker');
 wp_enqueue_script( 'wiziq_front_js', WIZIQ_PLUGINURL_PATH . 'js/wiziq_front_custom.js'); 

}

//add_action( 'plugins_loaded', 'my_plugin_override' );
add_action( 'wp', 'my_plugin_override' );
add_action( 'wp', 'wiziq_custom_authentication' );
add_action( 'wp', 'is_user_administrator' );



function is_user_administrator(){
	if(is_user_logged_in()){
		global $current_user;
		get_currentuserinfo();
		if(in_array("administrator", $current_user->roles)){
			return true;
		}
		else {
			return false;
		}
	} else {
		return false;
	}
}


function wiziq_custom_authentication(){
	if(isset($_GET['action']) && $_GET['action'] == 'addcourse'){
		$auth = is_user_administrator();
		if( ! $auth ) {
			$rurl = $_SERVER['REDIRECT_URL'];
			?>
				<script>
				//	window.location = "<?php echo $rurl; ?>";
				</script>	 
			<?php
		}
	}
}

function remove_querystring_var($url, $key) { 
	$url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&'); 
	$url = substr($url, 0, -1); 
	return $url; 
}

require_once ('shortcode/wiziq_frontend_classes.php');
require_once ('shortcode/wiziq_frontend_courses.php');
require_once ('shortcode/wiziq_frontend_content.php');

/*
 * Function to check for special request which have to be completed before header loads
 * @since 1.0
 */ 
function my_plugin_override() {
	// condition for add course only for the administrator.	
	$wiziq_courses = new Wiziq_Courses;
	$wiziq_content = new Wiziq_Content;
	$wiziq_frontend_classes = new Wiziq_Frontend_Classes;
	$wiziq_frontend_content = new Wiziq_Frontend_Content;
	if(isset($_POST['wiziq_addfront_course']))	{
		if ( !is_user_administrator () ) {
			?>
			<script>
				window.location = "<?php echo get_permalink(); ?>";
			</script>
		<?php
		}
		$wiziq_courses->wiziq_add_course( $_POST , get_permalink() );
	}
	else if(isset($_GET['action']) && $_GET['action'] == 'editcourse' && $_GET['wn'] != ''){
		
		$course_id = $_GET['course_id'];
		if ( ! wp_verify_nonce( $_GET['wn'] , 'edit-course-'.$course_id  ) ) {
			?>
			<script>
				window.location = "<?php echo get_permalink(); ?>";
			</script>
		<?php
		}
	}
	else if ( isset ($_POST['wiziq_editfront_course']) && isset ( $_POST['course_id'] ) ) {
		$course_id = $_POST['course_id'];
		$wiziq_courses->wiziq_edit_course($course_id, $_POST , get_permalink() );
	}
	else if(isset($_GET['action']) && $_GET['action'] == 'deletecourse' && $_GET['wn'] != '' && !isset( $_GET['deleted'])){
		$course_id = $_GET['course_id'];
		if ( wp_verify_nonce( $_GET['wn'] , 'delete-course-'.$course_id  ) ) {
			$wiziq_courses->wiziq_delete_course ( $_GET['wn'], $course_id , get_permalink());
			?>
			<script>
				window.location = "<?php echo get_permalink(); ?>";
			</script>
			<?php
		} 
	}
	/*
	 * classes functionality
	 */
	 else if ( isset ($_GET['caction'] ) && isset ( $_GET['course_id'] ) && isset ( $_GET['wp_nonce'] ) && 'add_class' == isset ($_GET['caction'] ) && isset ($_POST['add_class_wiziq']) ) {
		 $returnerrormsg = $wiziq_frontend_classes->wiziq_frontend_add_class($_POST);
	 }else if ( isset ($_GET['caction']) && isset ($_GET['class_id']) && isset ( $_GET['course_id'] ) && isset ( $_GET['wp_nonce'] ) && 'edit_class' == $_GET['caction'] && isset ( $_POST['wiziq_edit_class'] ) ) {
		 $wiziq_frontend_classes->wiziq_frontend_update_class  ( $_POST, $_GET['class_id'] );
	 }
	 /*
	  * Content functionality
	  */
	else if ( isset ( $_GET['ccaction'] ) && "view_content" == $_GET['ccaction'] && isset ( $_GET['refresh_content'] ) && isset ( $_GET['parent'] ) ) {
		$wiziq_content->wiziq_refresh_content ( $_GET['parent'] );
	} else if ( isset ( $_GET['ccaction'] ) && "view_content" == $_GET['ccaction'] && isset ( $_GET [ 'wp_nonce' ] ) && isset ( $_GET ['delete_content'] )  ) {
		$wiziq_frontend_content->wiziq_frontend_delete_content ( $_GET ['delete_content'], $_GET['parent'] , $_GET [ 'wp_nonce' ] );
	} else if ( isset ($_POST['wiziq_front_add_content'] ) && isset ( $_GET ['ccaction'] ) && "add_content" == $_GET ['ccaction'] &&  isset ( $_GET ['parent'] ) && isset ( $_GET ['course_id'] ) ) {
		$wiziq_frontend_content->wiziq_frontend_add_content ( $_GET ['parent'] , $_POST );
	}
	
}



?>
