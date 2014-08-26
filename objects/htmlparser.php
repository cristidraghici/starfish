<?php
if (!class_exists('starfish')) { die(); }

/**
 * HTML Parser
 *
 * @package starfish
 * @subpackage starfish.objects.htmlparser
 * 
 * @see https://github.com/cristidraghici/PHPparser
 */
class htmlparser
{	
        // The name of the mysql database to use for parsing, as specified in the config file
        private $connectionName = null; 
        
        /**
         * Run the parser for the given title
         */
        public function run()
        {
                // check the connection to the mysql database

                // check the existence of the tables and stored procedures

                return true;
        }
        
        /**
         * Return the status of parsing from the database
         */
        public function status()
        {
                return true;
        }
        /**
         * Output a message to the browser/command line
         */
        public function message()
        {
                return true;
        }
        

        /**
         * Add urls to the list
         */
        public function addUrl()
        {
                return true;
        }
        
        /**
         * Download the established urls
         */
        public function download()
        {
                return true;
        }
        
        /**
         * Process the downloaded urls
         */
        public function process()
        {
                return true;
        }
}
?>