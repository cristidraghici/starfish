<?php
if (!class_exists('starfish')) { die(); }

/**
 * Basic captcha for forms
 *
 * @package starfish
 * @subpackage starfish.objects.captcha
 */
class captcha
{	
        // Config the captcha
        var $captcha_number_of_chars    = 5; // number of chars in the captcha string

        /**
         * Init the object
         */
        function init()
        {
        }

        /*
        * Create a new captcha code
        */        
        function refreshCaptcha()
        {
                starfish::obj('parameters')->session('captcha', $this->random_string() );
                return true;
        }
        /**
         * Return an existing captcha code
         */
        function returnCaptcha()
        {
                if (strlen( starfish::obj('parameters')->session('captcha') ) != $this->captcha_number_of_chars)
                {
                        $this->refreshCaptcha();
                }
                return starfish::obj('parameters')->session('captcha');
        }
        /**
         * Generate a random captcha
         */
        function random_string()
        {
                $nr_chars = $this->captcha_number_of_chars;
                $chars = "ABCDEFGHIJKLMNOPQRSTUVXYZabcdefghijkmnopqrstuvwxyz0123456789~!@#$%^&*";
                
                srand((double)microtime()*1000000);
                $i = 1;
                $text = '' ;
                while ($i <= $nr_chars)
                {
                        $num = rand() % 33;
                        $tmp = substr($chars, $num, 1);
                        $text = $text . $tmp;
                        $i++;
                }
                
                return $text;
        }
        
        /**
         * Generate an image
         * 
         * @param number $height The height of the captcha image
         * @param number $width The width of the captcha image
         * @param string $string The text to show as captcha
         * @param array $bgs The list with background image files
         */
        function captcha_image($height, $width, $string, $bgs)
        {
                $captcha = imagecreatetruecolor($width, $height);
                $black = imagecolorallocate($captcha, 0, 0, 0);

                $bgs = array_values($bgs);
                $bg = $bgs[rand(0,(count($bgs)-1))];
                list($bgWidth, $bgHeight) = getimagesize($bg);    
                $bgImg = imagecreatefrompng($bg); 
                $bgX = rand(0, $bgWidth-$width);
                $bgY = rand(0, $bgHeight-$height);
                imagecopy($captcha, $bgImg, 0, 0, $bgX, $bgY, $width, $height);
                imagedestroy($bgImg);

                $line = imagecolorallocate($captcha,200,0,0);
                imageline($captcha,0,0,39,29,$line);
                imageline($captcha,40,0,64,29,$line);

                imagestring($captcha, 10, 15, 10, $string, $black);

                header( 'Cache-Control: no-store, no-cache, must-revalidate' );
                header( 'Cache-Control: post-check=0, pre-check=0', false );
                header( 'Pragma: no-cache' );
                header( 'Content-type: image/png' );
                imagepng($captcha);

                exit;
        }
}
?>