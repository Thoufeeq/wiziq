<?php

/*
 * Wiziq Global Variables
 */ 
define("WIZIQ_PLUGINURL_PATH", plugin_dir_url(  __FILE__  )); 
define("WIZIQ_PLUGIN_PATH", plugin_dir_path( __FILE__ ));
define("WIZIQ_COURSES_MENU", admin_url("admin.php?page=wiziq"));
define("WIZIQ_CONTENT_MENU", admin_url("admin.php?page=wiziq_content"));
define("WIZIQ_TEACHER_MENU", admin_url("admin.php?page=wiziq_teachers"));
define("WIZIQ_SETTINGS_MENU", admin_url("admin.php?page=wiziq_settings"));
define("WIZIQ_ENROLL_MENU", admin_url("admin.php?page=wiziq_enroll"));
define("WIZIQ_CLASS_MENU", admin_url("admin.php?page=wiziq_class"));
define("WIZIQ_ACCESS_KEY", "kndxlmt3RJw=");
define("WIZIQ_SECRET_KEY", "XxLzvbPUE2D/DFY5osT/6g==");
define("WIZIQ_ENROLL_USER", "Enroll User");
define("WIZIQ_USER_PREMISSIONS", "User Permissions");
define("WIZIQ_PAGINATION_LIMIT", get_option( 'posts_per_page' ));
//define("WIZIQ_PAGINATION_LIMIT", '1');
define("WIZIQ_DATE_FORMAT", get_option('date_format'));
define("WIZIQ_TIME_FORMAT", get_option('time_format'));
define("WIZIQ_DATE_TIME_FORMAT", WIZIQ_DATE_FORMAT." ".WIZIQ_TIME_FORMAT);
define("WIZIQ_APP_VERSION", 'Wordpress 1.0' );

/*
 * WizIQ error messages
 */
 
define("WIZIQ_COM_COMMAN_MESSAGE", 'There is an error while processing your request please contact to wiziq support' );
define("WIZIQ_COM_CATCH",'There is an  error while processing your request please contact to wiziq support or try later');

define('WIZIQ_COM_200','The request has been successfully completed.');
define('WIZIQ_COM_304','Not Modified: Operation ok. There was no new data to return');
define('WIZIQ_COM_400','Bad Request: The request is invalid. Error messages are returned for explanation purposes.');
define('WIZIQ_COM_401','Not Authorized: The credentials provided are invalid or are needed for the operation.');
define('WIZIQ_COM_403','Forbidden: The request is refused by the API.');
define('WIZIQ_COM_404','Not Found: The resource requested doesn’t exist. Ex: The provided car_id in the url doesn’t exist.');
define('WIZIQ_COM_500','Internal Server Error: An unrecoverable error has occurred at server side.');
define('WIZIQ_COM_502','Bad Gateway: The API service is down or being upgraded');
define('WIZIQ_COM_503','Service Unavailable: The service is overloaded with a lot requests and can’t manage the incoming request.');
define('WIZIQ_COM_1000','There is some error while processing your request.');
define('WIZIQ_COM_1001','presenter_id parameter is missing.');
define('WIZIQ_COM_1002','presenter_name parameter is missing.');
define('WIZIQ_COM_1003','Specified time_zone parameter is not available.');
define('WIZIQ_COM_1004','start_time parameter cannot precede current datetime.');
define('WIZIQ_COM_1005','Invalid start_time parameter.');
define('WIZIQ_COM_1006','time_zone parameter is missing.');
define('WIZIQ_COM_1007','Attendees can be  added only in an upcoming class.');
define('WIZIQ_COM_1008','This class has been cancelled.');
define('WIZIQ_COM_1009','class_id is invalid.');
define('WIZIQ_COM_1010','duration parameter cannot exceed  #MaxClassMinutes# minutes.');
define('WIZIQ_COM_1011','attendee_limit parameter cannot exceed #MaxUserPerClass# attendees.');
define('WIZIQ_COM_1012','Only #MaxConcurrentClass# simultaneous classes are allowed.');
define('WIZIQ_COM_1013','No record found.');
define('WIZIQ_COM_1014','title parameter is missing.');
define('WIZIQ_COM_1015','Cannot modify an in-progress class.');
define('WIZIQ_COM_1016','Cannot modify a completed class.');
define('WIZIQ_COM_1017','Cannot modify an expired class.');
define('WIZIQ_COM_1018','Cannot modify a deleted class.');
define('WIZIQ_COM_1019','start_time parameter is missing.');
define('WIZIQ_COM_1020','class_id parameter is missing.');
define('WIZIQ_COM_1021','attendee_list parameter is missing.');
define('WIZIQ_COM_1022','Datetime is not in valid format.');
define('WIZIQ_COM_1023','screen_name is missing.');
define('WIZIQ_COM_1024','attendee_id is missing.');
define('WIZIQ_COM_1025','page_size parameter cannot exceed 100.');
define('WIZIQ_COM_1026','Class has not held yet.');
define('WIZIQ_COM_1027','Expired class.');
define('WIZIQ_COM_1028','Cancelled class.');
define('WIZIQ_COM_1029','Attendance report will be available soon.');
define('WIZIQ_COM_1030','presenter_email parameter is missing.');
define('WIZIQ_COM_1031','presenter_email is not valid.');
define('WIZIQ_COM_1032','You have already scheduled a class for the current time.');
define('WIZIQ_COM_1033','Class is already cancelled.');
define('WIZIQ_COM_1034','Cannot cancel inprogress class.');
define('WIZIQ_COM_1035','Cannot cancel completed class.');
define('WIZIQ_COM_1036','Cannot cancel expired class.');
define('WIZIQ_COM_1039','content_id parameter is missing.');
define('WIZIQ_COM_1040','The file size exceeds the allowed size.');
define('WIZIQ_COM_1043','The language is invalid or unsupported in the VC');
define('WIZIQ_COM_1044','Language_culture_name parameter is missing.');
define('WIZIQ_COM_1045','Invalid folder_path.');
define('WIZIQ_COM_1046','presenter_email is not valid for this package.');
define('WIZIQ_COM_1047','presenter_id and presenter_name are not valid for this package.');
define('WIZIQ_COM_1048','Duplicate presenter_id cant be accepted.');
define('WIZIQ_COM_1049','The file type is not allowed.');
define('WIZIQ_COM_1052','Maximum title characters length is 70.');
define('WIZIQ_COM_1053','Invalid content_id.');
define('WIZIQ_COM_1054','Admin cannot upload a public content for teachers.');
define('WIZIQ_COM_1055','Invalid access level.');
define('WIZIQ_COM_1057','Teacher Email ID is required.');
define('WIZIQ_COM_1058','Please pass valid name parameter not more than 50 characters.');
define('WIZIQ_COM_1059','Password with Spaces, with less than 6 characters length and more than 15 characters is not allowed.');
define('WIZIQ_COM_1060','Please pass valid mobile number with country code or leave blank. For example:+44 9999999999.');
define('WIZIQ_COM_1061','Please pass valid work phone with country code or leave blank. For example:+44 9999999999.');
define('WIZIQ_COM_1062','This option is not available in your account. For more information please contact support@wiziq.com.');
define('WIZIQ_COM_1063','Your account has reached maximum limit of teachers. To add more teachers please contact sales@wiziq.com.');
define('WIZIQ_COM_1064','Teacher Email ID already exists.');
define('WIZIQ_COM_1065','Invalid file format. Only jpg, jpeg, png, gif are allowed.');
define('WIZIQ_COM_1066','Please pass valid teacher id.');
define('WIZIQ_COM_1067','Please pass valid email ID.');
define('WIZIQ_COM_1068','Value to parameter can_schedule_class is not passed or invalid. Please pass either 0 or 1 or leave blank.');
define('WIZIQ_COM_1069','Value to parameter is_active is not passed or invalid. Please pass either 0 or 1 or leave blank.');
define('WIZIQ_COM_1081','class_repeat_type parameter is empty/invalid.');
define('WIZIQ_COM_1082','class_occurrence / class_end_date parameter is empty/invalid.');
define('WIZIQ_COM_1083','specific_week / days_of_week parameter is empty/invalid.');
define('WIZIQ_COM_1084','monthly_date parameter is empty/invalid.');
define('WIZIQ_COM_1085','monthly_day / monthly_week_day parameter is empty/invalid.');
define('WIZIQ_COM_1090','extend_duration parameter is empty or invalid.');

define("WIZIQ_COM_CONTENT_ERROR", 'There is an error while processing your request please contact to wiziq support');

