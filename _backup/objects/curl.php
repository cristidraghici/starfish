<?php
/*
 Inspired by:
	https://github.com/ajimix/asana-api-php-class/blob/master/asana.php
*/
if (!class_exists('starfish')) { die(); }

class curl
{
    private $timeout = 10;
    public  $apiKey = '';
    public  $requestContentType = 'json';
    public  $debugInfo = '';
    
    public function curl()
    {
		if (!extension_loaded('curl')) { starfish::error(400, "PHP required extension - curl - not loaded."); }
        return true;
    }
    
    public function get($url, $data, $method='get')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Don't print the result
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // Don't verify SSL connection
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // "" ""
        if (strlen($this->apiKey) > 0)
        {
            curl_setopt($curl, CURLOPT_USERPWD, $this->apiKey);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: " . $this->type() )); // Send as JSON
        /*
        if ($this->advDebug)
		{
            curl_setopt($curl, CURLOPT_HEADER, true); // Display headers
            curl_setopt($curl, CURLOPT_VERBOSE, true); // Display communication with server
        }
        */
        
        switch (strtolower($method))
        {
            case 'delete':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            
            case 'put':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            
            case 'post':
                curl_setopt($curl, CURLOPT_POST, true);
                break;
            
            default:
            case 'get':
                $method = 'get';
                break;
        }
        
        if(!is_null($data) && $method != 'get')
		{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        
        try {
            $return = curl_exec($curl);
            $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
            $this->debugInfo = curl_getinfo($curl);
        }
		catch(Exception $ex)
		{
            $this->debugInfo = array(
                'no'    => curl_errno($curl),
                'error' => curl_error($curl)
            );
            
            $return = null;
        }
		
        curl_close($curl);
		
        return $return;
    }
    
    public function type()
    {
        switch ($this->requestContentType)
        {
            default:
            case 'json':
                return 'application/json';
                break;
        }
        
        return false;
    }
}
?>