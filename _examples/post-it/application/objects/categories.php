<?php
if (!class_exists('starfish')) { die(); }

class categories
{
	public $list = array();

	public function init()
	{
		$this->all();
		return true;
	}

	public function routes()
	{
		on('get', '/categories', function(){
			redirect('./login', 302, !obj('authentication')->check() );

			echo view('header');
			echo view('categories', array(
				'list' => obj('categories')->list
			));
			echo view('footer');
		});


		on('post', '/categories/add', function(){
			if (strlen(post('name')) > 0 ) 
			{
				obj('categories')->add(post('name'));
			}

			redirect('./categories/');
		});

		on('get', '/categories/delete/:alpha', function($user){
			if (strlen($user) > 0) 
			{
				obj('categories')->del($user);
			}

			redirect('./categories/');
		});

		return true;
	}


	public function all()
	{
		$list = array();

		$resource = starfish::obj('database')->query("select * from categories where owner_id='".session('user_id')."'");
		while ($row = starfish::obj('database')->fetch($resource))
		{
			$list[] = $row;
		}

		$this->list = $list;

		return $list;
	}

	public function add($name)
	{
		$name = starfish::obj('database')->sanitize($name);

		starfish::obj('database')->query("insert into categories(name, owner_id) values('".$name."', '".session('user_id')."')");
		$this->all();
		return true;
	}

	public function del($id)
	{
		starfish::obj('database')->query("delete from categories where _id='".$id."' and owner_id='".session('user_id')."'");
		$this->all();
		return true;
	}
}
?>