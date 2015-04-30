<?php
if (!class_exists('starfish')) { die(); }

/**
 * Common functions that are not part of a complex enough structure to build a module
 *
 * @package starfish
 * @subpackage starfish.objects.common
 */
class common
{	
	/**
	 * Create a tree from a given array
	 * 
	 * @param  array  $source                    Source of data
	 * @param  string $columnIdentifier          Id of the column
	 * @param  string $columnParent              Name of the column where the id of the father is saved
	 * @param  string [$childrenName='children'] Name of the column where the children will move
	 * @param  string [$parentValue='null']      Value for which to search for results
	 *                                                                         
	 * @return array  Generated tree file
	 * 
	 * @see http://stackoverflow.com/questions/4196157/create-array-tree-from-array-list
	 */
	function treeze ($source, $columnIdentifier, $columnParent, $childrenName='children', $parentValue=null)
	{
		$list = array();
		
		foreach ($source as $key=>$item)
		{
			if ($item[$columnParent] == $parentValue) 
			{
				$children = $this->treeze($source, $columnIdentifier, $columnParent, $childrenName, $item[$columnIdentifier]);
				if (count($children) > 0)
				{
					$item[$childrenName] = $children;
				}
				
				$list[] = $item;
			}
		}
		
		return $list;
	}
	
}
?>