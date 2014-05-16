<?php
if (!isset($path)) { die(); }

trait routing
{
    public static function on($method, $path, $callback=null)
    {        
        if ($callback != null)
        {
            // Get the parameters
            $params = array();
            preg_match_all('#/::([^/\s]*)#is', $path, $matches, PREG_SET_ORDER);
            foreach ($matches as $key=>$value)
            {
                $path = str_replace($value[0], '/*', $path);
                $params[] = $value[1];
            }
            
            switch (gettype($callback))
            {
                case 'array':
                    starfish::$routing[strtolower($method)][$path] = array(
                        'callback'  => $callback['callback'],
                        'class'     => $callback['class'],
                        'params'    => $params
                    );
                    break;
                
                default:
                    // Store the path
                    if (is_callable($callback))
                    {
                        starfish::$routing[strtolower($method)][$path] = array('callback'=>$callback, 'params'=>$params);
                        return true;
                    }
                    break;
            }
        }
        else
        {
            $callback   = '';
            $params     = array();
            
            // List the routes
            if (isset(starfish::$routing[$method]))
            {
                $all = starfish::$routing[$method];
                $routes = array_keys($all);
				
                // Get the proper function
                foreach ($routes as $key=>$check)
                {
                    $check = trim($check, '/');
                    $check = '#^'.str_replace('*', '([^\/]*)', $check).'$#is';
                    
                    #echo $path . ' - ' . $check . ' - '. preg_match($check, $path, $match) . ' - '. $match[1] ."<br>\n";
                    if (preg_match($check, $path, $match))
                    {
                        $route = $all[$routes[$key]];
                        
                        $callback = $route['callback'];
                        $list = $route['params'];
						
                        foreach ($list as $key=>$value)
                        {
                            $params[$value] = $match[$key+1];
                        }
                    }
                }
            }
            
            // Extract the parameters
            if (is_callable($callback))
            {
                call_user_func_array($callback, $params);
            }
            else
            {
                // Extract object from route
                preg_match('#([^\/]*)#is', $path, $match);
                $object = starfish::obj($match[1]);
                if ($object != false && (int)method_exists($object, 'exec') == 1)
                {
                    $object->exec();
                }
                elseif ($object == false)
                {
                    starfish::error(400, 'Bad request');
                }
            }
        }
        
        return true;
    }
}

?>