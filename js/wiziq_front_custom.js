/*
 * Load function on document ready
 */ 


jQuery(document).ready(function(){
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
    jQuery('#course_start_date, #course_end_date, #class_start_date').attr('readonly', true);
	if(jQuery("#add-front-class-error").length != 0) {
		var errormsg = jQuery("#add-front-class-error").html();
		//alert(errormsg);
		//jQuery('#wiziq-add-front-error').html(errormsg);
	}
	/*
	 * For classes
	 */
	 jQuery('#class_start_date').datepicker({
		dateFormat : 'mm/dd/yy',
		minDate: 0,
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
	
	/*
	 * For content page
	 */ 
	var foldercontent = jQuery(".folder-content").length ;
	if ( foldercontent ) {
		jQuery(".folder-content").addClass('wiziq_hide');
		jQuery(".right-arrow-wiziq").addClass('wiziq_hide');
	}

});

/*
 *  Function to validate course form
 */ 
jQuery(document).on('click',"#wiziq_add_course", function () {
	jQuery('.wiziq_error').empty();
	var today = new Date();
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
	else if(startdate == '' && enddate != ''){
			var startmsg = jQuery("#course_start_date_msg").html();
			jQuery('#course_start_date_err').html(startmsg);
			ret = false;
		}
	if(course_name == '') {
			var coursemsg = jQuery("#course_name_msg").html();
			jQuery('#course_name_err').html(coursemsg);
			ret= false;
	}
	return ret;
});

jQuery(document).on('change', '.hasDatepicker', function () {
	var cid = jQuery(this).attr('id');
	if ( cid != "") {
		var newcid = "#"+cid+"_id";
		jQuery( newcid ).addClass('wiziq_show');
		jQuery( newcid ).removeClass('wiziq_hide');
		
	}
});

/*
 * Function to check if course can be deleted
 */
jQuery(document).on('click', '.submitdelete' ,function () {
	
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
	jQuery('.cclass').each( function () {
		var course = jQuery(this).attr('id');
		var courseid = course.slice(7);
		if ( jQuery('#cb-select-'+courseid ).attr("checked") ){
			count++;
		}
	});
	if( ret ){
		if ( count === "0" ) {
			alert('Please Select Classes to Delete');
			return false;
		} else {
			var r = confirm("Are You Sure, You Want To Delete!");
			if (r == false)
			{
				return false;
			}
		}
	}
	return ret;
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
 * *****************Content Functions ********
 */
jQuery (document). on ('click', '.wiziq_delete_content' , function() {
	var ret = true;
	var conid = jQuery(this).attr('id');
	var content_id = conid.slice(8);
	var newid = "#wiziq_delete_content_hidden_"+content_id;
	var res = jQuery(newid).val();
	if ( res > 0 ) {
		var delete_inner_content = jQuery('#delete_inner_content').html();
		alert(delete_inner_content);
		ret = false;
	} else {
		var confirm_inner = jQuery('#wiziq_are_u_sure').html();
		var r = confirm(confirm_inner);
		if (r == false)
		{
			ret = false;
		}
	}
	return ret;
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
