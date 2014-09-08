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
        $path = '';
        // Cache expiration in minutes; 0 - forever
        $expires = 0;
        
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
         * Quickly return the content of a file 
         * 
         * @param string $file The filename/Url to use in caching
         */
        function quick($file)
        {
                $file = $this->name($file);
                
                if ($row = $this->exists($this->path . $file))
                {
                        return $row;
                }
                
                return false;
        }
        /**
         * Create the filename for the cache file
         * 
         * @param string $file The filename/Url to use in caching
         */
        function name($file)
        {
                $file = substr($file, -100) . '-' . md5($file);
                return $file;
        }
        
        /**
         * Read the content of a cache file
         * 
         * @param string $file The filename/Url to use in caching
         */
        function r($file)
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
        function w($file, $content)
        {
                $file = $this->name($file);
                
                return starfish::obj('files')->w($file, $content);
        }
        /**
         * Check if a file exists inside the cache and if it is still available
         * 
         * @param string $file The filename/Url to use in caching
         */
        function exists($file)
        {
                $file = $this->name($file);
                
                if (file_exists($file))
                {
                        if ($this->expires == 0)
                        {
                                return true;
                        }
                        
                        $difference = time() - $this->expires * 60;
                        if ($difference <= 0)
                        {
                                return true;
                        }
                }
                return false;
        }
}
?>