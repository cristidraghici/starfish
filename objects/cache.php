<?php
if (!class_exists('starfish')) { die(); }

/**
 * Cache files on disk
 *
 * @package starfish
 * @subpackage starfish.objects.cache
 */
class cache
{	
        // The default path to the cache files
        public $path = '';
        // Cache expiration in minutes; 0 - forever
        public $expires = 0;

        /**
         * Init the object
         */
        function init()
        {
                // Set the path to the storage files
                $this->path = starfish::config('_starfish', 'storage') . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
                if (!file_exists($this->path)) { starfish::obj('files')->w($this->path . 'index.html', 'Silence is golden.'); }

                // Set the expiration for the file
                $this->expires = 0;
        }

        /**
         * Set the global number of minutes until the cache expires
         * 
         * @param number $minutes The number of minutes
         */
        function setExpiration($minutes)
        {
                $this->expires = $minutes;
                return true;
        }

        /**
         * Create the filename for the cache file
         * 
         * @param string $file The filename/Url to use in caching
         */
        function name($file)
        {
                return $this->path . starfish::obj('files')->filename_validator( substr($file, -100) ) . '-' . md5($file);
        }

        /**
         * Quickly return the content of a file 
         * 
         * @param string $file The filename/Url to use in caching
         * @param number $expires Number of minutes until the cache expires, available only for this file
         */
        function quick($file, $expires=null)
        {
                if ($this->exists($file, $expires))
                {
                        return $this->get($file);
                }

                return false;
        }

        /**
         * Check if a file exists inside the cache and if it is still available
         * 
         * @param string $file The filename/Url to use in caching
         * @param number $expires Number of minutes until the cache expires, available only for this file
         * 
         * @return boolean Whether the cache exists and is available or not
         */
        function exists($file, $expires=null)
        {
                if (!is_numeric($expires)) { $expires = $this->expires; }

                // Convert the filename into the cache format
                $file = $this->name($file);

                // Check the existence
                if (file_exists($file))
                {
                        if ($this->expires == 0)
                        {
                                return true;
                        }

                        $difference = time() - ( filemtime($file) + $expires * 60 );
                        if ($difference <= 0)
                        {
                                return true;
                        }
                }

                return false;
        }

        /**
         * Read the content of a cache file
         * 
         * @param string $file The filename/Url to use in caching
         */
        function get($file)
        {
                $file = $this->name($file);

                return starfish::obj('files')->r($file);
        }

        /**
         * Write the content of a cache file
         * 
         * @param string $file The filename/Url to use in caching
         * @param string $content The content of the cached file
         */
        function add($file, $content)
        {
                $file = $this->name($file);

                return starfish::obj('files')->w($file, $content);
        }
}
?>