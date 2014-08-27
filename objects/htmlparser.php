<?php
if (!class_exists('starfish')) { die(); }

/**
 * HTML Parser
 * 
 * The database connection is shared between parsers and belongs to the Starfish Framework installation
 * 
 * @package starfish
 * @subpackage starfish.objects.htmlparser
 * 
 * @see https://github.com/cristidraghici/PHPparser
 * @todo  Check the existence of the tables and stored procedures
 */
class htmlparser
{	
        // The name of the mysql database to use for parsing, as specified in the config file
        private $connectionName = null; 
        
        /**
         * Check the parser install
         */
        public function checkInstall()
        {
                // check the connection to the mysql database
                if (starfish::obj('database')->get($this->connectionName) == null)
                {
                        starfish::obj('errors')->error(400, 'MySQL connection not available for htmlparser');
                        return false;
                }
                $connectionInfo = starfish::obj('database')->connectionInfo($this->connectionName);
                if ($connectionInfo['type'] != 'mysql')
                {
                        starfish::obj('errors')->error(400, 'MySQL connection needed for htmlparser');
                        return false;
                }
                
                return true;
        }
        
        /**
         * Set the connection to the database
         * 
         * @param string $name Name of the connection
         */
        public function setConnection($name)
        {
                $this->connectionName = $name;
                
                return true;
        }
        
        /**
         * Run the parser for the given title
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         */
        public function run($project_id, $group_id)
        {
                
                return true;
        }
        
        
        /**
         * Return the status of parsing from the database
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         */
        public function status($project_id, $group_id)
        {
                                
                return true;
        }
        /**
         * Output a message to the browser/command line
         * 
         * @param string $text The text of the message
         */
        public function message($text)
        {
                echo $message;
                
                return true;
        }
        

        /**
         * Add urls to the list
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         * @param string $url URL to download
         * @param array $data Data to use when parsing this url
         */
        public function addUrl($project_id, $group_id, $url, $data=array())
        {
                return true;
        }
        
        /**
         * Download the established urls
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         */
        public function download($project_id, $group_id)
        {
                return true;
        }
        
        /**
         * Process the downloaded urls
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         */
        public function process($project_id, $group_id)
        {
                return true;
        }
}
?>