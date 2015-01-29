<?php
if (!class_exists('starfish')) { die(); }

/**
 * Email wrapper
 *
 * @package starfish
 * @subpackage starfish.system.email
 * 
 * @todo Add gmail/yahoo/stmp support
 * @todo Add core support for email templates (just like tpl's case)
 */
class email
{
	public function sendmail($to, $subject, $message)
	{
		$this->send($to, $subject, $message);
	}

	public function send($to_email, $subject, $message, $to_name=null, $from_name='no-reply', $from_email='no-reply@site.com')
	{
		if ($to_name == null) { $to_name = $to_email; }
		
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// Additional headers
		$headers .= 'To: '.$to_name.' <'.$to_email.'>' . "\r\n";
		$headers .= 'From: '.$from_name.' <'.$from_email.'>' . "\r\n";

		// Mail it
		@mail($to_email, $subject, $message, $headers);
	}
}

/**
* Aliases used by class for easier programming
*/
function sendmail() { return call_user_func_array(array('email', 'sendmail'),    func_get_args()); }

?>