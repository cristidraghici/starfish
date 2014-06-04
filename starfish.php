<?php
/**
 * @author  Cristi DRAGHICI
 * @link    http://blog.draghici.net
 * @version 0.2a
 * 
 * @see     Parts from Dispatch PHP micro-framework were used.
 * @link    https://github.com/noodlehaus/dispatch
 * @license MIT
 * @link    http://opensource.org/licenses/MIT
 */

/** Entry point: file aggregator */

if (!class_exists('starfish'))
{
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR;
    $files = array(
        // Traits
        'config.php',
        'variables.php',
        'registry.php',
        'routing.php',
        'mvc.php',
        'exec.php',
        'errors.php'
    );
    
    if (PHP_VERSION_ID >= 50400)
    {
        /*
        * The minimum PHP 5.4 requirement is met.
        */
        
        // Include the system files
        foreach ($files as $value)
        {
            require_once( $path . $value);
        }
        
        // Include the main file
        require_once( $path . '_starfish.php');
    }
    elseif (PHP_VERSION_ID >= 50000)
    {
        /*
        * Backwards compatibility: make the framework available even for PHP versions older than 5.4 by loading the code of all the traits
        *
        * -- still testing
        */
        class traitSimulator
        {
            private $starfish   = '';
            
            private $path   = '';
            private $traits = array();
            private $files  = array();
            
            function __construct($path, $files)
            {
                $this->path  = $path;
                $this->files = $files;
            }
            
            function exec()
            {
                // Load all the traits into memory
                foreach ($this->files as $value)
                {
                    $this->loadTrait($this->path . $value);
                }
                
                // Load the main file into the memory
                $this->loadStarfish();
                
                // Eval the starfish code
                eval('php'. '?' . '>' . $this->starfish . '<' . '?');
            }
            
            function loadStarfish()
            {
                // Load the file content
                $this->starfish = @file_get_contents($this->path . '_starfish.php');
                
                // Replace the init sequence
                preg_match_all('#\s(.*)::init\(\);#', $this->starfish, $matches);
                foreach ($matches[1] as $key=>$value)
                {
                    if (isset($this->traits[ trim($value) ]))
                    {
                        $this->starfish = str_replace(
                            trim($value) . '::init();',
                            $this->traits[ trim($value) ]['init'],
                            $this->starfish
                        );
                    }
                }
                
                // Replace the use sequences
                preg_match_all('#use (.*);#', $this->starfish, $matches);
                foreach ($matches[1] as $key=>$value)
                {
                    if (isset($this->traits[ trim($value) ]))
                    {
                        $this->starfish = str_replace(
                            'use ' . trim($value) . ';',
                            $this->traits[ trim($value) ]['content'],
                            $this->starfish
                        );
                    }
                }
                
                // Replace starfish reference
                $this->starfish = str_replace('starfish::', 'self::', $this->starfish);
            }
            
            function loadTrait($file)
            {
                // Read the file
                $content = trim(@file_get_contents($file));
                
                // Extract the trait sequence
                $content = substr($content, strpos($content, 'trait'));
                $content = substr($content, 0, strrpos($content, '}') + 1);
                
                // Extract the name
                preg_match('#trait\s*([^\{]*?)\s*{#is', $content, $match);
                $title = trim($match[1]);
                // clean it from the content
                $content = substr($content, strpos($content, '{') + 1);
                $content = substr($content, 0, strrpos($content, '}'));
                
                // Extract the init() sequence
                preg_match('#public static function init(.*?)function#is', $content, $match);
                $init = trim($match[0]);
                $init = substr($init, 0, strrpos($init, '}') + 1);
                // clean the content of the init function
                $content = str_replace($init, '', $content);
                
                $init = substr($init, strpos($init, '{') + 1);
                $init = substr($init, 0, strrpos($init, '}'));
                $init = preg_replace('#return (.*);#', '', $init );
                
                // Store the Trait content
                $this->traits[$title] = array(
                    'content'   => $content,
                    'init'      => $init
                );
                
                return true;
            }
        }
        
        $traitSimulator = new traitSimulator($path, $files);
        $traitSimulator->exec();
    }
    else
    {
        die('Your PHP version is outdated.');
    }
    
    // Init the framework
    starfish::init();
    
    // Include the aliases
    require_once( $path . '_aliases.php');
}
?>