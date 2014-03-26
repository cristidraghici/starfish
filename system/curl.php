<?php
/*
 Inspired by:
	https://github.com/ajimix/asana-api-php-class/blob/master/asana.php
*/
if (!class_exists('starfish')) { die(); }

define("METHOD_POST", 1);
define("METHOD_PUT", 2);
define("METHOD_GET", 3);

class curl
{
	private $timeout = 10;
    private $debug = false;
    private $advDebug = false;
	
	public function get($url, $data = null, $method = METHOD_GET)
	{
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Don't print the result
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // Don't verify SSL connection
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // "" ""
        //curl_setopt($curl, CURLOPT_USERPWD, $this->apiKey);
        //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); // Send as JSON
        if ($this->advDebug)
		{
            curl_setopt($curl, CURLOPT_HEADER, true); // Display headers
            curl_setopt($curl, CURLOPT_VERBOSE, true); // Display communication with server
        }
        if ($method == METHOD_POST)
		{
            curl_setopt($curl, CURLOPT_POST, true);
        }
		else if ($method == METHOD_PUT)
		{
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        }
        if(!is_null($data) && ($method == METHOD_POST || $method == METHOD_PUT))
		{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
		
        try {
            $return = curl_exec($curl);
            $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
            if($this->debug || $this->advDebug)
			{
                echo "<pre>"; print_r(curl_getinfo($curl)); echo "</pre>";
            }
        }
		catch(Exception $ex)
		{
            if($this->debug || $this->advDebug)
			{
                echo "<br>cURL error num: ".curl_errno($curl);
                echo "<br>cURL error: ".curl_error($curl);
            }
            echo "Error on cURL";
            $return = null;
        }
		
        curl_close($curl);
		
        return $return;
    }
}
?>