<?php
if (!class_exists('starfish')) { die(); }

class mysql
{
	private $connection = false;
	private $lastQuery  = '';
    
	private $db_host = 'localhost';
	private $db_user = 'root';
	private $db_pass = '';
	private $db_name = '';
	
	function init()
	{
		return $this->connect();
	}
	
	function connect($data=array())
	{
        if (count($data) == 0 && isset (starfish::$config['db']))
        {
            $data = starfish::$config['db'];
        }
        if (isset($data['host'])) { $this->db_host = $data['host']; }
        if (isset($data['user'])) { $this->db_user = $data['user']; }
        if (isset($data['pass'])) { $this->db_pass = $data['pass']; }
        if (isset($data['name'])) { $this->db_name = $data['name']; }
        
        $link = @mysql_connect($this->db_host, $this->db_user, $this->db_pass); 
		if (!$link)
        { 
            starfish::error(400, $this->sqlerror() );
		}
        else
        {
            $seldb = @mysql_select_db($this->db_name, $link);
            if (!$seldb)
            {
                starfish::error(400, $this->sqlerror() );
            }
        }
		@mysql_set_charset('utf8', $link);
		$this->connection = $link;
		
		return true;
	}
	
	function q($query, $info=false)
	{
		if ($this->connection == false) { $this->connect(); }
		$this->lastQuery = $query;
		
        $q = @mysql_query($query, $this->connection);
		if (!$q) { starfish::error(400, $this->sqlerror() ); }
		
		if ($info == true)
		{
			return array(
				'resource' 	=> $q,
				'info'		=> $this->get_mysql_info($this->connection),
				'id'		=> @mysql_insert_id($this->connection)
			);
		}
		else
		{
			return $q;
		}
	}
	function insprc($query)
    {
		$id = null;
		
		$q = $this->q($query, false);
        $result = @mysql_result($q, 0);
		if (@is_numeric($result))
		{
			$id = $result;
		}
	
        return $id;
    }
	
	function get($query)
	{
		$data = array();
		
        $q = $this->q($query, false);
		while ($row = @mysql_fetch_array($q, MYSQL_ASSOC))
		{
			$data[] = $row;
		}
		
		return $data;
	}
    
	function fetch($q)
	{
		return @mysql_fetch_array($q);
	}
	
	
	function eecho($query)
	{
		starfish::error(400, $query);
	}
	
	function get_mysql_info($linkid = null){
		$linkid ? $strInfo = @mysql_info($linkid) : $strInfo = @mysql_info();
	   
		$return = array();
		preg_match('#Records: ([0-9]*)#', $strInfo, $records);
		preg_match('#Duplicates: ([0-9]*)#', $strInfo, $dupes);
		preg_match('#Warnings: ([0-9]*)#', $strInfo, $warnings);
		preg_match('#Deleted: ([0-9]*)#', $strInfo, $deleted);
		preg_match('#Skipped: ([0-9]*)#', $strInfo, $skipped);
		preg_match('#Rows matched: ([0-9]*)#', $strInfo, $rows_matched);
		preg_match('#Changed: ([0-9]*)#', $strInfo, $changed);
		
		$return['records'] = $records[1];
		$return['duplicates'] = $dupes[1];
		$return['warnings'] = $warnings[1];
		$return['deleted'] = $deleted[1];
		$return['skipped'] = $skipped[1];
		$return['rows_matched'] = $rows_matched[1];
		$return['changed'] = $changed[1];
	   
		return $return;
	}
    
    function sqlerror()
    {
        if (starfish::$config['debug'] == true)
        {
			if (strlen( $this->lastQuery) > 0)
			{
				starfish::error(400, 'MySQL Error: ' . @mysql_error() . ' - ' . @mysql_errno() . ' - ' . $this->lastQuery );
			}
			else
			{
				starfish::error(400, 'MySQL Error: ' . @mysql_error() . ' - ' . @mysql_errno() );
			}
        }
        else
        {
            starfish::error(400, 'Bad request!');
        }
    }
}


?>