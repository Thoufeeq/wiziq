<?php
class wiziq_authBase
{
	private $secretAcessKey="";
	private $access_key="";
	public function __construct($secretAcessKey,$access_key)
	{
		$this->secretAcessKey=$secretAcessKey;
		$this->access_key=$access_key;
	}
function wiziq_generateTimeStamp()
{
	return time();
}

function wiziq_generateSignature($methodName,&$requestParameters) {
$signatureBase="";
$secretAcessKey = urlencode($this->secretAcessKey);
$requestParameters["access_key"] = $this->access_key;
$requestParameters["timestamp"] =$this->wiziq_generateTimeStamp();
$requestParameters["method"] = $methodName;

foreach ($requestParameters as $key => $value)
{
	if(strlen($signatureBase)>0)
	$signatureBase.="&";
	$signatureBase.="$key=$value";
}
//echo "<br>signatureBase=".$signatureBase;
return base64_encode($this->wiziq_hmacsha1($secretAcessKey, $signatureBase));
}

function wiziq_hmacsha1($key,$data) { 
    $blocksize=64;
    $hashfunc='sha1';
    if (strlen($key)>$blocksize)
        $key=pack('H*', $hashfunc($key));
    $key=str_pad($key,$blocksize,chr(0x00));
    $ipad=str_repeat(chr(0x36),$blocksize);
    $opad=str_repeat(chr(0x5c),$blocksize);
    $hmac = pack(
                'H*',$hashfunc(
                    ($key^$opad).pack(
                        'H*',$hashfunc(
                            ($key^$ipad).$data
                        )
                    )
                )
            );
    return $hmac;
}

}//end class AuthBase

class wiziq_httpRequest
{
		function wiziq_do_post_request($url, $data, $optional_headers = null)
		  {  
			$params = array('http' => array(
						  'method' => 'POST',
						  'content' => $data
					   ));
			if ($optional_headers !== null) 
			{
				$params['http']['header'] = $optional_headers;
			}
			$ctx = stream_context_create($params);
			$fp = @fopen($url, 'rb', false, $ctx);
			if (!$fp) 
			{
				throw new Exception("Problem with $url, $php_errormsg");
			}
			$response = @stream_get_contents($fp);
			if ($response === false) 
			{
				throw new Exception("Problem reading data from $url, $php_errormsg");
			}
			 
			 return $response;
		  }
}//end class HttpRequest
