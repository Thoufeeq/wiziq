/*
 * Load function on document ready
 */ 
jQuery(document).ready(function() {
	/*
	 * Tooltip for description column in courses
	 */ 
	jQuery(".al_down").tooltip({ position: { my: "left center", at: "right center" } }); 
	jQuery('#course_start_date').datepicker({
		dateFormat : 'mm/dd/yy',
		minDate: 0,
	});
	jQuery('#course_end_date').datepicker({
		dateFormat : 'mm/dd/yy',
		minDate: '+1',
	});
	
	var checkradio = jQuery("input[name='class_occurrence_type']:checked").val();
	if ( "on_date" === checkradio ) {
		jQuery("#wiziq_class_end_date_occurance").removeAttr("readonly");
		jQuery("#wiziq_class_end_date_in").val('');
		jQuery("#wiziq_class_end_date_in").attr('readonly', true);
		jQuery('#wiziq_class_end_date_occurance').datepicker({
			dateFormat : 'mm/dd/yy',
			minDate: 0,
		}); 
		jQuery('#wiziq_class_end_date').attr('readonly', true);
	} else if( "after_class" === checkradio)  {
		jQuery("#wiziq_class_end_date_in").removeAttr("readonly");
		jQuery("#wiziq_class_end_date_occurance").datepicker("destroy");
		jQuery("#wiziq_class_end_date_occurance").val('');
		jQuery("#wiziq_class_end_date_occurance").attr('readonly', true);
	}
		
	jQuery('#course_start_date, #course_end_date, #class_start_date').attr('readonly', true);
	
	/*
	 * For classes
	 */
	 jQuery('#class_start_date').datepicker({
		dateFormat : 'mm/dd/yy',
		minDate: 0,
	}); 
	
	var foldercontent = jQuery(".folder-content").length ;
	if ( foldercontent ) {
		jQuery(".folder-content").addClass('wiziq_hide');
		jQuery(".right-arrow-wiziq").addClass('wiziq_hide');
	}
	
});

/*
 *  Function to validate course form
 */ 
jQuery(document).on('submit',"#add_course_form", function () {
	jQuery('.wiziq_error').empty();
	var course_name = jQuery('#course_name').val().trim();
	var course_descirption = jQuery('#course_descirption').val().trim();
	var startdate = jQuery('#course_start_date').val();
	var enddate = jQuery('#course_end_date').val();
	var ret = true;
	if( startdate != '' && enddate != '' ) {
		if (Date.parse(startdate) >= Date.parse(enddate)) {
			var endgreatemsg = jQuery("#course_end_date_greater_msg").html();
			jQuery('#course_end_date_err').html(endgreatemsg);
			ret = false;
		}
	}
	else if( startdate != '' && enddate == '') {
		var endmsg = jQuery("#course_end_date_msg").html();
		jQuery('#course_end_date_err').html(endmsg);
		ret = false;
	}
	else if(startdate == '' && enddate != '') {
		var startmsg = jQuery("#course_start_date_msg").html();
		jQuery('#course_start_date_err').html(startmsg);
		ret = false;
	}
	if(course_name == '') {
			var course_name_msg = jQuery("#course_name_msg").html();
			jQuery('#course_name_err').html(course_name_msg);
			ret= false;
	}
	return ret;
});

/*
 * Function to check if course can be deleted
 */
jQuery(document).on('click', '.submitdelete-course' ,function () {
	
	var id = jQuery(this).attr('id');	
	var coursetodelete = jQuery('#hid-class-'+id).val();
	if( coursetodelete > 0 ) {
		var delete_inner = jQuery('#course_delete').html();
		alert( delete_inner);
		return false;
	}
	var confirm_inner = jQuery('#wiziq_are_u_sure').html();
	var r = confirm(confirm_inner);
	if (r == false)
	{
		return false;
	}
});

/*
 * Function to delete multiple courses
 */ 
jQuery(document).on('click','.delete-courses' , function () {
	var count = "0";
	var ret = true;
	var delete_action_course = jQuery('#delete_action_course').val();
	if ( delete_action_course == 1) {
		jQuery('.courses').each( function () {
			var course = jQuery(this).attr('id');
			var courseid = course.slice(7);
			if ( jQuery('#cb-select-'+courseid ).attr("checked") ){
				count++;
				var coursetodelete = jQuery('#hid-class-'+courseid).val();
				if( coursetodelete > 0 ) {
					var delete_inner = jQuery('#course_delete').html();
					alert( delete_inner);
					ret = false;
				}
			}
		});
		if( ret ){
			if ( count === "0" ) {
				var select_inner = jQuery('#wiziq_select_course').html();
				alert( select_inner);
				return false;
			} else {
				var confirm_inner = jQuery('#wiziq_are_u_sure').html();
				var r = confirm(confirm_inner);
				if (r == false)
				{
					return false;
				}
			}
		}
		return ret;
	} else {
		return false;
	}
});

jQuery(document).on('change', '.hasDatepicker', function () {
	var cid = jQuery(this).attr('id');
	if ( cid != "") {
		var newcid = "#"+cid+"_id";
		jQuery( newcid ).addClass('wiziq_show');
		jQuery( newcid ).removeClass('wiziq_hide');
		
	}
})

/*
 * Function to show/hide a div
 */ 
function display_settings ( cid , contentid ) {
	var content = jQuery('#'+contentid).val(); 
	if ( content != ''){
		jQuery('#'+contentid).val('');
		jQuery("#"+cid).removeClass('wiziq_show');
		jQuery("#"+cid).addClass('wiziq_hide');
	} else {
		jQuery('#'+contentid).val('');
		jQuery("#"+cid).addClass('wiziq_show');
		jQuery("#"+cid).removeClass('wiziq_hide');
	}
}

/*
 * jQuery function for seetings page
 */ 

jQuery(document).on('submit',"#api-settings-form", function () {
	jQuery('.wiziq_error').empty();
	var access_key = jQuery('#access_key').val().trim();
	var secret_key = jQuery('#secret_key').val().trim();
	var ret = true;
	if ( access_key == "" ) {
		var amsg = jQuery( "#setting_access_key_msg" ).html();
		jQuery( "#setting_access_key_err" ).html(amsg);
		ret = false;
	}
	if ( secret_key == "" ) {
		var smsg = jQuery( "#setting_secret_key_msg" ).html();
		jQuery( "#setting_secret_key_err" ).html(smsg);
		ret = false;
	}
	return ret;
});

/*
 * Jquery function for the Enroll user page
*/

 jQuery(document).ready(function(){
                //Add users to errolled list from all users list
	jQuery('#wiziq-add-user').click(function () {
		if(jQuery("#wiziq-all-user option:selected").length==0){
			alert("Please select user from right panel");
			jQuery("#wiziq-all-user").focus();
			return;
		}
		var permission_string='';
		jQuery.map(jQuery('#wiziq-all-user').find("option:selected"),function(option, i) {
			permission_string += '<tr id=enroll_'+jQuery(option).val()+' class="alternate iedit" >';
			var user_id = jQuery(option).val();
			var permission_val = jQuery("#user_role_"+user_id).attr('role');
			if ( "teacher" === permission_val ) {
				var class_checked = " checked " ;
				var content_checked = " checked " ;
				var download_checked = " checked " ;
				var user_disable = " ";
				var permission_class = "create_class";
				var content_class = "upload_content";
			}
			else {
				var class_checked = " " ;
				var content_checked = "" ;
				var download_checked = "" ;
				var user_disable = " disabled ";
				var permission_class = " permission_disable";
				var content_class = " permission_disable";
			}
			permission_string += '<td><input type="hidden"  name="user_id[]" value="'+jQuery(option).val()+'" />'+jQuery(option).text() +'</td>';
			permission_string += '<td><input type="hidden" value="0" name="create_class['+jQuery(option).val()+']" /><input value="1" type="checkbox" '+user_disable+' name="create_class['+jQuery(option).val()+']" class="'+permission_class+'" id="create_class_'+jQuery(option).val()+'" '+class_checked+' /> </td>'; 
			permission_string += '<td><input type="hidden" value="0" name="view_recording['+jQuery(option).val()+']" /><input value="1" type="checkbox" checked = "checked" name="view_recording['+jQuery(option).val()+']" class="view_recording" /> </td>';
			permission_string += '<td><input type="hidden" value="0" name="download_recording['+jQuery(option).val()+']" /><input value="1" type="checkbox" name="download_recording['+jQuery(option).val()+']" class="download_recording" '+class_checked+' /> </td>';
			permission_string += '<td><input type="hidden" value="0" name="upload_content['+jQuery(option).val()+']" /><input value="1" type="checkbox" '+user_disable+' name="upload_content['+jQuery(option).val()+']" class="'+content_class+'" id="'+jQuery(option).val()+'" '+class_checked+' /> </td>';
			permission_string += '</tr>';
		});    
		jQuery('#wiziq-course-permission tbody').append(permission_string);
		jQuery('#wiziq-all-user').find('option:selected').remove().appendTo('#wiziq-enroll-user');
		if(jQuery("#wiziq-all-user option").length==0){
			jQuery("#wiziq-add-user").css('visibility','hidden');
			}
		if(jQuery("#wiziq-enroll-user option").length>0){
			jQuery("#wiziq-remove-user").css('visibility','visible');
		}
	});

	//Remove users from errolled list
	jQuery("#wiziq-remove-user").click(function(){
		if(jQuery("#wiziq-enroll-user option:selected").length==0){
			alert("Please select user from left panel");
			jQuery("#wiziq-enroll-user").focus();
			return;
		}
		jQuery.map(jQuery('#wiziq-enroll-user').find("option:selected"), function(option, i) {
			jQuery('#wiziq-course-permission tr#enroll_'+jQuery(option).val()).remove();
		});
		jQuery('#wiziq-enroll-user').find('option:selected').remove().appendTo('#wiziq-all-user');
		if(jQuery("#wiziq-enroll-user option").length==0)
			jQuery("#wiziq-remove-user").css('visibility','hidden');
		if(jQuery("#wiziq-all-user option").length>0)
			jQuery("#wiziq-add-user").css('visibility','visible');
	});
	
	//On upload content, if create class is not checked it will automatically checked
	jQuery(document).on('click','.upload_content',function(){
		var current_id = jQuery(this).attr('id');
		if(jQuery("#"+current_id).is(':checked'))
			jQuery("#create_class_"+current_id).prop('checked',true);
	});
	
	//On create class, if create class is unchecked upload content will automatically unchecked
	jQuery(document).on('click','.create_class', function(){
		var currentId = jQuery(this).attr('id');
		if(!jQuery("#"+currentId).is(':checked')){
			var ids= currentId.substring(currentId.lastIndexOf("_")+1);
			jQuery("#"+ids).prop('checked',false);
		}
	});
	
	//onload, if needed hide add or remode button
	if(jQuery("#wiziq-enroll-user option").length==0)
		jQuery("#wiziq-remove-user").css('visibility','hidden');
	if(jQuery("#wiziq-all-user option").length==0)
		jQuery("#wiziq-add-user").css('visibility','hidden');
});
            
/*
 * Classes functions
 */     
jQuery(document).on('blur','#class_name' , function () {
	jQuery('#class_name_err').empty();
	var class_name = jQuery('#class_name').val().trim();
	if ( class_name == '' ) {
		var duration_msg = jQuery('#class_name_wrong').html();
		jQuery('#class_name_err').html(duration_msg);
		ret = false;
	}
});
 
/*
 * Class duration validation
 */     
jQuery(document).on('blur','#class_duration', function () {
	jQuery('#class_duration_err').empty();
	var class_duration = parseInt(jQuery('#class_duration').val().trim());
	if ( !class_duration  ) {
		var duration_error = jQuery("#class_duration_ms").hasClass('wiziq_hide');
		if ( !duration_error ) {
			jQuery('#class_duration_ms').addClass('wiziq_hide');
		}
		var duration_msg = jQuery('#class_durantion_wrong').html();
		jQuery('#class_duration_err').html(duration_msg);
	} else if( class_duration < 30 || class_duration >300 ) {
		var duration_error = jQuery("#class_duration_ms").hasClass('wiziq_hide');
		if ( !duration_error ) {
			jQuery('#class_duration_ms').addClass('wiziq_hide');
		}
		jQuery('#class_duration_ms').addClass('wiziq_hide');
		var duration_msg = jQuery('#class_durantion_wrong').html();
		jQuery('#class_duration_err').html(duration_msg);
	} else {
		var duration_error = jQuery("#class_duration_ms").hasClass('wiziq_hide');
		if ( duration_error ){
			jQuery('#class_duration_ms').removeClass('wiziq_hide');
		}
	}
});


jQuery(document).on('blur','#wiziq_class_end_date_in', function () {
	jQuery('#class_end_date_err').empty();
	var class_end_date = jQuery("#wiziq_class_end_date_in").val().trim();
	var class_end_date_number = jQuery.isNumeric(class_end_date);
	if ( !class_end_date_number  ) {
		var duration_msg = jQuery('#class_end_date_occurance_wrong').html();
		jQuery('#class_end_date_err').html(duration_msg);
	}
	else if ( class_end_date == '' ) {
		var duration_msg = jQuery('#class_end_date_occurance_wrong').html();
		jQuery('#class_end_date_err').html(duration_msg);
	}
	else if (class_end_date < 1 || class_end_date > 60) {
		var duration_msg = jQuery('#class_end_occurance_wrong').html();
		jQuery('#class_end_date_err').html(duration_msg);
	}
});

/*
 * jQuery for single or recurring classes
 */ 
jQuery(document).on ( 'change', '.wiziq_class_type' , function () {
	var class_type = jQuery(this).val();
	if ( "recurring" === class_type )  {
		jQuery("#class_recurring").removeClass('wiziq_hide');
		jQuery("#wiziq_end_class").removeClass('wiziq_hide');
		jQuery("#class_single").addClass('wiziq_hide');
		jQuery("#class_start_date").val('');
		jQuery('#class_start_date').datepicker({
			dateFormat : 'mm/dd/yy',
			minDate: 0,
		});
		
	} else {
		
		jQuery("#class_recurring").addClass('wiziq_hide');
		jQuery("#wiziq_end_class").addClass('wiziq_hide');
		jQuery("#class_single").removeClass('wiziq_hide');
		var monthly_find = jQuery(".wiziq_monthly_class").hasClass('wiziq_hide');
		if ( !monthly_find ) {
			jQuery(".wiziq_monthly_class").addClass('wiziq_hide');
		}
		var weekly_find = jQuery(".wiziq_weekly_class").hasClass('wiziq_hide')
		if ( !weekly_find ) {
			jQuery(".wiziq_weekly_class").addClass('wiziq_hide');
		}
		jQuery('#wiziq_class_repeat').prop('selectedIndex',0);
	}
	
});

/*
 * Function for end date
 */ 
jQuery(document).on ( 'change', '.wiziq_class_end' , function () {
	var class_end = jQuery(this).val();
	jQuery('#class_end_date_err').empty();
	if ( "on_date" === class_end ) {
		jQuery("#wiziq_class_end_date_occurance").removeAttr("readonly");
		jQuery("#wiziq_class_end_date_in").val('');
		jQuery("#wiziq_class_end_date_in").attr('readonly', true);
		jQuery('#wiziq_class_end_date_occurance').datepicker({
			dateFormat : 'mm/dd/yy',
			minDate: 0,
		}); 
		jQuery('#wiziq_class_end_date').attr('readonly', true);
	} else if( "after_class" === class_end)  {
		jQuery("#wiziq_class_end_date_in").removeAttr("readonly");
		jQuery("#wiziq_class_end_date_occurance").datepicker("destroy");
		jQuery("#wiziq_class_end_date_occurance").val('');
		jQuery("#wiziq_class_end_date_occurance").attr('readonly', true);
	}
});

/*
 * Function for monthly date and day repeat 
 */ 
jQuery(document).on ( 'change', '.wiziq_class_monhtly_repeat' , function () {
	var wiziq_class_monhtly_repeat = jQuery( "input[name=class_repeatby_type]:radio:checked" ).val();
	if ( "repeat_day" === wiziq_class_monhtly_repeat ) {
		jQuery("#month_day_repeat").removeClass('wiziq_hide');
	var date_find = jQuery("#month_date_repeat").hasClass('wiziq_hide')
	if ( !date_find ) {
		jQuery("#month_date_repeat").addClass('wiziq_hide');
	}
	} else {
		jQuery("#month_date_repeat").removeClass('wiziq_hide');
		var date_find = jQuery("#month_day_repeat").hasClass('wiziq_hide')
		if ( !date_find ) {
			jQuery("#month_day_repeat").addClass('wiziq_hide');
		}
	}
});

/*
 * Jquery for monthly and weekly repeated classes 
 */ 
jQuery(document).on ( 'change', '#wiziq_class_repeat' , function () {
	jQuery('#wiziq_class_repeat_err').empty();
	var repeat_type = jQuery(this).val();
	if ("4" === repeat_type ) {
		jQuery(".wiziq_weekly_class").removeClass('wiziq_hide');
		jQuery(".wiziq_repeat_week_class_schedule").removeClass('wiziq_hide');
		var monthly_find = jQuery(".wiziq_monthly_class").hasClass('wiziq_hide')
		if ( !monthly_find ) {
			jQuery(".wiziq_monthly_class").addClass('wiziq_hide');
		}
	} else if ("5" === repeat_type ) {
		jQuery(".wiziq_monthly_class").removeClass('wiziq_hide');
		var weekly_find = jQuery(".wiziq_weekly_class").hasClass('wiziq_hide')
		if ( !weekly_find ) {
			jQuery(".wiziq_weekly_class").addClass('wiziq_hide');
		}
		var weekly_schedule_find = jQuery(".wiziq_repeat_week_class_schedule").hasClass('wiziq_hide');
		if ( !weekly_schedule_find ) {
			jQuery(".wiziq_repeat_week_class_schedule").addClass('wiziq_hide');
		}
		var wiziq_class_monhtly_repeat = jQuery( "input[name=class_repeatby_type]:radio:checked" ).val();
		if ( "repeat_day" === wiziq_class_monhtly_repeat ) {
			jQuery("#month_day_repeat").removeClass('wiziq_hide');
			var date_find = jQuery("#month_date_repeat").hasClass('wiziq_hide')
			if ( !date_find ) {
				jQuery("#month_date_repeat").addClass('wiziq_hide');
			}
		} else {
			jQuery("#month_date_repeat").removeClass('wiziq_hide');
			var date_find = jQuery("#month_day_repeat").hasClass('wiziq_hide')
			if ( !date_find ) {
				jQuery("#month_day_repeat").addClass('wiziq_hide');
			}
		}
		
	} else {
		var monthly_find = jQuery(".wiziq_monthly_class").hasClass('wiziq_hide');
		if ( !monthly_find ) {
			jQuery(".wiziq_monthly_class").addClass('wiziq_hide');
		}
		var weekly_find = jQuery(".wiziq_weekly_class").hasClass('wiziq_hide')
		if ( !weekly_find ) {
			jQuery(".wiziq_weekly_class").addClass('wiziq_hide');
		}
		var weekly_schedule_find = jQuery(".wiziq_repeat_week_class_schedule").hasClass('wiziq_hide');
		if ( !weekly_schedule_find ) {
			jQuery(".wiziq_repeat_week_class_schedule").addClass('wiziq_hide');
		}
		if ('0' === repeat_type) {
			var duration_msg = jQuery('#wiziq_class_repeat_error').html();
			jQuery('#wiziq_class_repeat_err').html(duration_msg);
		}
	}
});


/*
 * Add class form submit
 */ 
jQuery(document).on ( 'click', '#add_class_wiziq' , function () {
	var ret = true;
	jQuery('.wiziq_error').empty();
	var class_name = jQuery('#class_name').val().trim();
	var class_duration = parseInt(jQuery('#class_duration').val().trim());
	var class_start_date = jQuery('#class_start_date').val().trim();
	var class_repear_type = jQuery("#wiziq_class_repeat").val();
	if(jQuery("#wiziq_class_end_date").length != 0) {
		var class_end_date = jQuery('#wiziq_class_end_date').val().trim();
	}
	var class_type = jQuery(".wiziq_class_type:checked").val();
	/*
	 * Starting validations
	 */ 
	
	if ( class_name == '' ) {
		var duration_msg = jQuery('#class_name_wrong').html();
		jQuery('#class_name_err').html(duration_msg);
		ret = false;
	}
	if ( class_start_date == '' ) {
		var duration_msg = jQuery('#class_start_date_wrong').html();
		jQuery('#class_start_date_err').html(duration_msg);
		ret = false;
	}
	if (class_type === "recurring" ) {
		var checkradio = jQuery("input[name='class_occurrence_type']:checked").val();
		if ("after_class" == checkradio) {
			class_end_date = jQuery("#wiziq_class_end_date_in").val();
			var class_end_date_number = jQuery.isNumeric(class_end_date);
			if ( !class_end_date_number  ) {
				var duration_msg = jQuery('#class_end_date_occurance_wrong').html();
				jQuery('#class_end_date_err').html(duration_msg);
				ret = false;
			}
			else if ( class_end_date == '' ) {
				var duration_msg = jQuery('#class_end_date_occurance_wrong').html();
				jQuery('#class_end_date_err').html(duration_msg);
				ret = false;
			}
			else if (class_end_date < 1 || class_end_date > 60) {
				var duration_msg = jQuery('#class_end_occurance_wrong').html();
				jQuery('#class_end_date_err').html(duration_msg);
				ret = false;
			}
		}else{
			class_end_date = jQuery("#wiziq_class_end_date_occurance").val();
			if ( class_end_date == '' ) {
				var duration_msg = jQuery('#class_end_date_wrong').html();
				jQuery('#class_end_date_err').html(duration_msg);
				ret = false;
			}
		}
		
		if ('0' ===  class_repear_type ) {
			var duration_msg = jQuery('#wiziq_class_repeat_error').html();
			jQuery('#wiziq_class_repeat_err').html(duration_msg);
			ret = false;
		}
	}
	
	if ( !class_duration  ) {
		var duration_error = jQuery("#class_duration_ms").hasClass('wiziq_hide');
		if ( !duration_error ) {
			jQuery('#class_duration_ms').addClass('wiziq_hide');
		}
		var duration_msg = jQuery('#class_durantion_wrong').html();
		jQuery('#class_duration_err').html(duration_msg);
		ret = false;
	} else if( class_duration < 30 || class_duration >300 ) {
		var duration_error = jQuery("#class_duration_ms").hasClass('wiziq_hide');
		if ( !duration_error ) {
			jQuery('#class_duration_ms').addClass('wiziq_hide');
		}
		jQuery('#class_duration_ms').addClass('wiziq_hide');
		var duration_msg = jQuery('#class_durantion_wrong').html();
		jQuery('#class_duration_err').html(duration_msg);
		ret = false;
	} else {
		var duration_error = jQuery("#class_duration_ms").hasClass('wiziq_hide');
		if ( duration_error ){
			jQuery('#class_duration_ms').removeClass('wiziq_hide');
		}
	}
	if ( "recurring" == class_type ) {
		var wiziq_class_repeat = jQuery('#wiziq_class_repeat').val();
		if ( "4" === wiziq_class_repeat ) {
			var count = 0;
			jQuery('.week_days_check').each(function () {
				if (jQuery(this).is(":checked") ) {
					count++;
				}
			});
			if ( !count ){
				var week_error = jQuery('#class_week_days_error').html();
				jQuery('#class_week_days_error_msg').html(week_error);
				ret = false;
			}
		}
	}
	return ret;
});

/*
 * jQuery for schedule right now
 */ 
jQuery( document ).on('change','#class_schedule' , function () {
	var checked = jQuery(this).attr('checked');
	if ( checked === "checked" ) {
		jQuery("#class_start_date").val(jQuery.datepicker.formatDate('mm/dd/yy', new Date()));
		jQuery("#class_start_date").datepicker("destroy");
		var d = new Date();
		var hours = (d.getHours() <10 ? '0' : '') + d.getHours();
		var minutes = (d.getMinutes() <10 ? '0' : '') + d.getMinutes();
		jQuery("#start_time_hours").val(hours);
		jQuery("#start_time_minutes").val(minutes);
		
	} else {
		jQuery("#class_start_date").val('');
		jQuery('#class_start_date').datepicker({
			dateFormat : 'mm/dd/yy',
			minDate: 0,
		}); 
		jQuery("#start_time_hours option:first").attr('selected','selected');
		jQuery("#start_time_minutes option:first").attr('selected','selected');
	}
});


/*
 * Function to delete a single class
 */
jQuery(document).on('click', '.submitdelete-class' ,function () {
	var confirm_inner = jQuery('#wiziq_are_u_sure').html();
	var r = confirm(confirm_inner);
	if (r == false)
	{
		return false;
	}
});

/*
 * Function to delete multiple classes
 */
 
jQuery(document).on('click','#delete_mul_class' , function () {
	var count = "0";
	var ret = true;
	var delete_action_class = jQuery('#delete_action_class').val();
		if ( delete_action_class == 1) {
		jQuery('.cclass').each( function () {
			var course = jQuery(this).attr('id');
			var courseid = course.slice(7);
			if ( jQuery('#cb-select-'+courseid ).attr("checked") ){
				count++;
			}
		});
		if( ret ){
			if ( count === "0" ) {
				var select_inner = jQuery('#wiziq_select_class').html();
				alert( select_inner);
				return false;
			} else {
				var confirm_inner = jQuery('#wiziq_are_u_sure').html();
				var r = confirm(confirm_inner);
				if (r == false)
				{
					return false;
				}
			}
		}
		return ret;
	} else {
		return false;
	}
});  


jQuery(document).ready(function(){
	if(jQuery(".back").length != 0) {
    jQuery('a.back').click(function(){
        parent.history.back();
        return false;
    });
	}
});

/*
 * Content page jquery
 */ 
jQuery(document).on('click', '.left-arrow-wiziq', function () {
	jQuery(".folder-content").removeClass('wiziq_hide');
	jQuery(".right-arrow-wiziq").removeClass('wiziq_hide');	
	jQuery(".upload-content").addClass('wiziq_hide');
	jQuery(".left-arrow-wiziq").addClass('wiziq_hide');	
	jQuery("#content_type").val('folder');
});
jQuery(document).on('click', '.right-arrow-wiziq', function () {
	jQuery(".folder-content").addClass('wiziq_hide');
	jQuery(".right-arrow-wiziq").addClass('wiziq_hide');	
	jQuery(".upload-content").removeClass('wiziq_hide');
	jQuery(".left-arrow-wiziq").removeClass('wiziq_hide');	
	jQuery("#content_type").val('file');
});


jQuery(document).on('click','#wiziq_add_content_form' , function () {
	var ret = true;
	var content_type = jQuery("#content_type").val();
	if ( content_type == 'file') {
		var uploadingfile = jQuery("#uploadingfile").val();
		if (uploadingfile == '') {
			var content_file_wrong = jQuery('#content_file_wrong').html();
			jQuery('#content_file_err').html(content_file_wrong);
			ret = false;
		}
	} else {
		var uploadingfolder = jQuery("#folder_name").val();
		if (uploadingfolder == '') {
			var content_folder_wrong = jQuery('#content_folder_wrong').html();
			jQuery('#content_name_err').html(content_folder_wrong);
			ret = false;
		}
	}
	return ret;
});

/*
 * Delete content
 */ 

jQuery (document).on ('click' , '#delete-content' , function () {
	var count = "0";
	var ret = true;
	var delete_action_course = jQuery('#delete_action_content').val();
	if ( delete_action_course == 1) {
		jQuery('.content').each( function () {
			var contentid = jQuery(this).attr('id');
			var newcontentid = contentid.slice(8);
			if ( jQuery('#cb-select-'+newcontentid ).attr("checked") ){
				count++;
				var contenttodelete = jQuery('#hid-content-'+newcontentid).val();
				if( contenttodelete > 0 ) {
					var delete_inner_content = jQuery('#delete_inner_content').html();
					alert(delete_inner_content);
					ret =  false;
				}
			}
		});
		if( ret ){
			if ( count === "0" ) {
				var wiziq_select_content = jQuery('#wiziq_select_content').html();
				alert(wiziq_select_content);
				ret = false;
			} else {
				var confirm_inner = jQuery('#wiziq_are_u_sure').html();
				var r = confirm(confirm_inner);
				if (r == false)
				{
					return false;
				}
			}
		}
		return ret;
	}else {
		return false;
	}
}) 


/*
 * jQuery for teachers
 */ 
jQuery(document). ready( function () {
	/*
	 * To display and hide password
	 */ 
	jQuery('.teacher_password').hover (function () {
		var teacher_id = jQuery(this).attr('id');
		var teacher_newid = "#teacher_pass_"+teacher_id.slice(5);
		var dummy_newid = "#dummy_pass_"+teacher_id.slice(5);
		jQuery(dummy_newid).toggle();
		jQuery(teacher_newid).toggle();
		
			
	}, function () {
		var teacher_id = jQuery(this).attr('id');
		var teacher_newid = "#teacher_pass_"+teacher_id.slice(5);
		var dummy_newid = "#dummy_pass_"+teacher_id.slice(5);
		jQuery(teacher_newid).toggle();
		jQuery(dummy_newid).toggle();
	}
	);
});

jQuery(document).on ('click','#wiziq_add_teacher', function () {
	var ret = true;
	jQuery('.wiziq_error').empty();
	var password = jQuery('#password').val().trim();
	var teacher_check = jQuery("input[name='is_active']:checked").val();
	
	if( password == '') {
		var password_msg = jQuery("#course_name_msg").html();
		jQuery('#teacher_password_err').html( teacher_password_empty );
		ret= false;
	} else if ( password.length < 6 || password.length >15) {
		jQuery('#teacher_password_err').html( teacher_password_length );
		ret= false;
	}
	if ( 0 ==  teacher_check) {
		var upcoming_classes = jQuery('#upcoming_classes').val();
		if ( 0 == upcoming_classes ) {
			//alert('no upcoming classes');
		} else {
			var upcoming_msg = jQuery("#wiziq_teacher_sure").html();
			var r = confirm(upcoming_msg);
			if (r == false)
			{
				ret  =  false;
			}
		}
	} 
	return ret;
	
});


/*
 * enroll students in course
 */ 
jQuery(document).on('change','.all_check', function () {
	var checkid = jQuery(this).attr('id');
	if (jQuery('#'+checkid).is(':checked')) {
		checkid = checkid.slice(0,-4);
		jQuery('.'+checkid).attr( 'checked', true );
	} else {
		checkid = checkid.slice(0,-4);
		jQuery('.'+checkid).attr( 'checked', false );
	}
}) 
