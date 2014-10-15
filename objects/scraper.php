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
        // Limit the simultaneous download results
        private $simultaneousDownloads = 10;
        
        // Limit the simultaneous process results
        private $simultaneousProcessing = 20;

        // The name of the mysql database to use for parsing, as specified in the config file
        private $connectionName = null; 

        // The details about the project
        private $project_id = null;
        private $project_name = "";

        // Store the processing functions
        private $processing_functions = array();

        // Store the download/processing status of files
        public $status = array();

        // Messages
        public $message_total = 1;
        public $message_page = 1;
        
        // Path to the shutdown file
        public $shutdown = '';

        /**
         * The init function
         */
        public function init()
        {
                // Unlimited resources
                set_time_limit(0);
                ini_set("memory_limit", -1);

                // For the browser
                if (starfish::$constants['cli'] == false)
                {
                        //prevent apache from buffering it for deflate/gzip
                        header("Content-type: text/html");
                        header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

                        // Turn off output buffering
                        ini_set('output_buffering', 'off');
                        // Turn off PHP output compression
                        ini_set('zlib.output_compression', false);

                        //Flush (send) the output buffer and turn off output buffering
                        //ob_end_flush();
                        while (@ob_end_flush());

                        // Implicitly flush the buffer(s)
                        ini_set('implicit_flush', true);
                        ob_implicit_flush(true);
                }
                
                // Remove the emergency shutdown file, if needed
                $this->shutdown = starfish::config('_starfish', 'storage') . '/_shutdown';
                if (file_exists($this->shutdown))
                {
                        @unlink( $this->shutdown );
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
         * Reset the download and process queue for a project
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         */
        public function resetQueue($project_id, $group_id=null)
        {
                // Build where clause
                $where = "where project_id='".$project_id."'";
                if (is_numeric($group_id)) { $where .= " and group_id='".$group_id."'"; }

                // Update the process status
                $resource = starfish::obj('database')->query("update urls set status_process=1 ".$where);
                starfish::obj('database')->free( $resource );

                // Update the download status
                $resource = starfish::obj('database')->query("update urls set status_download=1 ".$where." and `type`=1");
                starfish::obj('database')->free( $resource );

                return true;
        }

        /**
         * Reset the download queue for a project
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         */
        public function resetDownloadQueue($project_id, $group_id=null)
        {
                // Build where clause
                $where = "where project_id='".$project_id."'";
                if (is_numeric($group_id)) { $where .= " and group_id='".$group_id."'"; }

                $resource = starfish::obj('database')->query("update urls set status_download=1 ".$where);
                starfish::obj('database')->free( $resource );

                return true;
        }

        /**
         * Reset the process queue for a project
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         */
        public function resetProcessQueue($project_id, $group_id=null)
        {
                // Build where clause
                $where = "where project_id='".$project_id."'";
                if (is_numeric($group_id)) { $where .= " and group_id='".$group_id."'"; }

                $resource = starfish::obj('database')->query("update urls set status_process=1 ".$where);
                starfish::obj('database')->free( $resource );

                return true;
        }

        /**
         * Stop the queue execution
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         */
        public function stopQueue($project_id, $group_id=null)
        {
                // Build where clause
                $where = "where project_id='".$project_id."'";
                if (is_numeric($group_id)) { $where .= " and group_id='".$group_id."'"; }

                $resource = starfish::obj('database')->query("update urls set status_download=3, status_process=2 ".$where);
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
         * @param string $method Method to use for downloading the urls
         * @param array $parameters Parameters used in the request
         * @param array $data Data to send with the request
         * @param array $storage Data to use when parsing this url
         */
        public function addUrl($project_id, $group_id, $type, $url, $method='get', $parameters=array(), $data=array(), $storage=array())
        {
                // Alter the parameters for storage
                @ksort($parameters);
                $parameters = @serialize($parameters);
                @ksort($data);
                $data = @serialize($data);
                @ksort($storage);
                $storage = @serialize($storage);
                
                // Sanitize the data
                $project_id = starfish::obj('database')->sanitize($project_id);
                $group_id = starfish::obj('database')->sanitize($group_id);
                $type = starfish::obj('database')->sanitize($type);
                $url = starfish::obj('database')->sanitize($url);
                $method = starfish::obj('database')->sanitize($method);
                $parameters = starfish::obj('database')->sanitize($parameters);
                $data = starfish::obj('database')->sanitize($data);
                $storage = starfish::obj('database')->sanitize($storage);
                
                // Add the url to the database
                $resource = starfish::obj('database')->query("select _url_add('".$project_id."','".$group_id."','".$type."','".$url."','".$method."','".$parameters."','".$data."', '".$storage."')");
                $rows = starfish::obj('database')->fetchAll( $resource );
                starfish::obj('database')->free( $resource );

                return true;
        }

        /**
         * Remove a url from the list
         * 
         * @param number $project_id Id of the project in use
         * @param number $group_id Group of urls inside the project
         * @param string $url URL to download
         * @param string $method Method to use for downloading the urls
         * @param array $parameters Parameters used in the request
         * @param array $data Data to use when parsing this url
         */
        public function removeUrl($project_id, $group_id, $url, $method='get', $parameters=array(), $data=array())
        {
                // Alter the parameters for storage
                @ksort($parameters);
                $parameters = @serialize($parameters);
                @ksort($data);
                $data = @serialize($data);
                @ksort($storage);
                $storage = @serialize($storage);
                                
                // Sanitize the data
                $project_id = starfish::obj('database')->sanitize($project_id);
                $group_id = starfish::obj('database')->sanitize($group_id);
                $url = starfish::obj('database')->sanitize($url);
                $method = starfish::obj('database')->sanitize($method);
                $parameters = starfish::obj('database')->sanitize($parameters);
                $data = starfish::obj('database')->sanitize($data);
                
                // Add the url to the database
                $resource = starfish::obj('database')->query("delete from urls where project_id='".$project_id."' and group_id='".$group_id."' and url='".$url."' and method='".$method."' and parameters='".$parameters."' and data='".$data."'");
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
                
                // Halt the execution, if shutdown is enforced
                if (file_exists($this->shutdown))
                {
                        return false;
                }
                
                // Do the downloading and processing
                if ($status['downloaded'] < $status['total'])
                {
                        // Build where clause
                        $where = "where status_download=1 and project_id='".$project_id."'";
                        if (is_numeric($group_id)) { $where .= " and group_id='".$group_id."'"; }

                        // Get a list of the files to download, together with updating their download status
                        $resource = starfish::obj('database')->query("select nr_crt, url, method, parameters, data, storage, group_id from urls ".$where." order by nr_crt asc limit 0, ".$this->simultaneousDownloads );
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
                                $value['data'] = unserialize($value['data']);
                                $value['parameters'] = unserialize($value['parameters']);
                                $value['storage'] = unserialize($value['storage']);

                                // ensure data is ok
                                $value['method'] = strtolower($value['method']);
                                if (!in_array($value['method'], array('get', 'post', 'put', 'delete'))) { $value['method'] = 'get'; }

                                // build the request list
                                switch ($value['method'])
                                {
                                        case 'get':
                                        case 'delete':
                                                $request = starfish::obj('curl')->{$value['method']}($value['url'], $value['parameters']);
                                                break;
                                        case 'put':
                                        case 'post':
                                                // To be reviewed
                                                $request = starfish::obj('curl')->{$value['method']}($value['url'], $value['parameters'], $value['data']);
                                                break;
                                                
                                }
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

                                // Update the content
                                $resource = starfish::obj('database')->query("insert into url_download(url_id, content) values('{url_id}', '{content}') on duplicate key update content='{content}'", null,
                                                                             array(
                                                                                     'url_id'=>$info['_request'][$key]['row']['nr_crt'],
                                                                                     'content'=>$value
                                                                             )
                                                                            );
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
                                        $content[$key] = $callback($value, $info['_request'][$key]['row']['storage'] );
                                }

                                // update the process status
                                $resource = starfish::obj('database')->query("select _url_set_process(".$info['_request'][$key]['row']['nr_crt'].", 2)");
                                starfish::obj('database')->free( $resource );
                        }

                        return array(
                                'info'          => $info,
                                'content'       => $content
                        );
                }
                elseif ($status['processed'] < $status['total'])
                {
                        // Build where clause
                        $where = "where status_process=1 and project_id='".$project_id."'";
                        if (is_numeric($group_id)) { $where .= " and group_id='".$group_id."'"; }

                        // Get a list of the files to download, together with updating their download status
                        $resource = starfish::obj('database')->query("select nr_crt, url, method, parameters, data, storage, group_id from urls ".$where." order by nr_crt asc limit 0, ".$this->simultaneousProcessing );
                        $rows = starfish::obj('database')->fetchAll( $resource );
                        starfish::obj('database')->free( $resource );

                        foreach ($rows as $key=>$value)
                        {
                                // alter the retrieved data for usage
                                $value['data'] = unserialize($value['data']);
                                $value['parameters'] = unserialize($value['parameters']);
                                $value['storage'] = unserialize($value['storage']);

                                // ensure data is ok
                                $value['method'] = strtolower($value['method']);
                                if (!in_array($value['method'], array('get', 'post', 'put', 'delete'))) { $value['method'] = 'get'; }

                                // build the request list
                                switch ($value['method'])
                                {
                                        case 'get':
                                        case 'delete':
                                                $request = starfish::obj('curl')->{$value['method']}($value['url'], $value['parameters']);
                                                break;
                                        case 'put':
                                        case 'post':
                                                // To be reviewed
                                                $request = starfish::obj('curl')->{$value['method']}($value['url'], $value['parameters'], $value['data']);
                                                break;
                                }
                                
                                $request['row'] = $value;

                                $requests[ ] = $request;
                        }

                        // Process the downloaded result - apply the group_id corresponding callback function
                        foreach ($requests as $key=>$value)
                        {
                                $resource = starfish::obj('database')->query("select url_id, content from url_download where url_id='".$value['row']['nr_crt']."'" );
                                $row = starfish::obj('database')->fetch( $resource );
                                starfish::obj('database')->free( $resource );

                                // Reset the callback
                                $callback = null;

                                // Get and apply the callback we wanted
                                if ($group_id == null && isset( $this->processing_functions [ $project_id ][ $value['row']['group_id'] ] ))
                                {
                                        $callback = $this->processing_functions [ $project_id ][ $value['row']['group_id'] ];
                                }
                                elseif ( isset( $this->processing_functions [ $project_id ][ $group_id ] ) )
                                {
                                        $callback = $this->processing_functions [ $project_id ][ $group_id ];
                                }

                                if ($callback != null)
                                {
                                        $content[$key] = $callback($row['content'], $value['row']['storage'] );
                                }
                                else
                                {
                                        $content[$key] = $row['content'];
                                }

                                $info[$key] = $value;

                                // update the process status
                                $resource = starfish::obj('database')->query("select _url_set_process(".$value['row']['nr_crt'].", 2)");
                                starfish::obj('database')->free( $resource );
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
         * Method to test the functions which will be added in the process lists
         * 
         * @param function $callback
         *                      - $html - the downloaded html to processing
         *                      - $data - the suplimentary data to use when processing
         * @param mixed $request
         *                      - case type is string, then a url request is sent on the get method
         *                      - case type is object, then $request is the request object itself
         * @param array $data Data to use when processing the HTML
         */
        public function processFunction($callback, $request, $data)
        {
                // Request the HTML
                if (gettype($request) == 'string')
                {
                        $request = starfish::obj('curl')->get($request);
                }
                $html = starfish::obj('curl')->single($request);

                // Execute the callback
                $callback($html, $data);

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
                // Halt the execution, if shutdown is enforced
                if (file_exists($this->shutdown))
                {
                        return $this->status[$project_id][$group_id] = array(
                                'total'         => 0,
                                'downloaded'    => 0,
                                'processed'     => 0,
                                'finished'      => true
                        );
                }
                
                if (!is_numeric($group_id))
                {
                        $resource = starfish::obj('database')->query("select 
                                (select count(*) from urls where project_id=".$project_id.") as total, 
                                (select count(*) from urls where project_id=".$project_id." and (status_download=2 or status_download=3 or status_download=4) ) as downloaded, 
                                (select count(*) from urls where project_id=".$project_id." and status_process=2) as processed
                        ");
                }
                else
                {
                        $resource = starfish::obj('database')->query("select 
                                (select count(*) from urls where project_id=".$project_id." and group_id=".$group_id.") as total, 
                                (select count(*) from urls where project_id=".$project_id." and group_id=".$group_id." and (status_download=2 or status_download=3 or status_download=4) ) as downloaded, 
                                (select count(*) from urls where project_id=".$project_id." and group_id=".$group_id." and status_process=2) as processed
                        ");
                }

                $row = starfish::obj('database')->fetch( $resource );
                starfish::obj('database')->free( $resource );

                if ($row['total'] == $row['processed'])
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
         * @param number $max The maximum messages to show before the content of the page is reset
         */
        public function message($text, $max=50)
        {
                // Show the current message
                if (starfish::$constants['cli'] == false)
                {
                        echo '<span>' . $this->message_total . '. </span>' . $text . '<span> ('.starfish::memory_usage().' memory used)</span>';
                }
                else
                {
                        echo $this->message_total . ' ' . $text;
                }

                // Access the helper
                $this->message_helper($max);

                return true;
        }

        /**
         * Show css for displaying the messages
         * 
         * @param number $max The maximum messages to show before the content of the page is reset
         */
        public function message_helper($max)
        {
                if (starfish::$constants['cli'] == false)
                {
                        echo '<br />' . PHP_EOL; 

                        if ($this->message_page >= $max)
                        {
                                echo '<SCRIPT LANGUAGE=JavaScript>document.body.innerHTML = "";</SCRIPT>';
                                $this->message_page = 1;
                                
                        }
                        
                        if ($this->message_total == 0 || $this->message_page == 1)
                        {
                                echo '<style type="text/css">'.
                                        'html, body {font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 11px;}'.
                                        ' a {color: #0099FF; text-decoration: none;} a:hover{text-decoration: underline;}'.
                                        ' span {color: #ccc; text-decoration: none;}'.
                                        '</style>';

                                //Flush (send) the output buffer and turn off output buffering
                                //ob_end_flush();
                                while (@ob_end_flush());

                                // Implicitly flush the buffer(s)
                                ini_set('implicit_flush', true);
                                ob_implicit_flush(true);
                        }

                        for($k = 0; $k < 1000; $k++) { echo ' '; }
                        @ob_flush();
                        @flush();
                }
                else
                {
                        echo PHP_EOL;
                }

                $this->message_total++;
                $this->message_page++;
                return true;
        }
}
?>