<?php
if (!class_exists('starfish')) { die(); }

class http
{
    public $config  = array();
    public $request = array();
    
    private $errorNo   = 0;
    private $errorStr  = '';
    
    private $socket;
    public $result  = '';
    
    function http($timeout=30, $contenttype='application/xml', $agent='PHP HTTP Client')
    {
        $this->config = array(
            'timeout'       => $timeout,
            'contenttype'   => $contenttype,
            'agent'         => $agent
        );
    }
    
    function init($host, $port=80, $data=null)
    {
        $this->request = array(
            'host' => $host,
            'port' => $port,
            'data' => $data,
            'path' => '/'
        );
    }
    
    function connect()
    {
        $this->socket = @fsockopen($this->request['host'], $this->request['port'], $this->errorNo, $this->errorStr, $this->config['timeout'] );
        if (!$this->socket)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    function path($str)
    {
        $this->request['path'] = $str;
    }
    
    function send_request($headers=false)
    {
        if ($this->connect())
        {
            @fwrite($this->socket,
                "POST ".$this->request['path']." HTTP/1.0\r\n".
                "Host:".$this->request['host'].":".$this->request['port']."\r\n".
                "User-Agent: ".$this->config['agent']."\r\n".
                "Content-Type: ".$this->config['contenttype']."\r\n".
                "Content-Length: ".strlen( $this->request['data'] ).
                "\r\n".
                "\r\n".$this->request['data'].
                "\r\n"
            );
            
			if ($headers == true)
			{
				$this->result = substr($this->result, strpos($this->result,"\r\n\r\n")+4);
			}
			
            while(!@feof($this->socket))
            {
                $this->result .= @fgets($this->socket, 2048);
            }
            @fclose($this->socket);
            
            return $this->result;
        }
        
        return false;
    }
}
?>