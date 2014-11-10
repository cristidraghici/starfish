<?php
if (!class_exists('starfish')) { die(); }

/**
 * Extjs helper class
 *
 * @package starfish
 * @subpackage starfish.objects.extjs
 */
class extjs
{	
        /**
	 * Init the object
	 */
        public static function init()
        {
        }

        // Extract the filters from the filter string
        function filterslist($string)
        {
                $data = array();

                if (strlen($string) > 0)
                {
                        $params = @json_decode($string, true);

                        if (count($params) > 0)
                        {
                                foreach ($params as $key=>$value)
                                {
                                        $data[$value['property']] = $value['value'];
                                }
                        }
                }


                return $data;
        }
        // Extract the sort properties
        function sortprops($params, $allowed=array(), $default='', $corespondence=array() )
        {
                $string = '';

                if (strlen($params) > 0)
                {
                        $params = @json_decode($params, true);
                        if (count($params) > 0)
                        {
                                $string = 'order by  ';
                                foreach ($params as $key => $value)
                                {
                                        if (in_array($value['property'], $allowed) && strlen($value['direction']) > 0 && in_array(strtolower($value['direction']), array('asc', 'desc')))
                                        {
                                                if (isset($corespondence[$value['property']]))
                                                {
                                                        $string .= $corespondence[$value['property']] . ' ' . $value['direction'] . ', ';
                                                }
                                                else
                                                {
                                                        $string .= $value['property'] . ' ' . $value['direction'] . ', ';
                                                }
                                        }
                                }
                                if (strlen($string) > 10) { $string = substr($string, 0, -2); } else { $string = ''; }
                        }
                }

                if (strlen($string) == 0) { $string = $default; }

                return $string;
        }
        // Extract the filter properties
        function filterprops($params, $allowed=array(), $default='', $corespondence=array() )
        {
                $string = 'where 1=1 ';

                $exactMatch = array();
                foreach ($allowed as $key=>$value)
                {
                        if (substr($value, -1) == '*')
                        {
                                $allowed[$key] = substr($value, 0, -1);
                                $exactMatch[ $allowed[$key] ] = true;
                        }
                        else
                        {
                                $exactMatch[ $allowed[$key] ] = false;
                        }
                }

                if (strlen($params) > 0)
                {
                        $params = @json_decode($params, true);

                        if (count($params) > 0)
                        {
                                foreach ($params as $key => $value)
                                {
                                        if (in_array($value['property'], $allowed) && strlen($value['value']) > 0 && (string)$value['value'] != 'null')
                                        {
                                                if ($exactMatch[ $value['property'] ] == true)
                                                {
                                                        if (isset($corespondence[$value['property']]))
                                                        {
                                                                $string .= "and ".$corespondence[$value['property']] . " = '" . strtolower( $value['value'] ) . "' ";
                                                        }
                                                        else
                                                        {
                                                                $string .= "and ".$value['property'] . " = '" . strtolower( $value['value'] ) . "' ";
                                                        }
                                                }
                                                else
                                                {
                                                        if (isset($corespondence[$value['property']]))
                                                        {
                                                                $string .= "and lower(".$corespondence[$value['property']] . "::varchar) like '%" . strtolower($value['value']) . "%' ";
                                                        }
                                                        else
                                                        {
                                                                $string .= "and lower(".$value['property'] . "::varchar) like '%" . strtolower($value['value']) . "%' ";
                                                        }
                                                }
                                        }
                                }

                                $string = substr($string, 0, -1);
                        }
                }

                return $string;
        }
        // Make the limit string
        function limitprops($params=array())
        {
                $limits = '';

                if (count($params) > 0)
                {
                        if (is_numeric($params['start'])) { $start = $params['start']; }
                        if (is_numeric($params['page']))  { $page  = $params['page']; }
                        if (is_numeric($params['limit'])) { $limit = $params['limit']; }
                }
                else
                {
                        if (is_numeric(starfish::params('start'))) { $start = starfish::params('start'); }
                        if (is_numeric(starfish::params('page')))  { $page  = starfish::params('page'); }
                        if (is_numeric(starfish::params('limit'))) { $limit = starfish::params('limit'); }
                }

                if (is_numeric($limit) && is_numeric($start)) { $limits = ' limit '.$limit.' offset '.$start; }

                return $limits;
        }
        /*
        Build the JSON tree
        */
        function build_tree_json($data, $parent_name, $parent_id=null )
        {
                $output = array();

                $a = 0;
                $parents = array();

                // Parents
                if (is_array($data))
                {

                        foreach ($data as $key=>$value)
                        {
                                if ($value[$parent_name] == $parent_id)
                                {
                                        $output[$a] = $value;
                                        $output[$a]['id'] = $key;
                                        $output[$a]['leaf'] = true;
                                        $parents[$key] = $a;

                                        $a++;
                                }
                        }       
                }

                // Children
                if (is_array($data))
                {
                        foreach ($data as $key=>$value)
                        {
                                if ($value[$parent_name] != $parent_id && isset($parents[ $value[$parent_name] ]))
                                {
                                        $children = $this->build_tree_json($data, $parent_name, $value[$parent_name] );

                                        if (count($children) > 0)
                                        {
                                                $output[ $parents[ $value[$parent_name] ] ]['children'] = $children;
                                                $output[ $parents[ $value[$parent_name] ] ]['leaf'] = false;
                                                $output[ $parents[ $value[$parent_name] ] ]['expanded'] = true;
                                        }
                                }
                        }
                }
                return $output;
        }

}
?>