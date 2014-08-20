<?php
if (!class_exists('starfish')) { die(); }

class postgresql
{
    public $errors;
    public $connected = false;
    
    function postgresql()
    {
		if (!extension_loaded('pgsql')) { starfish::error(400, "PHP required extension - pgsql - not loaded."); }
		
        starfish::$config['obj-alias']['pg'] = __CLASS__;
        starfish::$config['obj-alias']['pgsql'] = __CLASS__;
        starfish::$config['obj-alias']['postres'] = __CLASS__;
        
        return true;
    }
    
    
    // Init the object, make the connection
    public function init()
    {
        $constring = 'host='.starfish::$config['db']['host'].' port='.starfish::$config['db']['port'].' dbname='.starfish::$config['db']['name'].' user='.starfish::$config['db']['user'].'  password='.starfish::$config['db']['pass'];
        
        $link = @pg_connect ($constring); 
		if (!$link)
        { 
			starfish::error(400, "Could not connect to the database.");
		}
        else
        {
            $this->connected = true;
        }
        
        return true;
    }
    
    // Shows the given query
    public function eecho($query)
    {
        starfish::error(400, $query);
    }
    
    
    // Read all the results from the query
    public function a($query, $identif_string="")
    {
        $return = array();
        if ($this->connected == false) { $this->init(); }
         
            // Query the database
            $result = $this->q($query);
            
            if ($result)
            {
                while ($row = pg_fetch_row($result))
                {
                    $output = array();
                    
                    if (is_array($row))
                    {
                        $i = 0;
                        foreach($row as $element)
                        {
                            if (substr(pg_field_type($result, $i), 0, 1) == '_')
                            {
                                $element = explode(",", substr($element, 1, -1));
                                
                                $output[pg_field_name($result, $i)] = $element;
                            }
                            else
                            {   
                                if (pg_field_type($result, $i)=='numeric')
                                {
                                    $fieldname = pg_field_name($result, $i);
                                    
                                    switch ($fieldname)
                                    {
                                        case 'cnp':
                                            $output[pg_field_name($result, $i)] = $element;
                                            break;
                                        default:
                                            $output[pg_field_name($result, $i)] = floatval($element);
                                            break;
                                    }
                                    
                                }
                                else $output[pg_field_name($result, $i)] = $element;
                            }
                            
                            $i++;
                        }
                    }
                    
                    if (strlen($identif_string) > 0 && strlen($output[$identif_string]) > 0)
                    {
                        $return[$output[$identif_string]] = $output;
                    }
                    else
                    {
                        $return[] = $output;
                    }
                }
            }
            
        return $return;
    }
    
    // Return the first result of the query
    public function aq($query)
    {
        $result = $this->a($query);
        return $result[0];
    }
    
    
    // Execute the query
    public function q($query)
    {
        if (strlen($query) > 0)
        {
            if ($this->connected == false) { $this->init(); }
            
            $result = pg_query($query);
            return $result;
        }
        
        return false;
    }
    // Returns the number count(*)-ed results
    public function nr($query)
    {        
        if ($this->connected == false) { $this->init(); }
        
        $counted = 0;
        
        $resultCount = pg_query($query);
        if ($resultCount)
        {
            while ($row = pg_fetch_row($resultCount)) { 
                $counted = $row[0];
            }
            pg_free_result($resultCount);
        }
        
        return $counted;
    }
    // Inserts the line and returnes the ID of the last entry
    public function ins($q)
    {
        if ($this->connected == false) { $this->init(); }
        
        $result = pg_query($q);
        $row = pg_fetch_row($result);
        if (!$result)
        {
            return false;
        }
        else
        {
            return $row[0];
        }
        
        return false;
    }
    // Deletes a certain row from the database
    public static function del($q)
    {
        if ($this->connected == false) { $this->init(); }
        
        $result = pg_query($q);
        if (!$result)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    
    
    
    
    public function sql_from_function($pg_function, $values, $overwrite=array())
    {
        $query = "select ";
        
        // Function name
        preg_match('#CREATE OR REPLACE FUNCTION ([^\(]+)\(#i', $pg_function, $match);
        $query .= $match[1];
        
        // Function values
        $query .= $this->sql_values_from_function($pg_function, $values, $overwrite);
        
        return $query;
    }
    
    public function sql_values_from_function($pg_function, $values, $overwrite=array())
    {
        // stabileste tipurile de date din functie
        $types = $this->sql_from_function_types($pg_function);
        
        // suprascrie valorile fortate in lista de valori trimisa
        foreach ($overwrite as $key=>$value)
        {
            $values[$key] = $value;
        }
        
        // creaza string-ul
        $string = "(";
        foreach ($types as $key=>$value)
        {
            if (!isset($values[$key]) || $values[$key] == 'null' || $values[$key] == '')
            {
                $string .= 'null,';
            }
            else
            {
                switch ($value)
                {
                    case 'integer[]':
                    case 'bigint[]':
                    case 'numeric[]':
                        //$string .= "null,";
                        if (is_array($values[$key]) && count($values[$key]) > 0)
                        {
                            $newvalue = "array[";
                            $count = 0;
                            foreach ($values[$key] as $key2=>$value2)
                            {
                                if (strlen($value2) > 0)
                                {
                                    $newvalue .= $value2.",";
                                    $count++;
                                }
                            }
                            if (strlen($newvalue) > 6) { $newvalue = substr($newvalue, 0, -1); }
                            $newvalue .= "]";
                            
                            if ($count == 0)
                            {
                                $newvalue = "null";
                            }
                        }
                        else
                        {
                            $newvalue = "null";
                        }
                        
                        $string .= $newvalue.",";
                        break;
                    case 'varchar[]':
                        //$string .= "null,";
                        if (is_array($values[$key]) && count($values[$key]) > 0)
                        {
                            $newvalue = "array[";
                            $count = 0;
                            foreach ($values[$key] as $key2=>$value2)
                            {
                                if (strlen($value2) > 0)
                                {
                                    $newvalue .= "'".$value2."',";
                                    $count++;
                                }
                            }
                            if (strlen($newvalue) > 6) { $newvalue = substr($newvalue, 0, -1); }
                            $newvalue .= "]";
                            
                            if ($count == 0)
                            {
                                $newvalue = "null";
                            }
                        }
                        else
                        {
                            $newvalue = "null";
                        }
                        
                        $string .= $newvalue.",";
                        break;
                    case 'varchar':
                    case 'date':
                    case 'timestamp':
                    case 'char':
					case 'character':
                        $string .= "'".$values[$key]."',";
                        break;
                    case 'numeric':
                    case 'integer':
                    case 'bigint':
                    case 'float':
                        $string .= floatval($values[$key]).",";
                        break;
                    case 'bytea':
                        $string .= "'".$values[$key]."',";
                        break;
                }
            }
        }
        if (strlen($string) > 1) { $string = substr($string, 0, -1); } $string .= ")";
        
        return $string;
    }
    
    public function sql_from_function_types($string)
    {
        $array = array();
        // clean comments from function
        $string = preg_replace('/(\s\s+|\t|\n)/', "\n", $string);
        $string = preg_replace('/--([^\n]+)/'."\n", '', $string);
        $string = preg_replace('/(\s\s+|\t|\n)/', " ", $string);
        
        // create the array with values
        preg_match('/\(([^\)]+)\)/', $string, $match);
        $string = preg_replace('/(\s\s+|\t|\n)/', ' ', $match[1]);
        
        $work = explode(",", $string);
        foreach ($work as $key=>$value)
        {
            if (substr(trim($value), 0, 6) == 'param$' || substr(trim($value), 0, 6) == 'array$')
            {
                $row = substr(trim($value), 6);
                $parts = explode(" ", $row);
                $array[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        return $array;
    }
}


?>