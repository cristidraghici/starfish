<?php
if (!isset($path)) { die(); }

trait registry
{
    public static function obj($name)
    {
        if (isset(starfish::$config['obj-alias'][$name]))
        {
            $name = starfish::$config['obj-alias'][$name];
        }
        
        if (!isset(starfish::$objects[$name]))
        {
            /*
             * Create the api object
             */
            // if class exists within the current file
            if (class_exists($name))
            {
                starfish::$objects[$name] = new $name;
            }
            // if class exists within the system file
            elseif (file_exists( starfish::$config['root'] . 'objects/' . $name . '.php' ))
            {
                include( starfish::$config['root'] . 'objects/' . $name . '.php' );
                if (class_exists($name))
                {
                    starfish::$objects[$name] = new $name;
                }
                else
                {
                    if (starfish::$config['debug'] == false) { starfish::error(400, "Bad request."); }
                    
                    starfish::error(400, "Class '".$name."' does not exist.");
                    return false;
                }
            }
            // if class exists in the custom required objects list
            elseif (file_exists( starfish::$config['objects'] . $name . '.php' ))
            {
                include( starfish::$config['objects'] . $name . '.php' );
                if (class_exists($name))
                {
                    starfish::$objects[$name] = new $name;
                }
                else
                {
                    if (starfish::$config['debug'] == false) { starfish::error(400, "Bad request."); }
                    
                    starfish::error(400, "Class '".$name."' does not exist.");
                    return false;
                }
            }
            // debug error
            elseif (strlen($name) > 0)
            {
                if (starfish::$config['debug'] == false) { starfish::error(400, "Bad request."); }
                
                starfish::error(400, "File '".$name."' does not exist.");
                return false;
            }
            // silent error
            else
            {
                starfish::error(400, "Bad request.");
                return false;
            }
        }
        
        return starfish::$objects[$name];
    }
}

?>