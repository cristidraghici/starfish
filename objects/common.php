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
	 * - see http://stackoverflow.com/questions/4196157/create-array-tree-from-array-list
	 * @param  array  $source                    Source of data
	 * @param  string $columnIdentifier          Id of the column
	 * @param  string $columnParent              Name of the column where the id of the father is saved
	 * @param  string [$childrenName='children'] Name of the column where the children will move
	 * @param  string [$parentValue=null]        Value for which to search for results
	 * @return array  Generated tree file
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

    /**
     * Transform an array into an ini string
     * @param  array  $a       The source array
     * @param  array  [$parent = array()] The parent array
     * @return string The output string
     */
    function arr2ini($a, $parent = array())
    {
        $out = '';
        foreach ($a as $k => $v)
        {
            if (is_array($v))
            {
                //subsection case
                //merge all the sections into one array...
                $sec = array_merge((array) $parent, (array) $k);
                //add section information to the output
                $out .= '[' . join('.', $sec) . ']' . PHP_EOL;
                //recursively traverse deeper
                $out .= arr2ini($v, $sec);
            }
            else
            {
                //plain key->value case
                $out .= "$k=$v" . PHP_EOL;
            }
        }
        return $out;
    }
}
?>