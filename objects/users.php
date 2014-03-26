<?php

class users
{
    function users()
    {		
		switch(starfish::$exec['method'])
		{
			case 'get':		// Read (list/item)
				$page  = starfish::$exec['pathParts'][1];
				$start = starfish::$exec['pathParts'][1];
				$limit = starfish::$exec['pathParts'][1];
				
				if (
					( !is_numeric($start) || !is_numeric($limit) ) &&
					is_numeric($page)
				)
				{
					$this->getFn($page);
				}
				else
				{
					$this->listFn($page, $start, $limit);
				}
				
				break;
			case 'put':		// Update
				$this->putFn();
				break;
			case 'post': 	// Create
				$this->postFn();
				break;
			case 'delete':	// Delete
				$this->deleteFn();
				break;
		}
		
		function getFn($id)
		{
			return true;
		}
		function listFn($page, $start, $limit)
		{
			return true;
		}
		function putFn()
		{
			return true;
		}
		function postFn()
		{
			return true;
		}
		function deleteFn()
		{
			return true;
		}
    }
}


?>