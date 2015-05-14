<?php
if (!class_exists('starfish')) { die(); }

/*
 * @todo Add support for files other than php
 */
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
		//print_r( obj('db')->fetchAll( obj('db')->query("select * from classes_methods_parameters") ) );

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

				$this->update_db($value['content'], obj('db')->insert_id() );
			}
			else
			{
				obj('db')->query("insert into tree(title, type, path, parent) values('{title}', '{type}','{path}', '{parent}')", null, array(
					'title'=>$value['name'],
					'type'=>2,
					'path'=>$value['path'],
					'parent'=>$parent
				));
				$file_id = obj('db')->insert_id();

				$info = $this->analyze_obj($value['name'], $value['path']);

				// Class
				$list = $info['class'];
				obj('db')->query("insert into classes(file_id, title, comments) values('{file_id}', '{title}', '{comments}')", null, array(
					'file_id'=>$file_id,
					'title'=>$list['name'],
					'comments'=>$list['comments']
				));
				$class_id = obj('db')->insert_id();

				// Methods
				$list = $info['methods'];
				foreach ($list as $k1=>$v1) 
				{
					$parameters = '';
					foreach ($v1['parameters'] as $k2=>$v2)
					{
						$parameters .= '$' . $v2->name . ', ';
					}
					if (strlen($parameters) > 1) {
						$parameters = substr($parameters, 0, -2);
					}
					$parameters .= '';
					
					obj('db')->query("insert into classes_methods(class_id, title, parameters, comments, body) values('{class_id}', '{title}', '{parameters}', '{comments}', '{body}')", null, array(
						'class_id'=>$class_id,
						'title'=>$v1['name'],
						'parameters'=>$parameters,
						'comments'=>$v1['comments'],
						'body'=>$v1['body']
					));
					$method_id = obj('db')->insert_id();

					/* // Not recommended for nonMYSQL database
					foreach ($v1['parameters'] as $k2=>$v2)
					{
						obj('db')->query("insert into classes_methods_parameters(method_id, title) values('{method_id}', '{title}')", null, array(
							'method_id'=>$method_id,
							'title'=>$v2['name']
						));
					}
					*/
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
		foreach ($methods as $key=>$value) 
		{
			$methods[$key]['body'] = $this->get_function_body($content, $class['name'], $value['name']);
		}
		
		$aliases = obj('parser')->aliases($content);

		return array(
			'class'=>$class,
			'methods'=>$methods,
			'aliases'=>$aliases
		);
	}
	
	/**
	 * @see http://stackoverflow.com/questions/7026690/reconstruct-get-code-of-php-function
	 */
	public function get_function_body($content, $class, $name) 
	{
		$reflectionMethod = new ReflectionMethod($class, $name);
		
		$start_line = $reflectionMethod->getStartLine() - 1; // it's actually - 1, otherwise you wont get the function() block
		$end_line = $reflectionMethod->getEndLine();
		$length = $end_line - $start_line;
		
		$body = implode("\n", array_slice(explode("\n",$content), $start_line, $length));
		
		return $body;
	}
}

?>