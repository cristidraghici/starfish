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
	// The generated token
	public $token = '';
	// The default configuration
	public $config = array(
		'post'=>true,
		'put'=>true,
		'head'=>true,
		'option'=>true,
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
	 */
	public function generate() 
	{
		if (session('csrf_token') == null)
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
		return $this->token;
	}

	/**
	 * Check if the token was sent or not
	 * @return boolean True if correct
	 */
	public function check() 
	{
		$checked = '';

		switch (method()) 
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
		
		if ($checked == $this->token || $this->config[method()] != true)
		{
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
	 * Alter the forms inside a html code to contain the csrf token - all forms changed
	 * @todo Change the enabling just to the activated checked methods
	 * 
	 * @param  string $html The HTML code
	 * @return string The modified code
	 */
	public function alterForms($html) 
	{
		$html = preg_replace('#<form(.*?)>(.*?)</form>#is', "<form$1><input type='hidden' name='".$this->parameter."' value='".$this->token."'>$2</form>", $html);
		
		return $html;
	}
}
?>