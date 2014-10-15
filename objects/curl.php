<?php
if (!class_exists('starfish')) { die(); }

/**
 * Get files with cURL
 *
 * @package starfish
 * @subpackage starfish.objects.curl
 *
 * @see https://github.com/hackerone/curl/blob/master/Curl.php
 */
class curl
{	
        // Configuration for the requests
        private $config = array();

        // Private variable storing request and response information
        private $information = array();

        public function init()
        {
                if (!extension_loaded('curl')) { starfish::obj('errors')->error(400, "PHP required extension - curl - not loaded."); }

                $this->config = array(
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HEADER => false,
                        CURLOPT_VERBOSE => true,
                        CURLOPT_AUTOREFERER => true,
                        CURLOPT_CONNECTTIMEOUT => 30,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
                        CURLOPT_ENCODING => "",       // handle all encodings
                        CURLOPT_USERAGENT => "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",

                        CURLOPT_COOKIEJAR => starfish::config('_starfish', 'storage') .'system/storage/_cookie.txt',
                        CURLOPT_COOKIEFILE => starfish::config('_starfish', 'storage') .'system/storage/_cookie.txt'
                );
                
                $this->information = array();
                
                return true;
        }

        /**
         * Set a config option
         * 
         * @param string $name Name of the option
         * @param string $value Value for the option
         */
        public function setOption($name, $value)
        {
                $this->config[$name] = $value;
        }

        ###############
        # Execute
        ###############
        
        /**
         * Make a single get request
         */
        public function quickGet($url)
        {
                return $this->single( $this->get($url) );
        }
        
        /**
         * Make a single request
         * 
         * @param array $request Request information
         */
        public function single($request)
        {
                // Set the options for the request
                $url = $request['exec_url'];
                $options = $request['options'];
                $id = $request['id'];
                
                // Store the request
                $this->information['_request'] = $request;
                
                // Reset the info about the requests
                $this->resetInfo();

                // Init the request
                $ch = curl_init($url);
                curl_setopt_array($ch, $options);
                $output = curl_exec($ch);

                $this->information['_status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if(!$output)
                {
                        $this->information['_error'] = curl_error($ch);
                        $this->information['_info'] = curl_getinfo($ch);
                }
                else 
                {
                        $this->information['_info'] = curl_getinfo($ch);
                }

                if(@$options[CURLOPT_HEADER] == true)
                {
                        list($header, $output) = $this->_processHeader($output, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
                        $this->information['_header'] = $header;
                }

                curl_close($ch);

                return $output;
        }

        /**
         * Make multiple requests
         * 
         * @param array $requests Requests information
         */
        public function multiple($requests)
        {
                if (!isset($requests[0]) || !is_array($requests[0])) { $requests = array($requests);}
                $this->_status = array();
                
                // Reset the info about the requests
                $this->resetInfo();
                
                // Add handles for each of the urls
                $mh = curl_multi_init();

                $handles = array();
                
                foreach ($requests as $key=>$value)
                {
                        $ch = curl_init( $value['exec_url'] );
                        curl_setopt_array( $ch, $value['options'] );

                        curl_multi_add_handle($mh, $ch);

                        $handles[] = array(
                                'id'       => $value['id'],
                                'handle'    => $ch
                        );

                        // store the request
                        $this->information['_request'][ $value['id'] ] = $value;
                }

                // Run the download
                $running=null;
                do 
                {
                        curl_multi_exec($mh, $running);

                        // added a usleep for 0.25 seconds to reduce load
                        usleep (250000);
                }
                while ($running > 0);

                // Get the content of the urls (if there is any)
                for($i=0; $i<count($handles); $i++)
                {
                        $id = $handles[$i]['id'];
                        $ch = $handles[$i]['handle'];
                        $output = curl_multi_getcontent($ch);

                        $this->information['_status'][$id] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                        if(!$output)
                        {
                                $this->information['_error'][$id] = curl_error($ch);
                                $this->information['_info'][$id] = curl_getinfo($ch);
                        }
                        else 
                        {
                                $this->information['_info'][$id] = curl_getinfo($ch);
                        }

                        if(@$options[CURLOPT_HEADER] == true)
                        {
                                list($header, $output) = $this->_processHeader($output, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
                                $this->information['_header'][$id] = $header;
                        }
                        
                        $content[ $id ] = $output;
                        
                        curl_multi_remove_handle($mh, $ch);
                }

                // Close the multi curl handle to free system resources
                curl_multi_close($mh);

                unset($mh, $handles);

                // Return the contents
                return $content;
        }

        /**
         * Return the info about requests
         * 
         * @return array Information about the requests executed
         */
        public function info()
        {
                return $this->information;
        }

        /** 
         * Reset the info about the requests executed
         */
        public function resetInfo()
        {
                // Reset the info about the requests
                $this->information = array();

                return true;
        }

        /**
         * Create an id for the requested page
         * 
         * @param array $request The request information provided by $this->get(), $this->post(), $this->put(), $this->delete()
         * @return string MD5 serialized string for the $request param
         */
        private function id($request=array())
        {
                return md5(serialize($request));
        }

        ###############
        # Methods
        ###############
        /**
         * Single get request
         */
        public function get($url, $params=array(), $config=array())
        {
                $exec_url = $this->buildUrl($url, $params);
                $options = $config + $this->config;

                $return = array(
                        'method'   => 'get',
                        'exec_url' => $exec_url,
                        'options'  => $options
                );
                $return['id'] = $this->id($return);

                return $return;
        }
        /**
         * Post request
         */
        public function post($url, $params=array(), $data=null, $config=array())
        {
                $exec_url = $this->buildUrl($url, $params);

                $options = $config + $this->config;
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $data;
                
                $return = array(
                        'method'   => 'post',
                        'exec_url' => $exec_url,
                        'options'  => $options
                );
                $return['id'] = $this->id($return);
                
                return $return;
        }
        /**
         * Put request
         */
        public function put($url, $params=array(), $data=null, $config=array())
        {
                $exec_url = $this->buildUrl($url, $params);

                $f = fopen('php://temp', 'rw+');
                fwrite($f, $data);
                rewind($f);
                $options = $config + $this->config;
                $options[CURLOPT_PUT] = true;
                $options[CURLOPT_INFILE] = $f;
                $options[CURLOPT_INFILESIZE] = strlen($data);

                $return = array(
                        'method'   => 'put',
                        'exec_url' => $exec_url,
                        'options'  => $options
                );
                $return['id'] = $this->id($return);

                return $return;
        }
        /**
         * Delete request
         */
        public function delete($url, $params=array(), $config=array())
        {
                $exec_url = $this->buildUrl($url, $params);

                $options = $config + $this->config;
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';

                $return = array(
                        'method'   => 'delete',
                        'exec_url' => $exec_url,
                        'options'  => $options
                );
                $return['id'] = $this->id($return);

                return $return;
        }

        /**
         * Make a format for the url
         * 
         * @param string $url The url resource
         * @param array $data More data to parse
         * 
         * @see https://github.com/hackerone/curl/blob/master/Curl.php
         */
        public function buildUrl($url, $data = array())
        {
                if (!is_array($data)) { $data = array(); }
                $parsed = parse_url($url);
                
                isset($parsed['query']) ? parse_str($parsed['query'], $parsed['query']) : $parsed['query'] = array();
                $params = isset($parsed['query']) ? $data + $parsed['query'] : $data;
                $parsed['query'] = ($params) ? '?' . http_build_query($params) : '';
                if (!isset($parsed['path'])) {
                        $parsed['path']='/';
                }
                $parsed['port'] = isset($parsed['port'])?':'.$parsed['port']:'';
                return $parsed['scheme'].'://'.$parsed['host'].$parsed['port'].$parsed['path'].$parsed['query'];
        }
        /**
         * Convert header output to readable 
         */
        public function _processHeader($response, $header_size)
        {
                return array(substr($response, 0, $header_size), substr($response, $header_size));
        }
}
?>