<?php
if (!class_exists('starfish')) { die(); }

class session
{
	private $session = array();
	
	private $init   = false;
	private $prefix = 'session';
	private $enforced = false;
	
	public function session()
	{
		$this->init();
	}
	
	private function init()
	{
		if ($this->init == false)
		{
			$this->init = true;
			if (isset(starfish::$config['session'])) 
			{ 
				$this->prefix = starfish::$config['session']; 
			}
			
			session_start();
			$this->session = $_SESSION;
			session_write_close();
		}
		
		return true;
	}
	
	
	public function start()
	{
		$this->enforced = true;
		session_start();
	}
	public function end()
	{
		$this->enforced = false;
		session_write_close();
	}
	
	
	public function get($variable)
	{
		if (isset( $this->session[$this->prefix . $variable] ))
		{
			return $this->session[$this->prefix . $variable];
		}
		
		return null;
	}
	public function set($variable, $value)
	{
		if ($this->enforced == false) session_start();
		$_SESSION[$this->prefix . $variable] = $value;
		if ($this->enforced == false) session_write_close();
		
		$this->session[$this->prefix . $variable] = $value;
		
		return $value;
	}
	public function del($variable)
	{
		if ($this->enforced == false) session_start();
		unset($_SESSION[$this->prefix . $variable]);
		if ($this->enforced == false) session_write_close();
		
		unset($this->session[$this->prefix . $variable]);
		
		return true;
	}
	
	
    public function millitime()
    {
        $microtime = microtime();
        $comps = explode(' ', $microtime);
        
        return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
    }
}


?>