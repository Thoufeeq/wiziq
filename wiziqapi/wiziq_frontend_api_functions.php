<?php
/*
 * Wiziq functions to be used only in frontend
 */ 
class wiziq_frontend_api_functions  {
	
	/*
	 * Function to cancel a class on server
	 * @since 1.0
	 */ 
	function wiziq_cancel( $class_id ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
        $access_key = get_option('access_key');
		$secretAcessKey = get_option('secret_key');
		$webServiceUrl = get_option('recurring_api_url');
		require_once("AuthBase.php");
		$authBase = new wiziq_authBase($secretAcessKey,$access_key);
		$method = "cancel";
		$requestParameters["signature"] = $authBase->wiziq_generateSignature($method, $requestParameters);
		$qry = "select response_class_id from $wiziq_classes where id = '$class_id'";
		$res = $wpdb->get_row($qry);
		$response_class_id = $res->response_class_id;
        $requestParameters["class_id"] = $response_class_id;
        //exit();
        $wiziq_httpRequest = new wiziq_httpRequest();
        try {
            $XMLReturn = $wiziq_httpRequest->wiziq_do_post_request($webServiceUrl . '?method=cancel', http_build_query($requestParameters, '', '&'));
        } catch (Exception $e) {
            $e->getMessage();
            $api_error = $e->getMessage();
        }
        
        if (!empty($XMLReturn)) {
            try {
				$objDOM = new DOMDocument();
				$xyz =  $objDOM->loadXML($XMLReturn);
				$xml = new SimpleXMLElement($XMLReturn);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            $status = $objDOM->getElementsByTagName("rsp")->item(0);
            $attribNode = $status->getAttribute("status");
            if ($attribNode == "ok") {
                $cancelTag = $objDOM->getElementsByTagName("cancel")->item(0);
                $qry = "delete from $wiziq_classes where id= '$class_id'";
                $wpdb->query($qry);
				$cancel_erro = '<div class="updated"><p><strong>'.__('Class deleted successfully','wiziq').'</strong></p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_class_delete_error', $cancel_erro );
            } else if ($attribNode == "fail") {
				$errors = $objDOM->getElementsByTagName("error")->item(0);
				$errorcode = $errors->getAttribute("code");
				$errormsg = $errors->getAttribute("msg");
				if ( "1020" == $errorcode || "1033" == $errorcode || "1034" == $errorcode || "1035" == $errorcode || "1036" == $errorcode   ) {
					$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
					$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' </strong> '.__($error1,'wiziq').'</p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_class_delete_error', $cancel_erro ); 
				}else {
					$error1 = WIZIQ_COM_COMMAN_MESSAGE;
					$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' </strong> '.__($error1,'wiziq').'</p></div>';
					global $myerror;
				
					$myerror = new WP_Error( 'wiziq_class_delete_error', $cancel_erro ); 
				}
				
             }
        }
        if ( isset ($api_error) )  {
			$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').'</strong> '.__(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
			global $myerror;
			$myerror = new WP_Error( 'wiziq_class_delete_error', $cancel_erro ); 
		}
    }// end cancel class function
    
    /*
     * function to add single or recurring classes
     * @since 1.0
     */ 
	function addLiveClass_Frontend($data) {
		
		global $wpdb;
		global $current_user;
		$coursid = $data['courseid'];
      	get_currentuserinfo();
        $method = isset($data['classmethod']) && $data['classmethod'] != 'single' ? "create_recurring" : "create";
        //access api url,access key and secret-key
        $access_key = get_option('access_key');
        $secretAcessKey = get_option('secret_key');
        $webServiceUrl = ($method == 'create_recurring') ? get_option('recurring_api_url') : get_option('api_url');

        //// Include API BASE File .....
		require_once("AuthBase.php");
		$authBase = new wiziq_authBase( $secretAcessKey,$access_key );

        $requestParameters["signature"] = $authBase->wiziq_generateSignature($method, $requestParameters);

        #for teacher account pass parameter 'presenter_email'
        //This is the unique email of the presenter that will identify the presenter in WizIQ. Make sure to add
        //this presenter email to your organization�s teacher account. � For more information visit at: (http://developer.wiziq.com/faqs)
        $teacher_id = $data['wiziq_teacher'];
        if ( $teacher_id  == 0 ) {
		
        
			$requestParameters["presenter_email"] = $current_user->user_email;
			#  #for room based account pass parameters 'presenter_id', 'presenter_name'
			//////// Get Cureent user Id and name form wordpress	
			$requestParameters["presenter_id"] = $current_user->ID;
			if(!empty($current_user->user_firstname)){
				$requestParameters["presenter_name"] = $current_user->user_firstname.' '.$current_user->user_lastname;
			} else	{
				$requestParameters["presenter_name"] = $current_user->display_name;
			}
		} else {
			
			$user_info = get_userdata( $teacher_id );
			if(!empty($user_info->first_name)){
				$teacher_name = $user_info->first_name.' '.$user_info->last_name;
			} else	{
				$teacher_name = $user_info->display_name;
			}
			
			$requestParameters["presenter_email"] = $user_info->user_email;
			#  #for room based account pass parameters 'presenter_id', 'presenter_name'
			//////// Get Cureent user Id and name form wordpress	
			$requestParameters["presenter_id"] = $teacher_id;
			
			$requestParameters["presenter_name"] = $teacher_name;
			
			
		}

        $requestParameters["title"] = $data['class_name']; //Required  , value =	English Class

        if (isset($data['schedule_now'])) {
            date_default_timezone_set($data['classtimezone']);
            $requestParameters["start_time"] =  date('m/d/Y H:i:s');     //date('m/d/Y H:i:s', strtotime($data['class_time']));
        } else {
            $requestParameters["start_time"] = date('m/d/Y', strtotime($data['class_time'])) . ' ' . $data['hours'] . ':' . $data['minutes'];
        }
        
        //Required 12/12/2012 12:12

         $response['classtime'] = $requestParameters["start_time"];
        // $method  is   create_recurring
        if ($method == 'create_recurring') {

            $requestParameters["class_repeat_type"] = $data['class_repeat']; //Required  , value =   1
            if ($data['class_occurrence_type'] == 'after_class') {

                $requestParameters["class_occurrence"] = $data['class_occurrence']; //Required , value =  4
            } elseif ($data['class_occurrence_type'] == 'on_date') {
                $requestParameters["class_end_date"] = date('m/d/Y', strtotime($data['class_end_date'])); //optional       , value = 2014-10-10 
            }

            if ($data['class_repeat'] == 4) {

                $requestParameters["specific_week"] = $data['specific_week']; //Required  , value =   2
                $requestParameters["days_of_week"] = implode(',', $data['days_of_week']);
                $data['days_of_week'] = implode(',', $data['days_of_week']);
                //exit;
            }

            if ($data['class_repeat'] == 5) {

                if ($data['class_repeatby_type'] == 'repeat_day') {

                    $requestParameters["monthly_day"] = $data['every_month_day_no'];
                    $requestParameters["monthly_week_day"] = $data['every_month_day_day'];
                    $requestParameters["rdo_by_day"] = "true";
                } elseif ($data['class_repeatby_type'] == 'repeat_date') {
                    //echo $data['every_month_date'];
                    //exit;
                    $requestParameters["monthly_date"] = $data['every_month_date'];
                    $requestParameters["rdo_by_date"] = "true";
                }
            }
        }
        /// Other optional parameter 

		$requestParameters["duration"] = $data['duration']; //optional 120
		$requestParameters["time_zone"] = $data['classtimezone']; //optional
        $requestParameters["attendee_limit"] = $data['attendee_limit']; //optional 100
        $requestParameters["create_recording"] = $data['recordclass']; //optional */
		$requestParameters["language_culture_name"] = $data['language'];
		$requestParameters["control_category_id"] = ""; //optional
        $requestParameters["return_url"] = ""; //optional
        $requestParameters["status_ping_url"] = ""; //optional
        $requestParameters["app_version"] = WIZIQ_APP_VERSION;
		$wiziq_httpRequest = new wiziq_httpRequest();
        try {
            $XMLReturn = $wiziq_httpRequest->wiziq_do_post_request($webServiceUrl . '?method=' . $method, http_build_query($requestParameters, '', '&'));
            return $XMLReturn;
        } catch (Exception $e) {
            $api_error = __(WIZIQ_COM_CATCH,'wiziq');
            return $e;
        }
    }// end add class function

	/*
	 * function to update a single class
	 * @since 1.0
	 */
	 function updateSingleLiveClass($data ,$class_id) {
        //access api url,access key and secret-key
		global $wpdb;
		global $current_user;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
        $access_key = get_option('access_key');
		$secretAcessKey = get_option('secret_key');
		$webServiceUrl = get_option('recurring_api_url');
		require_once("AuthBase.php");
		$authBase = new wiziq_authBase($secretAcessKey,$access_key);
		$method = "modify";
		$requestParameters["signature"] = $authBase->wiziq_generateSignature($method, $requestParameters);
		$qry = "select response_class_id,created_by from $wiziq_classes where id = '$class_id'";
		$res = $wpdb->get_row($qry);
		$response_class_id = $res->response_class_id;
        $requestParameters["class_id"] = $response_class_id;
       

        #for teacher account pass parameter 'presenter_email'
        //This is the unique email of the presenter that will identify the presenter in WizIQ. Make sure to add
        //this presenter email to your organization�s teacher account. � For more information visit at: (http://developer.wiziq.com/faqs)
        //$requestParameters["presenter_email"] = $data['presenter_email'];
        #  #for room based account pass parameters 'presenter_id', 'presenter_name'
        //////// Get Cureent user Id and name form Joomla	
        
        $requestParameters["presenter_id"] = $res->created_by;
		if(!empty($current_user->user_firstname)){
			$requestParameters["presenter_name"] = $current_user->user_firstname.' '.$current_user->user_lastname;
		} else	{
			$requestParameters["presenter_name"] = $current_user->display_name;
		};
        
        $requestParameters["title"] = $data['class_name']; //Required  , value =	English Class
		if (isset($data['schedule_now'])) {
			date_default_timezone_set($data['classtimezone']);
			$requestParameters["start_time"] =  date('m/d/Y H:i:s');     //date('m/d/Y H:i:s', strtotime($data['class_time']));
        } else {
			$requestParameters["start_time"] = date('m/d/Y', strtotime($data['class_time'])) . ' ' . $data['hours'] . ':' . $data['minutes'];
		}
       // $requestParameters["start_time"] = date('m/d/Y H:i:s', strtotime($data['class_time'])); //Required 12/12/2012 12:12
        /// Other optional parameter 
        $response['classtime'] = $requestParameters["start_time"];
        $requestParameters["duration"] = $data['duration']; //optional 120
        $requestParameters["time_zone"] = $data['classtimezone']; //optional
        $requestParameters["attendee_limit"] = $data['attendee_limit']; //optional 100
        $requestParameters["create_recording"] = $data['recordclass']; //optional 
        $requestParameters["language_culture_name"] = $data['language'];
        $requestParameters["control_category_id"] = ""; //optional
        $requestParameters["return_url"] = ""; //optional
        $requestParameters["status_ping_url"] = ""; //optional
        $requestParameters["app_version"] = WIZIQ_APP_VERSION;
        $wiziq_httpRequest = new wiziq_httpRequest();
        try {
            $XMLReturn = $wiziq_httpRequest->wiziq_do_post_request($webServiceUrl . '?method=modify', http_build_query($requestParameters, '', '&'));
            return $XMLReturn;
        } catch (Exception $e) {
            $api_error = __(WIZIQ_COM_CATCH,'wiziq');
        }
    } // end class update function
    
    /*
     * Function to add attendees in the class
     * @since 1.0
     */
    function wiziq_addattendee( $courseid, $classid, $languageculturename ) {
			global $wpdb;
			global $current_user;
			$attendeeid = $current_user->ID;
			if(!empty($current_user->user_firstname)){
				$attendeescreenname = $current_user->user_firstname.' '.$current_user->user_lastname;
			} else	{
				$attendeescreenname = $current_user->display_name;
			}
			
			
			$access_key = get_option('access_key');
			$secretAcessKey = get_option('secret_key');
			$webServiceUrl = get_option('recurring_api_url');
			require_once("AuthBase.php");
			$authBase = new wiziq_authBase($secretAcessKey,$access_key);
			
			$method = "add_attendees";
			$XMLAttendee="<attendee_list>
			<attendee>
				<attendee_id><![CDATA[$attendeeid]]></attendee_id>
				<screen_name><![CDATA[$attendeescreenname]]></screen_name>
				<language_culture_name><![CDATA[$languageculturename]]></language_culture_name>
			</attendee>
		  </attendee_list>";
			
			$requestParameters["signature"] = $authBase->wiziq_generateSignature($method, $requestParameters);
			$requestParameters["class_id"] = $classid;//required
			$requestParameters["attendee_list"]=$XMLAttendee;
			$wiziq_httpRequest = new wiziq_httpRequest();
			try
			{
				$XMLReturn=$wiziq_httpRequest->wiziq_do_post_request($webServiceUrl.'?method=add_attendees',http_build_query($requestParameters, '', '&')); 
			}
			catch(Exception $e)
			{	
				echo $e->getMessage();
			}
			if(!empty($XMLReturn))
			{
				try
				{
				  $objDOM = new DOMDocument();
				  $objDOM->loadXML($XMLReturn);
				}
				catch(Exception $e)
				{
				  echo $e->getMessage();
				}
				$status=$objDOM->getElementsByTagName("rsp")->item(0);
				$attribNode = $status->getAttribute("status");
				if($attribNode=="ok")
				{
					$methodTag=$objDOM->getElementsByTagName("method");
					$method=$methodTag->item(0)->nodeValue;
					
					$class_idTag=$objDOM->getElementsByTagName("class_id");
					$class_id=$class_idTag->item(0)->nodeValue;
					
					$add_attendeesTag=$objDOM->getElementsByTagName("add_attendees")->item(0);
					$add_attendeesStatus = $add_attendeesTag->getAttribute("status");
					
					$attendeeTag=$objDOM->getElementsByTagName("attendee");
					$length=$attendeeTag->length;
					for($i=0;$i<$length;$i++)
					{
						$attendee_idTag=$objDOM->getElementsByTagName("attendee_id");
						$attendee_id=$attendee_idTag->item($i)->nodeValue;
						
						$attendee_urlTag=$objDOM->getElementsByTagName("attendee_url");
						$attendee_url=$attendee_urlTag->item($i)->nodeValue;
					}
					?>
					<script>
						window.location = "<?php echo $attendee_url; ?>";
					</script>
					<?php 
									
				}
				else if($attribNode=="fail")
				{
					$error=$objDOM->getElementsByTagName("error")->item(0);
					$errorcode = $error->getAttribute("code");	
					$errormsg = $error->getAttribute("msg");
					if ( "1007" == $errorcode || "1008" == $errorcode || "1009" == $errorcode || "1020" == $errorcode || "1021" == $errorcode || "1023" == $errorcode  || "1024" == $errorcode || "1043" == $errorcode || "1044" == $errorcode ) {
						$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
					}
					else {
						$error1 = WIZIQ_COM_COMMAN_MESSAGE;
					}
					$errorms = '<div class = "error " id = "add-front-class-error" ><p><strong>'.__('ERROR','wiziq').' </strong> '.__($error1,'wiziq').'</div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_add_attendee_error', $errorms );
			} 
		}
	}// end function to add attendees to a class
 
}
