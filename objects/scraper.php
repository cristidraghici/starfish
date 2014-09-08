<?php
if (!class_exists('starfish')) { die(); }

/**
 * Scraper to download lists of links
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
{	
        // Limit the simultaneous results
        private $simultaneousDownloads = 10;
        
        // The name of the mysql database to use for parsing, as specified in the config file
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
                $row = starfish::obj('database')->fetch( $resource );
                starfish::obj('database')->free( $resource );
                
                $this->project_id = $row['nr_crt'];
                
                return true;
        }

        /**
         * Reset the download queue for a project
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         * 
         * @return boolean True if the process still needs to continue
         */
        public function resetQueue($project_id, $group_id=null)
        {
                // Build where clause
                $where = "where project_id='".$project_id."'";
                if (is_numeric($group_id)) { $where .= "and group_id='".$group_id."'"; }
                
                $resource = starfish::obj('database')->query("update urls set status_download=1, status_process=1 ".$where);
                starfish::obj('database')->free( $resource );
        }
        
        /**
         * Add urls to the list
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         * $param number $type 
         *                      1 - download every type the parser runs
         *                      2 - permanent
         * @param string $url URL to download
         * @param array $data Data to use when parsing this url
         */
        public function addUrl($project_id, $group_id, $type, $url, $method='get', $parameters=array(), $data=array())
        {
                // Alter the parameters for storage
                $parameters = @serialize(@ksort($parameters));
                $data = @serialize(@ksort($data));
                
                // Add the url to the database
                $resource = starfish::obj('database')->query("select _url_add('".$project_id."','".$group_id."','".$type."','".$url."','".$method."','".$parameters."','".$data."')");
                $rows = starfish::obj('database')->fetchAll( $resource );
                starfish::obj('database')->free( $resource );
                
                return true;
        }
        
        /**
         * Download the established urls - starts a download process for the urls inside the database
         * 
         * e.g. while ( starfish::obj('scraper')->download(1, 1) ) { starfish::obj('scraper')->message('Download in progress.'); }
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         * 
         * @return boolean True if the process still needs to continue
         */
        public function download($project_id, $group_id=null)
        {
				$status = $this->status($project_id, $group_id);
                if ($status['finished'] == false)
                {
                        // Build where clause
                        $where = "where status_download=1 and project_id='".$project_id."'";
                        if (is_numeric($group_id)) { $where .= "and group_id='".$group_id."'"; }
                        
                        // Get a list of the files to download, together with updating their download status
                        $resource = starfish::obj('database')->query("select nr_crt, url, method, parameters, data, group_id from urls ".$where." limit 0, ".$this->simultaneousDownloads );
                        $rows = starfish::obj('database')->fetchAll( $resource );
                        starfish::obj('database')->free( $resource );
                        
                        // update the status for the selected files
                        foreach ($rows as $key=>$value)
                        {
                                $resource = starfish::obj('database')->query("select _url_set_download(".$value['nr_crt'].", 2)");
                                starfish::obj('database')->free( $resource );
                        }
                        
                        // Download the project files
                        $requests = array();
                        foreach ($rows as $key=>$value)
                        {
                                // alter the retrieved data for usage
                                $value['data'] = @unserialize($value['data']);
                                $value['parameters'] = @unserialize($value['parameters']);
                                
                                // ensure data is ok
                                $value['method'] = strtolower($value['method']);
                                if (!in_array($value['method'], array('get', 'post', 'put', 'delete'))) { $value['method'] = 'get'; }
                                
                                // build the request list
                                $request = starfish::obj('curl')->{$value['method']}($value['url'], $value['parameters']);
                                $request['row'] = $value;
                                
                                $requests[] = $request;
                        }
                        
                        // Execute the query
                        $content = starfish::obj('curl')->multiple($requests);
                        $info = starfish::obj('curl')->info();
                        
                        // Process the downloaded result - apply the group_id corresponding callback function
                        foreach ($content as $key=>$value)
                        {
                                // Reset the callback
                                $callback = null;
                                
                                // Update the status
                                $resource = starfish::obj('database')->query("select _url_set_download(".$info['_request'][$key]['row']['nr_crt'].", 3)");
                                starfish::obj('database')->free( $resource );
                                
                                // Get and apply the callback we wanted
                                if ($group_id == null && isset( $this->processing_functions [ $project_id ][ $info['_request'][$key]['row']['group_id'] ] ))
                                {
                                        $callback = $this->processing_functions [ $project_id ][ $info['_request'][$key]['row']['group_id'] ];
                                }
                                elseif ( isset( $this->processing_functions [ $project_id ][ $group_id ] ) )
                                {
                                        $callback = $this->processing_functions [ $project_id ][ $group_id ];
                                }
                                
                                if ($callback != null)
                                {
                                        $content[$key] = $callback($value, $info['_request'][$key]['row']['data'] );
                                }
                        }

                        return array(
                                'info'          => $info,
                                'content'       => $content
                        );
                }
                else
                {
                        return false;
                }
        }
        
        /**
         * Process the downloaded urls
         * 
         * This method stores inside the current object a list of functions to apply to the downloaded content.
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         * @param function $callback 
         *                      - $html - the downloaded html to processing
         *                      - $data - the suplimentary data to use when processing
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
         *              - downloaded - number of files downloaded so far
         *              - processed - number of files processed
         *              - finished - boolean - whether the process is finished or not
         */
        public function status($project_id, $group_id=null)
        {
                if (!is_numeric($group_id))
                {
                        $resource = starfish::obj('database')->query("select (select count(*) from urls where project_id=".$project_id.") as total, (select count(*) from urls where project_id=".$project_id." and (status_download=3 or status_download=4) ) as downloaded, (select count(*) from urls where project_id=".$project_id." and status_process=2) as processed");
                }
                else
                {
                        $resource = starfish::obj('database')->query("select (select count(*) from urls where project_id=".$project_id." and group_id=".$group_id.") as total, (select count(*) from urls where project_id=".$project_id." and group_id=".$group_id." and (status_download=3 or status_download=4) ) as downloaded, (select count(*) from urls where project_id=".$project_id." and group_id=".$group_id." and status_process=2) as processed");
                }
                
                $row = starfish::obj('database')->fetch( $resource );
                starfish::obj('database')->free( $resource );
                
                if ($row['total'] == $row['downloaded'])
                {
                        $row['finished'] = true;
                }
                else
                {
                        $row['finished'] = false;
                }
                
                // Set the default values
                return $this->status[$project_id][$group_id] = array(
                        'total'=>$row['total'],
                        'downloaded'=>$row['downloaded'],
                        'processed'=>$row['processed'],
                        'finished'=>$row['finished']
                );
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