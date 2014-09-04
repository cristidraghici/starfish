<?php
if (!class_exists('starfish')) { die(); }

/**
 * Scraper to download webpages
 * 
 * The database connection is shared between parsers and belongs to the Starfish Framework installation
 * 
 * @package starfish
 * @subpackage starfish.objects.htmlparser
 * 
 * @see https://github.com/cristidraghici/PHPparser
 * @todo  Check the existence of the tables and stored procedures
 */
class scraper
{	// The name of the mysql database to use for parsing, as specified in the config file
        private $connectionName = null; 
        
        // The details about the project
        private $project_id = null;
        private $project_name = "";
        
        // Store the processing functions
        private $processing_functions = array();
        
        // Store the download/processing status of files
        public $status = array();
        
        /**
         * Set the connection to the database
         * 
         * @param string $name Name of the connection
         */
        public function setConnection($name)
        {
                // Change the connection name
                $this->connectionName = $name;
                
                // Check the install
                $this->checkInstall();
                
                return true;
        }
        
        /**
         * Check the parser install
         */
        private function checkInstall()
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
         * Set the project name
         * 
         * @param string $name Name of the project
         */
        public function setProject($name)
        {
                $this->project_name = $name;
                
                $resource = starfish::obj('database')->query("select _project_get_id('".$name."') as nr_crt;");
                $row = $query->fetch( $resource );
                starfish::obj('database')->free( $resource );
                
                $this->project_id = $row['nr_crt'];
                
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
         * Download the established urls - starts a download process for the urls inside the database
         * 
         * 
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         * 
         * @return boolean True if the process still needs to continue
         */
        public function download($project_id, $group_id=null)
        {
                if ($this->status($project_id, $group_id) == true)
                {
                        // Download the project files
                        $resource = starfish::obj('database')->query("select _url_set_downloading(''), url, group_id limit 0, 10;");
                        $rows = $query->fetchAll( $resource );
                        starfish::obj('database')->free( $resource );
                        
                        
                        
                        // Process - apply the group_id corresponding callback function

                        return true;
                }
                else
                {
                        return false;
                }
        }
        
        /**
         * Process the downloaded urls
         * - return a list of urls with content to be processed by outside functions
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         */
        public function process($project_id, $group_id, $callback)
        {
                $this->processing_functions [ $project_id ][ $group_id ] = $callback;
                return true;
        }
        
        /**
         * Return the status of parsing from the database
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         * 
         * @return array        
         *              - total - total number of pages to download
         *              - current - number of files downloaded so far
         *              - processed - number of files processed
         *              - finished - boolean - whether the process is finished or not
         */
        public function status($project_id, $group_id=null)
        {
                $this->status[$project_id][$group_id] = array();
                
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
}
?>