<?php
/*
 * Wiziq utilities functions
 */ 
Class Wiziq_Util {
	
	/*
	 * Pagination functionality
	 * @since 1.0
	 */ 
	function custom_pagination( $page,$total_pages,$limit,$adjacents,$targetpage='' )
	{
				/* Setup page vars for display. */
		if ($page == 0) $page = 1;					//if no page var is given, default to 1.
		$prev = $page - 1;							//previous page is page - 1
		$next = $page + 1;							//next page is page + 1
		$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
		$lpm1 = $lastpage - 1;						//last page minus 1
		
		/* 
			Now we apply our rules and draw the pagination object. 
			We're actually saving the code to a variable in case we want to draw it more than once.
		*/
		$pagination = "";
		/*if ( is_admin() ) {
			$pagintesign = "&";
		} else {
			if ( get_option('permalink_structure') ) { 
					$pagintesign = "?";
				} else {  
				$pagintesign = '&';  
			}
		}*/
		if($lastpage > 1)
		{	
			$pagination .= "<div class=\"tablenav-pages\">";
			
			//previous button
			$pagination .=  '<span class ="pagination-links">';
			if ($page > 1) {
				$pagination.= "<a class= 'first-page' href='".$targetpage."pageno=1'>«</a>";
				$pagination.= "<a class= 'first-page' href='".$targetpage."pageno=$prev'>‹</a>";
			}
			else {
				$pagination.= "<span><a class= 'first-page disabled' href='javascript:void(0);' >«</a></span>";
				$pagination.= "<span><a class= 'first-pagew disabled'  href = 'javascript:void(0);' >‹</a></span>";
			}
			
			
			//pages
			if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
			{	
				for ($counter = 1; $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
					else
						$pagination.= "<a href='".$targetpage."pageno=$counter'>$counter</a>";					
				}
			}
			elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
			{
				//close to beginning; only hide later pages
				if($page < 1 + ($adjacents * 2))		
				{
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
					{
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a href='".$targetpage."pageno=$counter'>$counter</a>";					
					}
					$pagination.= "...";
					$pagination.= "<a href= '".$targetpage."pageno=$lpm1'>$lpm1</a>";
					$pagination.= "<a href= '".$targetpage."pageno=$lastpage'>$lastpage</a>";		
				}
				//in middle; hide some front and some back
				elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
				{
					$pagination.= "<a href= '".$targetpage."pageno=1'>1</a>";
					$pagination.= "<a href= '".$targetpage."pageno=2'>2</a>";
					$pagination.= "...";
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
					{
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a href='".$targetpage."pageno=$counter'>$counter</a>";					
					}
					$pagination.= "...";
					$pagination.= "<a href= '".$targetpage."pageno=$lpm1'>$lpm1</a>";
					$pagination.= "<a href= '".$targetpage."pageno=$lastpage'>$lastpage</a>";		
				}
				//close to end; only hide early pages
				else
				{
					$pagination.= "<a href='".$targetpage."pageno=1'>1</a>";
					$pagination.= "<a href='".$targetpage."pageno=2'>2</a>";
					$pagination.= "...";
					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
					{
						if ($counter == $page)
							$pagination.= "<span class=\"current\">$counter</span>";
						else
							$pagination.= "<a href='".$targetpage."pageno=$counter'>$counter</a>";					
					}
				}
			}
			
			//next button
			if ($page < $counter - 1) {
				$pagination.= "<a class = 'last-page' href='".$targetpage."pageno=$next'>›</a>";
				$pagination.= "<a class = 'last-page' href='".$targetpage."pageno=$lastpage'>»</a>";
			}
			else {
				$pagination.= "<span class=\"disabled\"><a class = 'last-page disabled' >›</a></span>";
				$pagination.= "<span class=\"disabled\"><a class = 'last-page disabled' >»</a></span>";
			}
				
			$pagination .=  '</span>';
			$pagination.= "</div>\n";
		}
		return $pagination;
	}// end pagination function
	
	/*
	 * Function to get limited words from a string
	 * @since 1.0
	 */ 
	function shorten_string($string, $wordsreturned) {
			/*  Returns the first $wordsreturned out of $string.  If string
			contains fewer words than $wordsreturned, the entire string
			is returned.
			*/
			$retval = $string;      //  Just in case of a problem
			 
			$array = explode(" ", $string);
			if (count($array)<=$wordsreturned)
			/*  Already short enough, return the whole thing
			*/
			{
				$retval = $string;
			}
			else
			/*  Need to chop of some words
			*/
			{
				array_splice($array, $wordsreturned);
				$retval = implode(" ", $array)." ...";
			}
			return $retval;
	}// end function  to shortend the string
	
	/*
	 * Date time function to get live status
	 * @since 1.0
	 */ 
	function wiziq_get_datetime ( $classtime, $duration, $timezone ) {
		date_default_timezone_set( $timezone );
		$currenttime = date('Y-m-d H:i:s');
		$durationsec = $classtime.'+'.$duration.' minutes'; 
		$new_time = date('Y-m-d H:i:s', strtotime($durationsec));
		if((strtotime($currenttime) >= strtotime($classtime)) && (strtotime($currenttime) <= strtotime($new_time))){
			return true;
		}
		else  {
			return false;
		}
	}//end function to get live status 
	
	/*
	 * Function for getting the page url on frontend
	 * This function checks if its front page or any other page
	 * @since 1.0
	 */ 
	function wiziq_frontend_url_structure () {
		if ( is_front_page()  ) {
			//echo '?'." is permerlin from front <br>";
			return '?';
		}
		else {
			if ( get_option('permalink_structure') ) { 
				//echo '?'." is permerlin from front <br>";
				return '?'; 
			} else {  
				//echo '&'." is permerlin from front <br>";
				return '&';  
			}
		}
	}// end url structure
	
	/*
	 * Function to check if a coulmn exist in a table or not
	 * Pass table name with prefix
	 * Pass coulmn name
	 * @since 1.0
	 */ 
	 function wiziq_table_exist_check ( $tablename, $columnname ) {
		 global $wpdb;
		 $checkqry = $wpdb->query("SHOW COLUMNS FROM $tablename LIKE '$columnname'");
		 if ( !empty ($checkqry) ) {
			 return true;
		 }
		 else {
			 return false;
		 }
	 }
	
}
