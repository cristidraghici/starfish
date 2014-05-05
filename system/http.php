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


class http2
{
    public $config   = array();
    public $request  = array();
    public $response = array();
    
    private $socket;
    
    /** Init the script */
    public function http()
    {
        $this->config('timeout', 30);
        $this->config('content_type', 'application/xml');
        $this->config('agent', 'PHP Starfish HTTP Client');
    }
    // Shortway for setting GET requests request configuration
    public function url($string)
    {
        return true;
    }
    public function page($host, $port=80, $method="GET", $path='/', $data=array())
    {
        $this->request('host', $host);
        $this->request('port', (int)$port);
        $this->request('method', strtoupper($method));
        $this->request('path', $path);
        $this->request('data', $data);
        
        return true;
    }
    
    // Configuration
    public function config($name, $value)
    {
        $this->config[$name] = $value;
        return true;
    }
    public function request($name, $value)
    {
        $this->request[$name] = $value;
        return true;
    }
    
    public function exec()
    {
        if ($this->connect())
        {
            @fwrite($this->socket,
                $this->request['method']." ".$this->request['path']." HTTP/1.0\r\n".
                "Host:".$this->request['host'].":".$this->request['port']."\r\n".
                "User-Agent: ".$this->config['agent']."\r\n".
                "Content-Type: ".$this->config['content_type']."\r\n".
                "Content-Length: ".strlen( $this->request['data'] ).
                "\r\n".
                "\r\n".$this->request['data'].
                "\r\n"
            );
            
            while(!@feof($this->socket))
            {
                $this->response['result'] .= @fgets($this->socket, 2048);
            }
            $this->disconnect();
            
            $pos = strpos($this->response['result'], "\r\n\r\n") + 4;
            $this->response['headers'] = substr($this->response['result'], 0, $pos);
            $this->response['body']    = substr($this->response['result'], $pos);
            $this->response['status']  = true;
            
            return $this->response['body'];
        }
        
        $this->response['status'] = false;
        return false;
    }
    
    /** Internal functionality */
    private function connect()
    {
        $this->socket = @fsockopen($this->request['host'], $this->request['port'], $this->response['no'], $this->response['str'], $this->config['timeout'] );
        if (!$this->socket)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    private function disconnect()
    {
        @fclose($this->socket);
        
        return true;
    }
}
?>