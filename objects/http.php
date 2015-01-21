<?php
if (!class_exists('starfish')) { die(); }

/**
 * Simple manager for HTTP requests
 *
 * @package starfish
 * @subpackage starfish.objects.http
 */

class http
{
	public $config   = array();
	public $request  = array();
	public $response = array();

	private $socket;

	/** Init the script */
	public function http()
	{
		$this->response['request_headers'] = '';
		$this->response['headers'] = '';
		$this->response['body'] = '';
		$this->response['status'] = false;

		$this->config('timeout', 30);
		$this->config('content_type', 'application/xml');
		$this->config('agent', 'PHP Starfish HTTP Client');
	}
	// Shortway for setting GET requests request configuration
	public function url($string)
	{
		$info = @parse_url($string);

		$host = isset($info['host']) ? $info['host'] : '';
		$port = isset($info['port']) ? $info['port'] : ($info['scheme'] == 'http') ? 80 : 443;
		$path = isset($info['path']) ? $info['path'] : '';

		$this->request('host', $host);

		$this->request('path', $path);
		$this->request('port', $port);

		if (isset($info['query']))
		{
			$this->request('data', $info['query']);
		}

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
		// Fixes
		if ($name == 'method') { $value = strtoupper($value); }
		if ($name == 'data') {
			if (gettype($value) == 'array') {
				$new = '';
				foreach ($value as $key=>$value) {
					$new .= "&".$key."=".$value;
				}

				$value = $new;
			}
		}

		// Set the info
		$this->request[$name] = $value;

		return true;
	}

	public function exec()
	{
		if ($this->connect())
		{
			$this->response['request_headers'] =
				$this->request['method']." ".$this->request['path']." HTTP/1.1\r\n".
				"Host: ".$this->request['host'].":".$this->request['port']."\r\n".
				"User-Agent: ".$this->config['agent']."\r\n".
				"Content-Type: ".$this->config['content_type']."\r\n".
				"Content-Length: ".strlen( $this->request['data'] )."\r\n".
				'Connection: close'.
				"\r\n".
				"\r\n".$this->request['data'].
				"\r\n";

			@fwrite($this->socket, $this->response['request_headers']);

			$result = '';
			while(!@feof($this->socket))
			{
				$result .= @fgets($this->socket, 2048);
			}            
			$this->response['result'] = $result;
			$this->disconnect();

			$pos = @strpos($this->response['result'], "\r\n\r\n") + 4;
			$this->response['headers'] = @substr($this->response['result'], 0, $pos);
			$this->response['body']    = @substr($this->response['result'], $pos);
			$this->response['status']  = true;

			return $this->response['body'];
		}

		$this->response['status'] = false;
		return false;
	}

	/** Internal functionality */
	private function connect()
	{
		$host = ($this->request['port'] == 443) ? 'ssl://'.$this->request['host'] : $this->request['host'];

		$this->socket = @fsockopen($host, $this->request['port'], $this->response['no'], $this->response['str'], $this->config['timeout'] );
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