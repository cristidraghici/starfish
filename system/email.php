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
	/**
	 * Send email - shortcut function
	 * @param string $to      Destination
	 * @param string $subject Subject
	 * @param string $message The message itself
	 */
	public function sendmail($to, $subject, $message)
	{
		$this->send($to, $subject, $message);
	}

	/**
	 * Send email - original function
	 * @param string $to_email                         Destination
	 * @param string $subject                          Subject
	 * @param string $message                          Message
	 * @param string [$to_name=null]                   Name of the recipient
	 * @param string [$from_name='no-reply']           Name of the sender
	 * @param string [$from_email='no-reply@site.com'] Email of the sender
	 */
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