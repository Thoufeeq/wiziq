<?php 
/* start function to get all courses */

function get_front_courses(){
global $wpdb;
$tbname = $wpdb->prefix.'wiziq_courses';
$sqry = "select * from $tbname order by id desc";
$result = $wpdb->get_results($sqry);
if(!empty($result)){ return $result;}
else { return false; }
}

/* close  function to get all courses */
?>
