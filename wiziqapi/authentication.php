<?php
class CheckapicredentailsClass
{
	
	
	function Checkapicredentails($secretAcessKey,$access_key,$webServiceUrl)
	{
		require_once("AuthBase.php");
		$authBase = new wiziq_authBase($secretAcessKey,$access_key);
		$method = "get_account_info";
		$requestParameters["signature"]=$authBase->wiziq_generateSignature($method,$requestParameters);
		$wiziq_httpRequest=new wiziq_httpRequest();
		try
		{
			$XMLReturn=$wiziq_httpRequest->wiziq_do_post_request($webServiceUrl.'?method=get_account_info',http_build_query($requestParameters, '', '&')); 
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
			return 1;
		}
		else if($attribNode=="fail")
		{	
			$error=$objDOM->getElementsByTagName("error")->item(0);
			$apierror['code'] = $error->getAttribute("code"); 
   			$apierror['msg'] = $error->getAttribute("msg");	
			return $apierror;
		}
	 }//end if	
   }//end function
	
}
?>
