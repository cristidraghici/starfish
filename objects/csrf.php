<?php
if (!class_exists('starfish')) { die(); }

/**
 * CSRF protection
 *
 * @package starfish
 * @subpackage starfish.objects.csrf
 */
class csrf
{
	// Name of the parameter to use in the verification
	public $parameter = 'csrf_verification';
	
	// The default configuration
	public $config = array(
		'refresh_on_check'=>true,
		
		'post'=>true,
		'put'=>true,
		'head'=>true,
		'options'=>true,
		'get'=>false
	);


	/**
	 * Init function
	 */
	public function init() 
	{
		$this->generate();
	}
	
	/**
	 * Set a new configuration for the object
	 * @param array $options New configuration list
	 */
	public function setConfig($options) 
	{
		$this->config = array_merge($this->config, $options);
	}

	/**
	 * Change the default parameter name
	 * @param string $string New name
	 */
	public function setVerifyParameter($string)
	{
		$this->parameter = $string;
	}

	/**
	 * Generate a new key
	 * @param boolean [$force=false] If true, then the token is forced to refresh
	 */
	public function generate($force=false) 
	{
		if (session('csrf_token') == null || $fprce == true)
		{
			session('csrf_token', md5(md5(time()) . time()));
		}
	}

	/**
	 * Return the token
	 * @return string The token
	 */
	public function get() 
	{
		return session('csrf_token');
	}

	/**
	 * Check if the token was sent or not
	 * @return boolean True if correct
	 */
	public function check() 
	{
		$checked = '';
		
		switch (strtolower(method())) 
		{
			case 'post':
			$checked = post( $this->parameter );
			break;
			case 'put':
			$checked = put( $this->parameter );
			break;
			case 'head':
			$checked = head( $this->parameter );
			break;
			case 'options':
			$checked = options( $this->parameter );
			break;
			case 'get':
			$checked = get( $this->parameter );
			break;
		}
		
		if ($checked == $this->get() || $this->config[strtolower(method())] != true)
		{
			if ($this->config['refresh_on_check'] === true)
			{
				$this->generate(true);
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if the token is correct, die if not correct
	 */
	public function hardCheck()
	{
		if ($this->check() == false) 
		{
			die('CSRF protection triggered.');
		}
	}
	
	/**
	 * Alter the forms inside a html code to contain the csrf token
	 * @todo https://regex101.com/r/sJ1nT5/1
	 * 
	 * @param  string $html The HTML code
	 * @return string The modified code
	 */
	public function alterForms($html) 
	{
		//$html = preg_replace('#<form (.*?)>(.*?)</form>#ism', "<form$1><input type='hidden' name='".$this->parameter."' value='".$this->token."'>$2</form>", $html);
		
		if ($this->config['post'] === true)
		{
			$html = preg_replace('/<form([^>]*)method=[\'|\"]post[\'|\"]([^>]*)>(.*?)<\/form>/ism', "<form$1method='post'$2> <input type='hidden' name='".$this->parameter."' value='".$this->get()."'>$3</form>", $html);
		}
		if ($this->config['put'] === true)
		{
			$html = preg_replace('/<form([^>]*)method=[\'|\"]put[\'|\"]([^>]*)>(.*?)<\/form>/ism', "<form$1method='put'$2> <input type='hidden' name='".$this->parameter."' value='".$this->get()."'>$3</form>", $html);
		}
		if ($this->config['head'] === true)
		{
			$html = preg_replace('/<form([^>]*)method=[\'|\"]head[\'|\"]([^>]*)>(.*?)<\/form>/ism', "<form$1method='head'$2> <input type='hidden' name='".$this->parameter."' value='".$this->get()."'>$3</form>", $html);
		}
		if ($this->config['options'] === true)
		{
			$html = preg_replace('/<form([^>]*)method=[\'|\"]options[\'|\"]([^>]*)>(.*?)<\/form>/ism', "<form$1method='options'$2> <input type='hidden' name='".$this->parameter."' value='".$this->get()."'>$3</form>", $html);
		}
		if ($this->config['get'] === true)
		{
			$html = preg_replace('/<form([^>]*)method=[\'|\"]get[\'|\"]([^>]*)>(.*?)<\/form>/ism', "<form$1method='get'$2> <input type='hidden' name='".$this->parameter."' value='".$this->get()."'>$3</form>", $html);
		}
		
		return $html;
	}
}
?>