<?php
if (!class_exists('starfish')) { die(); }

/**
 * Templating system
 *
 * @package starfish
 * @subpackage starfish.objects.mysql
 */

class tpl
{
        /**
	 * Declare used variables
	 *
	 * $path - Path to the files
	 * $parts - Blocks of code
	 * 
	 * $dump - Used for ob_start() efficiency
	 */
        public $path = '';
        public $parts = array();

        private $dump = false;


        /**
	 * Init the script
	 */
        public function init()
        {
                // Init the path
                $this->path = starfish::config('_starfish', 'template');

                if (substr($this->path, -1) != '/')
                {
                        $this->path .= '/';
                }

                // Current path
                $current = starfish::config('_starfish', 'site_url') . substr( starfish::obj('parameters')->path() , 1);

                // Store automatic variables
                $this->set('site_url', starfish::config('_starfish', 'site_url') );
                $this->set('site_title',starfish::config('_starfish', 'site_title') );
                $this->set('site_description', starfish::config('_starfish', 'site_description') );
                $this->set('/', starfish::config('_starfish', 'site_url') );
                $this->set('./', $current );

                return true;
        }

        /*
         * Set a variable for the templating system
         * 
         * @param string $variable Name of the variable
         * @param string $value Value for the variable
         */
        public function set($variable, $value)
        {
                $list = starfish::get('_starfish_templates');
                $list[$variable] = $value;

                return starfish::set('_starfish_templates', $list);
        }

        /*
         * Get the value of a variable
         * 
         * @param string $variable Name of the variable to retrive
         * @return mixed Value of the variable
         */
        public function get($variable)
        {
                $list = starfish::get('_starfish_templates');

                return isset( $list[$variable] ) ?  $list[$variable] : null;
        }

        /*
         * Get a view
         * 
         * @param string $file - File to load
         * @param mixed $variables - Variables to insert into the code
         */
        public function view($file, $variables=null)
        {
                $html = '';

                // Alter and establish the input data
                if (is_array($variables))
                {
                        extract($variables);
                }

                // Add the file termination, if needed
                if (substr($file, -8) != '.tpl.php')
                {
                        $tplFile = $this->path . $file . '.tpl.php';
                }
                else
                {
                        $tplFile = $file;
                }

                $tplContent = starfish::obj('files')->r($tplFile);

                if ($tplContent)
                {
                        // Remove safeguards
                        $tplContent = str_replace(array(
                                '<'.'?php /* Starfish Framework Template protection */ die(); ?'.'>' . PHP_EOL,
                                '<'.'?php /* Starfish Framework Template protection */ die(); ?'.'>',
                        ), '', $tplContent);

                        // Include predefined variables
                        $tplContent = $this->variables($tplContent);

                        // Remove spaces for SWITCH to work properly
                        $tplContent = preg_replace('#\?>([\s]+)<\?php#is', '?><?php', $tplContent);

                        ob_start();
                        eval(' ?>'.$tplContent.'<?php ');
                        $html = ob_get_clean();
                }

                return $html;

        }

        /*
         * Dump the template in the browser
         */
        public function dump()
        {
                if ($this->dump == false)
                {
                        $this->dump = true;
                        ob_start();
                }
                else
                {
                        $html = ob_get_clean();
                        ob_start();

                        // Include predefined variables
                        $html = $this->variables($html);

                        // Remove empty var spaces
                        $html = preg_replace('#{([A-Za-z0-9_^}\w]+)}#i', '', $html);

                        // Move meta to head
                        $meta = '';
                        preg_match_all('/<meta([^>]+)>/i', $html, $matches);
                        foreach ($matches[0] as $key=>$value)
                        {
                                $html = str_replace($value, '', $html);
                                $meta .= PHP_EOL.$value;
                        }
                        $html = str_ireplace('<head>', "<head>".PHP_EOL.$meta, $html);

                        echo $html;
                }
        }

        /*
         * Insert the stored variables into the template
         * 
         * @param string $html HTML code to alter
         * @return string The new HTML code
         */
        private function variables($html)
        {
                $variables = starfish::get('_starfish_templates');

                foreach ($variables as $key=>$value)
                {
                        $html = str_replace('{'.$key.'}', $value, $html);
                }
                return $html;
        }
}

/**
* Aliases used by class for easier programming
*/
function view()   { return call_user_func_array(array( obj('tpl') , 'view'),    func_get_args()); }
?>