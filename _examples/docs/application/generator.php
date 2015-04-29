<?php
if (!class_exists('starfish')) { die(); }

class generator
{
	public function generate()
	{
		$list = obj('files')->tree('../../', array(
			'../../.git*',
			'../../_examples/*',
			'../../_tests/*',
			'../../helpers/*',
			'../../libraries/*',
			'../../storage/*',
            '/index.html',
            '/README.md',
            '(.*)LICENSE'
		));
        
        // Clean the database
        obj('db')->query('delete from tree');
        obj('db')->query('delete from classes');
        
        // Update the database
		$this->update_db($list);
        
        //print_r( obj('db')->fetchAll( obj('db')->query("select * from tree") ) );
        
		return true;
	}

	public function update_db($list, $parent=null)
	{
        //print_r($list);
        foreach ($list as $key=>$value)
        {
            if ($value['type'] === 'folder') 
            {
                obj('db')->query("insert into tree(title, type, path, parent) values('".$value['name']."', '1','".$value['path']."', '".$parent."')");
                $row = obj('db')->fetch( obj('db')->query("select * from tree where title='".$value['name']."' and type='1' and path='".$value['path']."' and parent='".$parent."'") );
                $this->update_db($value['content'], $row['_id']);
            }
            else
            {
                obj('db')->query("insert into tree(title, type, path, parent) values('".$value['name']."', '2','".$value['path']."', '".$parent."')");
                $row = obj('db')->fetch( obj('db')->query("select * from tree where title='".$value['name']."' and type='2' and path='".$value['path']."' and parent='".$parent."'") );
                $file_id = $row['_id'];
				
                $info = $this->analyze_obj($value['name'], $value['path']);
                
				// Class
				$list = $info['class'];
				obj('db')->query("insert into classes(file_id, title, comments) values('".$file_id."', '".$list['name']."', '".$list['comments']."')");
                $row = obj('db')->fetch( obj('db')->query("select * from classes where file_id='".$file_id."' and title='".$list['name']."' and comments='".$list['comments']."'") );
                $id = $row['_id'];
				
				// Methods
				$list = $info['methods'];
				
				// Aliases
				$list = $info['aliases'];
            }
        }
	}

	public function is_obj($class, $file)
	{
		$class = substr($class, 0, -4);
		$content = r($file);
		if (preg_match('#class '.$class.'#i', $content, $match))
		{
			return true;
		}

		return false;
	}

	public function analyze_obj($class, $file)
	{
        if (substr($class, -4) === '.php')
        {
            $class = substr($class, 0, -4);
        }
		$content = r($file);
		
		//Instantiate the reflection object
		require_once($file);
		$reflector = new ReflectionClass( $class );

		$class = obj('parser')->getclass($reflector);
		$methods = obj('parser')->methods($reflector);
		$aliases = obj('parser')->aliases($content);
        
        return array(
            'class'=>$class,
            'methods'=>$methods,
            'aliases'=>$aliases
        );
	}
}

?>