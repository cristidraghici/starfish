<?php
if (!class_exists('starfish')) { die(); }

class proxy
{
    public $url = '';
    public $data = array();
    public $result = '';
    
    function init($url=null)
    {
        $this->url();
        $this->data();
        
        if ($this->data['data'] == null)
        {
            $this->result = $this->send_request();
        }
        else
        {
            $http = new http();
            $http->init($this->data['host'], $this->data['port'], $this->data['data']);
            $http->path($this->data['path']);
            
            $this->result = $http->send_request();
        }
        
        $this->process_request();
    }
    
    /*
    Get the request information
    */
    function url($url=null)
    {
        if ($url == null)
        {
            if(array_key_exists('HTTP_SERVERURL', $_SERVER))
            {
              $url = $_SERVER['HTTP_SERVERURL'];
            }
            else
            {
              $url = $_REQUEST['url'];
            }
        }
        
        $this->url = $url;
    }
    function data()
    {
        $parsed = @parse_url($this->url);
        $this->data['host'] = isset($parsed['host']) ? $parsed['host'] : 'localhost';
        $this->data['path'] = isset($parsed['path']) ? $parsed['path'] : '';
        $this->data['path'] = isset($parsed['query']) ? $this->data['path'] . '?' . $parsed['query'] : $this->data['path'];
        $this->data['port'] = isset($parsed['port']) ? $parsed['port'] : '80';
        
        $this->data['contenttype'] = isset($_REQUEST['contenttype']) ? $_REQUEST['contenttype'] : 'text/xml';
        $this->data['data'] = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : null;
        
        
    }
    
    /*
    Action and Result processing
    */
    function send_request()
    {
        $ch = curl_init();
        $timeout = 5; // set to zero for no timeout
        
        // fix to allow HTTPS connections with incorrect certificates
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        curl_setopt ($ch, CURLOPT_URL, $this->url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt ($ch, CURLOPT_ENCODING , "gzip, deflate");
        curl_setopt ($ch, CURLOPT_HEADER , 0);
        
        $file_contents = curl_exec($ch);
        curl_close($ch);
        $lines = array();
        $lines = explode("\n", $file_contents);
        
        if(!($response = $lines))
        {
            echo "Unable to retrieve file ".$url;
        }
        $response = implode("", $response);
        
        return $response;
    }
    function process_request()
    {
        $len = strlen($this->result);
        $pos = strpos($this->result, "<");
        if($pos > 1)
        {
            $this->result = substr($this->result, $pos, $len);
        }
        else
        {
            $pos = strpos($this->result, "{");
            if($pos > 1)
            {
                $this->result = substr($this->result, $pos, $len);
            }
        }
        
        echo $this->result;
    }
}
?>