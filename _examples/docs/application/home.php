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
		$text = preg_replace("/(@)[a-zA-Z]*(\/\S*)?/", "<i>$0</i>", $text);
		
		$text = $this->md->transform(nl2br($text));
		
		return $text;
	}
	
	public function startPage($id=null)
	{
		echo starfish::obj('tpl')->view('header');
		
		// Build the tree
		$list = obj('db')->fetchAll( obj('db')->query("select * from tree order by type asc") );
		$tree = obj('common')->treeze($list, '_id', 'parent');
		asort($tree);
		
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
		
		return view('method', array(
			'title'=>$row['title'],
			'comments'=>$this->commentSyntaxHighlight($row['comments']),
			'body'=>$body
		));
	}
}

?>