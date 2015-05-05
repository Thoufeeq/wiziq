<?php
/*
 * Class for api request for all back ends and a few reusable functions used in front end also
 * @since 1.0
 */ 
class wiziq_api_functions 
{
	/*
	 * Function to get list of time zones
	 *  @since 1.0
	 */
	function getTimeZone(){
        //For live api
        $xmlTimeUrl = get_option( 'timezone_api_url' );
        $timeZone = array();
        $error = "";
        if (function_exists('curl_init')) {
            try {
                $ch = curl_init($xmlTimeUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $data = curl_exec($ch);
                curl_close($ch);
                if(!empty($data)){
                    $xmlObject = new SimpleXmlElement($data, LIBXML_NOCDATA);
                    foreach ($xmlObject->time_zone as $value) {
                        $timeZone[(string)$value] = (string)$value;
                    }
                }else{
                    $error ='Error in getting time zone';
                    echo '<div class="error"><p><strong>ERROR</strong>'.$error.'</p></div>';
                }
            } catch (Exception $e) {
                $error= $e->getMessage();
                echo '<div class="error"><p><strong>ERROR</strong>'.$error.'</p></div>';
            }
        }  else {
            $error = 'Curl extention is not installed';
            echo '<div class="error"><p><strong>ERROR</strong>'.$error.'</p></div>';
        }
        asort($timeZone);
        return $timeZone;
    }
    
    /*
     * function to get languages from wiziq api
     *  @since 1.0
     */ 
    function getLanguages(){
        $xmlLangUrl = 'http://class.api.wiziq.com/vc-language.xml';

        $languages = array();
        $error = "";
        if (function_exists('curl_init')) {
            try {
                $ch = curl_init($xmlLangUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $data = curl_exec($ch);
                curl_close($ch);
                if(!empty($data)){
                    $xmlObject = new SimpleXmlElement($data, LIBXML_NOCDATA);
                    foreach ($xmlObject->virtual_classroom->languages->language as $value) {
                        $languages[(string)$value->language_culture_name] = (string)$value->display_name;
                    }
                }else{
                    $error ='Error in getting languages';
                    echo '<div class="error"><p><strong>ERROR</strong>'.$error.'</p></div>';
                }
            } catch (Exception $e) {
                $error= $e->getMessage();
                echo '<div class="error"><p><strong>ERROR</strong>'.$error.'</p></div>';
            }
        }  else {
            $error = 'Curl extention is not installed';
            echo '<div class="error"><p><strong>ERROR</strong>'.$error.'</p></div>';
        }
        asort($languages);
        return $languages;
    }
    
    /*
     * function to add single or recurring classes
     *  @since 1.0
     */ 
	function addLiveClass($data) {
		
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
		$authBase = new wiziq_authBase($secretAcessKey,$access_key);

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
            }

            if ($data['class_repeat'] == 5) {

                if ($data['class_repeatby_type'] == 'repeat_day') {

                    $requestParameters["monthly_day"] = $data['every_month_day_no'];
                    $requestParameters["monthly_week_day"] = $data['every_month_day_day'];
                    $requestParameters["rdo_by_day"] = "true";
                } elseif ($data['class_repeatby_type'] == 'repeat_date') {
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
        } catch (Exception $e) {
            $this->errormsg .= $e->getMessage();
            $api_error = $e->getMessage();
            
        }
		$abc = new SimpleXMLElement($XMLReturn);
        if (!empty($XMLReturn)) {
            try {
                $objDOM = new DOMDocument();
                $objDOM->loadXML($XMLReturn);
            } catch (Exception $e) {
                $this->errormsg .= $e->getMessage();
            }
            $status = $objDOM->getElementsByTagName("rsp")->item(0);
            $attribNode = $status->getAttribute("status");
            $abc = new SimpleXMLElement($XMLReturn);
        }//end if	
        if ($attribNode == 'ok') {
            $response['livestatus'] = 'ok';
            $methodTag = $objDOM->getElementsByTagName("method");
            $response['response_method'] = $methodTag->item(0)->nodeValue;
			//if ( attendee_limit
            if ($response['response_method'] == 'create_recurring') {
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
               values ('".$requestParameters['presenter_id']."', 
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
               'not_available',
               '0' ,
                '',
                'True')  
                  ";
                $wpdb->query($insertqry);
                ?>
				<script>
					window.location = "<?php echo WIZIQ_CLASS_MENU.'&action=view_course&course_id='.$coursid; ?>&raddsucess";
				</script>
				<?php
            } else {
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
               values ('".$requestParameters['presenter_id']."', 
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
               'not_available',
               '1' ,
                '',
                'False')  
                  ";
                $wpdb->query($insertqry);
                $lastid = $wpdb->insert_id;
                ?>
				<script>
					window.location = "<?php echo WIZIQ_CLASS_MENU.'&action=class_detail&class_id='.$lastid.'&course_id='.$coursid; ?>";
				</script>
				<?php
			}
		} else if ($attribNode == "fail") {
			$error = $objDOM->getElementsByTagName("error")->item(0);
			$errorcode = $error->getAttribute("code");
			$errormsg = $error->getAttribute("msg");
			if ("1003" == $errorcode ||"1004" == $errorcode || "1005" == $errorcode || "1010" == $errorcode || "1011" == $errorcode || "1012" == $errorcode || "1014" == $errorcode || "1019" == $errorcode || "1022" == $errorcode || "1030" == $errorcode || "1031" == $errorcode || "1032" == $errorcode || "1043" == $errorcode || "1090" == $errorcode ) {
				$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
			}
            else {
				$error1 = WIZIQ_COM_COMMAN_MESSAGE;
			}
			global $myerror;
            $adderror = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
            
			$myerror = new WP_Error( 'wiziq_class_add_error', $adderror ); 
            
           // redirect the user to class page with error
        } else {
			
			echo '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.'</strong>'.__(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
        }
        return $response;
    }//end add class function
    
    /*
     * Function to check recurring classes and insert in the database
     *  @since 1.0
     */ 
    function wiziq_view_schedule ( $master_id, $classid ) {
		global $wpdb;
		$wiziq_classes = $wpdb->prefix."wiziq_wclasses";
		$access_key = get_option('access_key');
		$secretAcessKey = get_option('secret_key');
		$webServiceUrl = get_option('recurring_api_url');
		require_once("AuthBase.php");
		$authBase = new wiziq_authBase($secretAcessKey,$access_key);
		$requestParameters["signature"] = $authBase->wiziq_generateSignature('view_schedule', $requestParameters);
        $requestParameters["class_master_id"] = $master_id;
        $requestParameters["page_number"] = '1';
        $requestParameters["page_size"] = '60';
        
		$wiziq_httpRequest = new wiziq_httpRequest();
		try {
			$XMLReturn = $wiziq_httpRequest->wiziq_do_post_request($webServiceUrl . '?method=view_schedule', http_build_query($requestParameters, '', '&'));
            //libxml_use_internal_errors(true);
			$objdom = new SimpleXMLElement($XMLReturn);
			$attribnode = (string) $objdom->attributes();
		} catch (Exception $e) {
			$this->errormsg .= $e->getMessage();
			$api_error = $e->getMessage();
        }
		if (!empty($XMLReturn)) {
			try {
				$objDOM = new DOMDocument();
				$objDOM->loadXML($XMLReturn);
				$xml = new SimpleXMLElement($XMLReturn);
			} catch (Exception $e) {
				$this->errormsg .= $e->getMessage();
			}
			$status = $objDOM->getElementsByTagName("rsp")->item(0);
			$attribNode = $status->getAttribute("status");
		}
		if ($attribNode == 'ok') {
			$attribnode = (string) $objdom->attributes();
			$recurringlist = $objdom->view_schedule->recurring_list;
			$savedresqry = "select * from $wiziq_classes where response_class_id= '$classid' ";
			$savedres = $wpdb->get_row($savedresqry);
			if ( $recurringlist ) {
				
				foreach ( $recurringlist as $list )  {
                foreach($list->class_details as $classdetail) {
					$recurring_class_id = $classdetail->class_id;
					if ( $classid != $recurring_class_id ){
						$presenter_url = $classdetail->presenter_list->presenter->presenter_url;
						//$wiziq_classes
						$insertqry = "insert into $wiziq_classes 
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
						values ('".$savedres->created_by."', 
					   '".$classdetail->class_title."' , 
						'".date ("Y-m-d H:i:s" , strtotime($classdetail->start_time))."' ,
						'".$classdetail->duration."',
						'".$savedres->courseid."' ,
						'".$savedres->classtimezone."',
						'".$savedres->language."',
						'".$savedres->recordclass."',
						'".$savedres->attendee_limit."',
						'".$recurring_class_id."',
						'".$classdetail->recording_url."',
						'".$presenter_url."',
						'".$classdetail->class_status."', 
						'".$master_id."',
						'".$classdetail->attendance_report_status."', 
						'1' ,
						'',
						'True')  
						  ";
						$wpdb->query($insertqry);
					}
				}
				$originalclass = $savedres->id;
				$wpdb->query("update $wiziq_classes set get_detail = '1' where id = '$originalclass' ");
				
				}
			}
		} else if ($attribNode == "fail") {
			$error = $objDOM->getElementsByTagName("error")->item(0);
			$errorcode = $error->getAttribute("code");
			if ( "1004" == $errorcode || "1005" == $errorcode || "1017" == $errorcode) {
				$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
			}
			else {
				$error1 = WIZIQ_COM_COMMAN_MESSAGE;
			}
			$get_erro =  '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
			global $myerror;
			$myerror = new WP_Error( 'wiziq_class_get_data_error', $get_erro ); 
		} else {
			$get_erro =  '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.'</strong>'.__(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
			global $myerror;
			$myerror = new WP_Error( 'wiziq_class_get_data_error', $get_erro ); 
        }
        return $recurringlist;
		
	}
	
	/*
	 * Function to get data of a single class 
	 *  @since 1.0
	 */ 
	 function wiziq_get_data ( $classid ) {
		global $wpdb;
		$access_key = get_option('access_key');
		$secretAcessKey = get_option('secret_key');
		$webServiceUrl = get_option('recurring_api_url');
		require_once("AuthBase.php");
		$authBase = new wiziq_authBase($secretAcessKey,$access_key);
		$requestParameters["signature"] = $authBase->wiziq_generateSignature('get_data', $requestParameters);
		$requestParameters["class_id"] = $classid;
        $requestParameters["columns"] = "presenter_id, presenter_name,presenter_url, start_time,
                            time_zone, create_recording, status, language_culture_name,
                            duration, recording_url,is_recurring,class_master_id,title,class_recording_status,attendance_report_status , attendee_limit";
        
		$wiziq_httpRequest = new wiziq_httpRequest();
		try {
			$XMLReturn = $wiziq_httpRequest->wiziq_do_post_request($webServiceUrl . '?method=get_data', http_build_query($requestParameters, '', '&'));
            //libxml_use_internal_errors(true);
		} catch (Exception $e) {
			$this->errormsg .= $e->getMessage();
			echo $api_error = $e->getMessage();
        }
		if (!empty($XMLReturn)) {
			try {
				$objdom = new SimpleXMLElement($XMLReturn);
				$attribNode = $objdom->attributes();

				if ($attribNode == 'ok') {
					$getdata = $objdom->get_data->record_list->record;
					return $getdata;
				} else if ($attribNode == "fail") {
					$errormsg = $objdom->error->attributes()->msg;
					$errorcode = $objdom->error->attributes()->code;
					if ( "1004" == $errorcode || "1005" == $errorcode || "1017" == $errorcode) {
						$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
					}
						else {
						$error1 = WIZIQ_COM_COMMAN_MESSAGE;
					}
					$get_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_class_get_data_error', $get_erro ); 
					//echo '<div class="error"><p><strong>ERROR </strong>'.$error1.'</p></div>';
					
				}
			} catch (Exception $e) {
				$get_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_class_get_data_error', $get_erro ); 
				//echo '<div class="error"><p><strong>ERROR</strong>'.WIZIQ_COM_CATCH.'</p></div>';
				
			}
		}
		if ( isset ($api_error) )  {
			$get_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' ' .' </strong>'.__(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
			global $myerror;
			$myerror = new WP_Error( 'wiziq_class_get_data_error', $get_erro ); 
			//echo '<div class="error"><p><strong>ERROR</strong>'.WIZIQ_COM_CATCH.'</p></div>';
		}
		
	}

	
	
	/*
	 * Function to delete a class
	 *  @since 1.0
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
                $cancel_erro = '<div class="updated"><p><strong>'.__('Classes deleted successfully','wiziq').'</strong></p></div>';
				global $myerror;
				$myerror = new WP_Error( 'wiziq_class_delete_error', $cancel_erro );
            } else if ($attribNode == "fail") {
				$errors = $objDOM->getElementsByTagName("error")->item(0);
				$errorcode = $errors->getAttribute("code");
				$errormsg = $errors->getAttribute("msg");
				if ( $errorcode == "1009" || $errorcode == "1036" ) {
					$qry = "delete from $wiziq_classes where id= '$class_id'";
					$wpdb->query($qry);
					$cancel_erro = '<div class="error"><p><strong>'.__('Class deleted successfully','wiziq').'</strong></p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_class_delete_error', $cancel_erro ); 
				} else {
					if ( "1020" == $errorcode || "1033" == $errorcode || "1034" == $errorcode || "1035" == $errorcode || "1036" == $errorcode   ) {
						$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
					}
					else {
						$error1 = WIZIQ_COM_COMMAN_MESSAGE;
					}
					$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_class_delete_error', $cancel_erro ); 
				}
             }
        }//end if
        if ( isset($api_error) && $api_error )  {
			$cancel_erro =  '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.'</strong>'.__(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
			global $myerror;
			$myerror = new WP_Error( 'wiziq_class_delete_error', $cancel_erro );
		}
	}

    /*
	 * Function to get download link
	 *  @since 1.0
	 */	
	function getdownloadlink($class_id) {
		global $wpdb;
		$access_key = get_option('access_key');
		$secretAcessKey = get_option('secret_key');
		$webServiceUrl = get_option('recurring_api_url');
		require_once("AuthBase.php");
		$authBase = new wiziq_authBase($secretAcessKey,$access_key);
		$requestParameters["signature"] = $authBase->wiziq_generateSignature('download_recording', $requestParameters);
		$requestParameters["class_id"] = $class_id;
        $curos = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strstr($curos, "win")) {
            $format = 'exe';
            $requestParameters["recording_format"] = "exe";
        } else {
            $format = 'zip';
            $requestParameters["recording_format"] = "zip";
        }
       	$wiziq_httpRequest = new wiziq_httpRequest();
		try {
				$XMLReturn = $wiziq_httpRequest->wiziq_do_post_request($webServiceUrl . '?method=download_recording', http_build_query($requestParameters, '', '&'));
		} 
		catch (Exception $e) {
			$this->errormsg .= $e->getMessage();		
		}
		if (!empty($XMLReturn)) {
			try {
				$objdom = new SimpleXMLElement($XMLReturn);
				$attribNode = $objdom->attributes();
			} catch (Exception $e) {
				$e->getMessage();
				$api_error = $e->getMessage();
			}
			if ($attribNode == 'ok') {
				$status_xml_path = $objdom->download_recording->status_xml_path;
				$xml1  = simplexml_load_file($status_xml_path);
				if ($xml1->download_recording->download_status == 'true') {
					$record = $xml1->download_recording->recording_download_path;
					return $record;
				}
			} else if ($attribNode == "fail") {
				$errormsg = $objdom->error->attributes()->msg;
				$errorcode = $objdom->error->attributes()->code;
				if ( "1004" == $errorcode || "1005" == $errorcode || "1017" == $errorcode) {
					$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
				}
				else {
					$error1 = WIZIQ_COM_COMMAN_MESSAGE;
				}
				//echo '<div class="error"><p><strong>ERROR </strong>'.$error1.'</p></div>';
			}
	   }//end if
		if ( isset($api_error) && $api_error )  {
			//echo '<div class="error"><p><strong>ERROR</strong>'.WIZIQ_COM_CATCH.'</p></div>';
		}
    }
	
	/*
	 * Function to update a single class
	 * @param data , pass post array
	 * @param class_id, pass class id p.k
	 * since 1.0
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
		$qry = "select created_by, response_class_id from $wiziq_classes where id = '$class_id'";
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
        
        $user_info = get_userdata( $res->created_by  );
		if(!empty($userdata->user_firstname)){
			$requestParameters["presenter_name"] = $user_info->first_name.' '.$user_info->last_name;
		} else	{
			$requestParameters["presenter_name"] = $user_info->display_name;
		}
        $requestParameters["presenter_email"] = $user_info->user_email;
        
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
		} catch (Exception $e) {
			$api_error = $e->getMessage();
        }
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
		$acb = new SimpleXMLElement($XMLReturn);
		//echo '<pre>';print_r($acb); echo '</pre>';
		if ($attribNode == 'ok') {
			$response = true;
		} else if ($attribNode == "fail") {
			$xml = new SimpleXMLElement($XMLReturn);
			$error = $objDOM->getElementsByTagName("error")->item(0);
			$errorcode = $error->getAttribute("code");
			$errormsg = $error->getAttribute("msg");
			if ("1003" == $errorcode ||"1004" == $errorcode || "1005" == $errorcode || "1006" == $errorcode || "1010" == $errorcode || "1011" == $errorcode || "1012" == $errorcode || "1015" == $errorcode || "1016" == $errorcode || "1017" == $errorcode || "1018" == $errorcode || "1020" == $errorcode || "1022" == $errorcode || "1031" == $errorcode || "1032" == $errorcode || "1032" == $errorcode || "1043" == $errorcode ) {
				$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
			}
			else {
				$error1 = WIZIQ_COM_COMMAN_MESSAGE;
			}
			echo '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
            $response = false;
        } else {
			echo '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.'</strong>'.__(WIZIQ_COM_CATCH,'wiziq').'</p></div>';
		}
        return $response;
    }
    
	/*
	* Function to display attende report
	*  @since 1.0
	*/
     function getAttendance_report ( $class_id ) {
        $access_key = get_option('access_key');
		$secretAcessKey = get_option('secret_key');
		$webServiceUrl = get_option('recurring_api_url');
		require_once("AuthBase.php");
		$authBase = new wiziq_authBase($secretAcessKey,$access_key);
        $method = "get_attendance_report";
        $requestParameters["signature"] = $authBase->wiziq_generateSignature($method, $requestParameters);
        $requestParameters["class_id"] = $class_id;
        $wiziq_httpRequest = new wiziq_httpRequest();
        try {
            $XMLReturn = $wiziq_httpRequest->wiziq_do_post_request($webServiceUrl . '?method=get_attendance_report', http_build_query($requestParameters, '', '&'));
            $attendancexml = new SimpleXMLElement($XMLReturn);
            $attendancexmlstatus = $attendancexml->attributes();
            if ($attendancexmlstatus == 'ok') {
                $attendancexmlch = $attendancexml->get_attendance_report;
                $attendancexmlchstatus = $attendancexmlch->attributes();
                 if ($attendancexmlchstatus == 'true') {
                    $attendancexmlchdur = $attendancexmlch->class_duration;
                    $attendancexmlchattlist = $attendancexmlch->attendee_list->attendee;
                    return $attendancexmlchattlist;
                }
            } else if ($attendancexmlstatus == "fail") {
                $errorcode =  $attendancexml->error->attributes()->code;
                $errormsg =  $attendancexml->error->attributes()->msg;
                if ( "1004" == $errorcode || "1005" == $errorcode || "1017" == $errorcode) {
				$error1 =  eval('return WIZIQ_COM_'. $errorcode . ';');
				}
				else {
					$error1 = WIZIQ_COM_COMMAN_MESSAGE;
				}
				echo '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
            }
        } catch (Exception $e) {
            $error1 = WIZIQ_COM_CATCH;
			echo '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
        }
    }//end function to get attendance report

	/*
	 * Function for content (folder create, folder delete, content delete)
	 *  @since 1.0
	*/ 
	function wiziq_content_method($requestParameters , $method ) {
			$access_key = get_option('access_key');
			$secretAcessKey = get_option('secret_key');
			$webServiceUrl = get_option('content_url');
			require_once("AuthBase.php");
			$requestparameters = array();
			$authBase = new wiziq_authBase($secretAcessKey, $access_key);
			$requestparameters["signature"] = $authBase->wiziq_generateSignature($method, $requestparameters);
			
			if( !empty ( $requestParameters ) )
			{
				foreach($requestParameters as $key=>$value)
				{
					$requestparameters[$key] = $value;
				}
			}
			$wiziq_httpRequest = new wiziq_httpRequest();
			$xmlreturn = $wiziq_httpRequest->wiziq_do_post_request($webServiceUrl."?method=$method" , http_build_query($requestparameters, '', '&'));
			$createfolderxml = new SimpleXMLElement($xmlreturn);
			return $createfolderxml;
	}
	
	/*
	 * Function to upload content  for frontend and backend
	 * @since 1.0
	 */ 
	function wiziq_content_upload($uploaddata ,$filedata , $parent_id) {
		global $current_user;
		global $wpdb;
		$access_key = get_option('access_key');
		$secretAcessKey = get_option('secret_key');
		$webServiceUrl = get_option('content_url');
		$method = "upload";
		require_once("AuthBase.php");
		$requestparameters = array();
		$authBase = new wiziq_authBase($secretAcessKey, $access_key);
		$requestparameters["signature"] = $authBase->wiziq_generateSignature($method, $requestparameters);
		if (!empty($uploaddata['file_title'])) {
			$requestparameters["title"] = $uploaddata['file_title'];
		} else {
			$filename = array();
			$filename = explode(".", $filedata['uploadingfile']['name']);
			$requestparameters["title"] = $filename['0'];
		}
		if (!empty($uploaddata['folderpath'])) {
			$requestparameters["folder_path"] = $uploaddata['folderpath'];
		}
		//$requestparameters['presenter_id'] = $current_user->ID;
		$requestparameters['presenter_email'] = $current_user->user_email;
		$requestparameters["app_version"] = WIZIQ_APP_VERSION;
		if(!empty($current_user->user_firstname)){
			//$requestparameters["presenter_name"] = $current_user->user_firstname.' '.$current_user->user_lastname;
		} else	{
			//$requestparameters["presenter_name"] = $current_user->display_name;
		}
		$content = file_get_contents($filedata['uploadingfile']['tmp_name']);
		
		$filefieldname = (array_keys($filedata));
		$delimiter = '-------------' . uniqid();
		$filefields = array(
			'file1' => array(
			'name' => $filedata['uploadingfile']['name'],
			'type' => $filedata['uploadingfile']['type'],
			'content' => $content),
			);
		
			
		$data = '';
		foreach ($requestparameters as $name => $value) {
			$data .= "--" . $delimiter . "\r\n";
			$data .= 'Content-Disposition: form-data; name="' . $name . '";' . "\r\n\r\n";
			// note: double endline
			$data .= $value . "\r\n";
		}
		foreach ($filefields as $name => $file) {
			$data .= "--" . $delimiter . "\r\n";
			// "filename" attribute is not essential; server-side scripts may use it
			$data .= 'Content-Disposition: form-data; name="' . $filefieldname['0'] . '";' .
			' filename="' . $file['name'] . '"' . "\r\n";
			// this is, again, informative only; good practice to include though
			$data .= 'Content-Type: ' . $file['type'] . "\r\n";
			// this endline must be here to indicate end of headers
			$data .= "\r\n";
			// the file itself (note: there's no encoding of any kind)
			$data .= $file['content'];
		}
		$data .= "\r\n"."--" . $delimiter . "--\r\n";
		$str = $data;
		// set up cURL
		$ch=curl_init($webServiceUrl."?method=upload");
		curl_setopt_array($ch, array(
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => array( // we need to send these two headers
				'Content-Type: multipart/form-data; boundary='.$delimiter,
				'Content-Length: '.strlen($str)
			),
			CURLOPT_POSTFIELDS => $data,
		));
		$ress =   curl_exec($ch);
		curl_close($ch);
		try {
			$contentupload = new SimpleXMLElement($ress);
			$contentupload_rsp = $contentupload->attributes();
			if ($contentupload_rsp == "ok") {
				$contentupload_status = $contentupload->upload->attributes();
				if ($contentupload_status == "true") {
					$content_details = $contentupload->upload->content_details;
						$response['livestatus'] = 'ok';
						$response['content_id'] = (string)$content_details->content_id;
						$response['file_name'] = $requestparameters["title"];
						//$data['uploadingfile'] = $filedata['uploadingfile']['name'];
						$wiziq_contents = $wpdb->prefix."wiziq_contents";
						$qry = "insert into $wiziq_contents
						( status, created_by , isfolder,
						name, parent, content_id, uploadingfile, folderpath)
						values ( 'Inprogress', '$current_user->ID', '0' ,
						'".$response['file_name']."', '$parent_id' , '".$response['content_id']."', 
						'".$filefields['file1']['name']."', 
						''
						)
						";
						$wpdb->query($qry);
						return true;
				} 
			} else if ($contentupload_rsp =="fail") {
					$errorcode = $contentupload->error->attributes()->code;
					$errormsg = $contentupload->error->attributes()->msg;
					if ("1001" == $errorcode ||"1002" == $errorcode || "1013" == $errorcode || "1014" == $errorcode || "1030" == $errorcode || "1031" == $errorcode || "1039" == $errorcode || "1040" == $errorcode || "1045" == $errorcode || "1046" == $errorcode || "1047" == $errorcode || "1048" == $errorcode || "1049" == $errorcode || "1052" == $errorcode || "1054" == $errorcode || "1055" == $errorcode ) {
						$errormsg =  eval('return WIZIQ_COM_'. $errorcode . ';');
					}
					else {
						$errormsg = WIZIQ_COM_CONTENT_ERROR;
					}
					$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.'</strong>'. __($errormsg,'wiziq').'</p></div>';
					global $myerror;
					$myerror = new WP_Error( 'wiziq_content_upload_error', $cancel_erro ); 
					return false;
			 }
		} catch (Exception $e) {
			$error = WIZIQ_COM_CATCH;
			$cancel_erro = '<div class="error"><p><strong>'.__('ERROR','wiziq').' '.' </strong>'.__($error1,'wiziq').'</p></div>';
			global $myerror;
			$myerror = new WP_Error( 'wiziq_content_upload_error', $cancel_erro ); 
			return false;
		}
	}
	
	
	/*
	 * Function for teachers 
	 *  @since 1.0
	*/ 
	function wiziq_teacher_method($requestParameters , $method ) {
			$access_key = get_option('access_key');
			$secretAcessKey = get_option('secret_key');
			$webServiceUrl = get_option('recurring_api_url');
			require_once("AuthBase.php");
			$requestparameters = array();
			$authBase = new wiziq_authBase($secretAcessKey, $access_key);
			$requestparameters["signature"] = $authBase->wiziq_generateSignature($method, $requestparameters);
			if( !empty ( $requestParameters ) )
			{
				foreach($requestParameters as $key=>$value)
				{
					$requestparameters[$key] = $value;
				}
			}
			$requestparameters["app_version"] = WIZIQ_APP_VERSION;
			$wiziq_httpRequest = new wiziq_httpRequest();
			$xmlreturn = $wiziq_httpRequest->wiziq_do_post_request($webServiceUrl."?method=$method" , http_build_query($requestparameters, '', '&'));
			$teacherxml = new SimpleXMLElement($xmlreturn);
			return $teacherxml;
	}
}
?>
