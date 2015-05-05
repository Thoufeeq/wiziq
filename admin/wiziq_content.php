<?php
/*
 * Functionality to check the request for content
 * @since 1.0
 */ 
echo '<div class= "wrap" >';
		$wiziq_content = new Wiziq_Content;
		if ( isset ($_GET['contentact'])  && 'add_content' == $_GET['contentact'] && isset ( $_GET['wp_nonce'] ) ) {
			if (isset ($_POST['wiziq_add_content']) ) {
				$wiziq_content->wiziq_add_content ( $_GET['parent'] , $_POST );
			}
			$wiziq_content->wiziq_add_content_form ();
		} else {
			if (!isset ( $_GET['parent'] ) ) {
				$parent = 1 ;
			} else {
				$parent = $_GET['parent'];
			}
			if ( isset ( $_GET['refresh'] ) ) {
				$wiziq_content->wiziq_refresh_content ( $parent );
			}
			$wiziq_content->wiziq_view_content ( $parent );
		}
echo '</div>';
