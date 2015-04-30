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
        obj('db')->query('delete from classes_methods');
        obj('db')->query('delete from classes_methods_parameters');
        
        // Update the database
		$this->update_db($list);
        
        //print_r( obj('db')->fetchAll( obj('db')->query("select * from tree") ) );
        //print_r( obj('db')->fetchAll( obj('db')->query("select * from classes") ) );
        //print_r( obj('db')->fetchAll( obj('db')->query("select * from classes_methods") ) );
        print_r( obj('db')->fetchAll( obj('db')->query("select * from classes_methods_parameters") ) );
        
		return true;
	}

	public function update_db($list, $parent=null)
	{
        //print_r($list);
        foreach ($list as $key=>$value)
        {
            if ($value['type'] === 'folder') 
            {
                obj('db')->query("insert into tree(title, type, path, parent) values('{title}', '{type}','{path}', '{parent}')", null, array(
					'title'=>$value['name'],
					'type'=>1,
					'path'=>$value['path'],
					'parent'=>$parent
				));
                $row = obj('db')->fetch( obj('db')->query("select _id from tree where title='{title}' and type='{type}' and path='{path}' and parent='{parent}'", null, array(
					'title'=>$value['name'],
					'type'=>1,
					'path'=>$value['path'],
					'parent'=>$parent
				)));
                
                $this->update_db($value['content'], $row['_id']);
            }
            else
            {
                obj('db')->query("insert into tree(title, type, path, parent) values('{title}', '{type}','{path}', '{parent}')", null, array(
					'title'=>$value['name'],
					'type'=>2,
					'path'=>$value['path'],
					'parent'=>$parent
                ));
                $row = obj('db')->fetch( obj('db')->query("select * from tree where title='{title}' and type='{type}' and path='{path}' and parent='{parent}'", null, array(
					'title'=>$value['name'],
					'type'=>2,
					'path'=>$value['path'],
					'parent'=>$parent
                )) );
                
                $file_id = $row['_id'];
				
                $info = $this->analyze_obj($value['name'], $value['path']);
                
				// Class
				$list = $info['class'];
                obj('db')->query("insert into classes(file_id, title, comments) values('{file_id}', '{title}', '{comments}')", null, array(
					'file_id'=>$file_id,
					'title'=>$list['name'],
					'comments'=>$list['comments']
                ));
                $row = obj('db')->fetch( obj('db')->query("select * from classes where file_id='{file_id}' and title='{title}' and comments='{comments}'", null, array(
					'file_id'=>$file_id,
					'title'=>$list['name'],
					'comments'=>$list['comments']
                )) );
                $class_id = $row['_id'];
				
				// Methods
				$list = $info['methods'];
                foreach ($list as $k1=>$v1) 
                {
                    obj('db')->query("insert into classes_methods(class_id, title, comments) values('{class_id}', '{title}', '{comments}')", null, array(
                        'class_id'=>$class_id,
                        'title'=>$v1['name'],
                        'comments'=>$v1['comments']
                    ));
                    
                    $row = obj('db')->fetch( obj('db')->query("select * from classes_methods where class_id='{class_id}' and title='{title}' and comments='{comments}'", null, array(
                        'class_id'=>$class_id,
                        'title'=>$v1['name'],
                        'comments'=>$v1['comments']
                    )) );
                    $method_id = $row['_id'];
                    
                    foreach ($v1['parameters'] as $k2=>$v2)
                    {
                        obj('db')->query("insert into classes_methods_parameters(method_id, title) values('{method_id}', '{title}')", null, array(
                            'method_id'=>$method_id,
                            'title'=>$v1['name']
                        ));
                    }
                }
				
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