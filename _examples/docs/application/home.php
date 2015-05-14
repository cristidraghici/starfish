<?php
if (!class_exists('starfish')) { die(); }

class home
{
    private $md = null;

    public function init()
    {
        on('get', '/', array($this, 'startPage') );
        on('get', '/class/:num', array($this, 'startPage') );

        $this->md = new Markdown();
    }

    public function commentSyntaxHighlight($text) 
    {		
        $text = preg_replace("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", "<a href=$0 target=_blank>$0</a> ", $text);
        $text = preg_replace("/(@)[a-zA-Z]*(\/\S*)?/", "<span class='class-row-title'>$0</span>", $text);

        $text = $this->md->transform(nl2br($text));

        return $text;
    }

    public function startPage($id=null)
    {
        echo starfish::obj('tpl')->view('header');

        // Select the list of files and append the methods
        $list = obj('db')->fetchAll( obj('db')->query("select * from tree order by type asc") );

        // Select clases
        $classes = obj('db')->fetchAll( obj('db')->query("select * from classes") );
        $classesIds = array();
        foreach ($classes as $key=>$value)
        {
            $classesIds[$value['_id']] = $value['file_id'];
        }
        
        // Select the method names
        $methods = obj('db')->fetchAll( obj('db')->query("select * from classes_methods") );
        
        foreach ($methods as $key=>$value) {
            $list[] = array(
                '_id' => '#' . $value['title'],
                'parent' => $classesIds[$value['class_id']],
                'title' => $value['title'],
                'type' => 3
            );
        }


        // Build the tree
        $tree = obj('common')->treeze($list, '_id', 'parent');
        usort($tree, function($a, $b) {
            if ($a == $b)
            {
                //echo "a is same priority as b, keeping the same\n";
                return 0;
            }
            else if ($a > $b && ($a['type'] == 2 || ($a['type'] != 2 && $b['type'] != 2)))
            {
                //echo "a is higher priority than b, moving b down array\n";
                return -1;
            }
            else {
                //echo "b is higher priority than a, moving b up array\n";               
                return 1;
            }
        });

        echo starfish::obj('tpl')->view('home', array(
            'tree'		=> view('tree', array('list'=>$tree)),
            'content'	=> $this->classBody($id)
        ));
        echo starfish::obj('tpl')->view('footer');     
    }

    public function classBody($id=null) 
    {
        if ($id == null) 
        {
            $row = obj('db')->fetch( obj('db')->query("select * from tree where title='starfish.php'") );
            $id = $row['_id'];
        }

        $row = obj('db')->fetch( obj('db')->query("select * from classes where file_id='".$id."'") );
        $class_id = $row['_id'];

        $methods = '';
        $q = obj('db')->query("select * from classes_methods where class_id='".$class_id."'");
        while ($r = obj('db')->fetch($q)) 
        {
            $methods .= $this->methodBody($r);
        }

        return view('class', array(
            'title'=>$row['title'],
            'comments'=>$this->commentSyntaxHighlight($row['comments']),

            'methods'=>$methods
        ));
    }

    public function methodBody($row)
    {
        $body = highlight_string( '<'.'?php '. PHP_EOL . htmlspecialchars_decode($row['body'], ENT_QUOTES) . PHP_EOL . ' ?'.'>', true);
        $title = $row['title'];
        /* // Not recommended for the textdb version
		$title .= ' (';
		$r2 = obj('db')->query("select * from classes_methods_parameters where _id='".$row['_id']."'")->fetchAll();
		foreach ($r2 as $key=>$value)
		{
			$title .= $value['title'];
		}
		$title .= ')';
		*/

        return view('method', array(
            'title'=>$title,
            'parameters'=>$row['parameters'],
            'comments'=>$this->commentSyntaxHighlight($row['comments']),
            'body'=>$body
        ));
    }
}

?>