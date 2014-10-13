<?php
if (!class_exists('starfish')) { die(); }

class examples
{
        function getExamples()
        {
                $path = starfish::config('_starfish', 'root') . '_examples' . DIRECTORY_SEPARATOR;
                $examples = array();
                
                $Markdown = new Markdown();
                
                $all = obj('files')->all( $path );
                $folders = $all['folders'];
                
                natsort($folders);
                
                foreach ($folders as $key=>$value)
                {
                        if (file_exists($path . $value . DIRECTORY_SEPARATOR . 'README.md'))
                        {
                                $examples[$key]['title'] = strtoupper($value);
                                $examples[$key]['content'] = $Markdown->transform( r( $path . $value . DIRECTORY_SEPARATOR . 'README.md' ) );
                                $examples[$key]['modified'] = date("Y-m-d", starfish::obj('files')->directorymtime( $path . $value ) );
                                
                                $screenshot = $path . $value . DIRECTORY_SEPARATOR . 'screenshot.png';
                                $storage = starfish::config('_starfish', 'storage') . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $value . '-screenshot.png';
                                if (file_exists($screenshot) && !file_exists($storage))
                                {
                                        @copy($path . $value . DIRECTORY_SEPARATOR . 'screenshot.png', $storage);
                                }
                                
                                if (file_exists($storage))
                                {
                                        $examples[$key]['screenshot'] = 'storage/projects/' . $value . '-screenshot.png';
                                }
                                else
                                {
                                        $examples[$key]['screenshot'] = 'public/images/example.jpg';
                                }
                        }
                }
                
                return $examples;
        }
}
?>