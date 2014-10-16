<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for logging
 *
 * @package starfish
 * @subpackage starfish.system.logs
 *
 * @todo Maybe an interface to view the content of the logs.
 */
class logs
{
        // The default path to the cache files
        public $path = null;

        /**
	 * Init
	 *
	 * @todo Check the size of the log file, clean it if too big
	 * @todo Establish the default date format
	 */
        public function init()
        {
                // Set the path to the storage files
                $this->path = starfish::config('_starfish', 'storage') . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
                if (!file_exists($this->path)) { starfish::obj('files')->w($this->path . 'index.html', 'Silence is golden.'); }

                return true;
        }

        /**
         * Save information to the log file
         * 
         * @param string $file The name of the file which will store the logs
         * @param mixed $data The data to log
         */
        public function saveLog($file, $data)
        {
                $file = $this->path . $file;

                $data = @json_encode($data) . PHP_EOL;
                $this->add($file, $data . PHP_EOL, 'a');

                return true;
        }

        /**
         * Reset the log file
         * 
         * @param string $file The name of the file which will store the logs
         */
        public function resetLog($file)
        {
                $file = $this->path . $file;

                starfish::obj('files')->w($file, "", 'w');

                return true;
        }

        /**
	 * Write into a log file
	 *
	 * @param string $file The file where to write the content
	 * @param mixed $text The content to store
	 */
        public static function add($file, $text)
        {
                $file = $this->path . $file;

                // Make sure we are dealign with a string
                if (gettype($text) != 'string') { $text = @serialize($text); }

                // write the data
                starfish::obj('files')->w($file, $text, 'a');


                return true;
        }
}

/**
* Aliases used by class for easier programming
*/
function log()   { return call_user_func_array(array('logs', 'add'),    func_get_args()); }
?>