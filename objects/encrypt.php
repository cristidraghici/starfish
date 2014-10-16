<?php
if (!class_exists('starfish')) { die(); }

/**
 * "Encrypt" string
 *
 * @package starfish
 * @subpackage starfish.objects.encrypt
 */
class encrypt
{	
        /**
	 * Declare used variables
	 *
	 * $hash - String to use for building the corresponding encrypted sting
	 */
        private $hash = 'aBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSTtUuVvWwXxYyZz0123456789.,!$*+-?@#As';

        /**
	 * Constructor
	 */
        function __construct()
        {
                if (strlen(starfish::config('_encrypt', 'hash')) > 0)
                {
                        $this->hash( md5(starfish::config('_encrypt', 'hash')) );
                }
                else
                {
                        $this->hash( md5($this->hash) );
                }

                return true;
        }

        /**
	 * Set a new hash
	 *
	 * @param string $string The new hash
	 */
        public function hash($string=null)
        {
                if ($string != null)
                {
                        $this->hash = $string;
                }

                return true;
        }

        /**
	 * Better base64_encode function
	 *
	 * @param string $string The string
	 *
	 * @return string $string The new string
	 */
        public function safe_b64encode($string)
        {
                $data = base64_encode($string);
                $data = str_replace(array('+','/','='),array('-','_',''),$data);

                return $data;
        }

        /**
	 * Better base64_decode function
	 *
	 * @param string $string The string
	 *
	 * @return string $string The new string
	 */
        public function safe_b64decode($string)
        {
                $data = str_replace(array('-','_'),array('+','/'),$string);
                $mod4 = @strlen($data) % 4;
                if ($mod4)
                {
                        $data .= substr('====', $mod4);
                }
                return @base64_decode($data);
        }

        /**
	 * The main encrypt function
	 *
	 * @param string $string The string
	 * 
	 * @return string $string The new string
	 */
        public function encode($string)
        { 
                if(!$string) { return false; }

                $text = $string;
                $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
                $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
                $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->hash, $text, MCRYPT_MODE_ECB, $iv);

                return trim($this->safe_b64encode($crypttext)); 
        }

        /**
	 * The main decrypt function
	 *
	 * @param string $string The string
	 * 
	 * @return string $string The new string
	 */
        public function decode($string)
        {
                if(!$string) { return false; }

                $crypttext = $this->safe_b64decode($string); 
                $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
                $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
                $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->hash, $crypttext, MCRYPT_MODE_ECB, $iv);

                return trim($decrypttext);
        }

        /**
         * Create a password hash 
         * 
         * @param string $string String which acts like password
         * @return string Encoded password hash
         */
        function password_hash($string)
        {
                return md5(md5($string . starfish::config('_starfish', 'site_url') )  . starfish::config('_starfish', 'site_url') );	
        }
}
?>