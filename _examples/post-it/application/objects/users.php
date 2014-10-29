<?php
if (!class_exists('starfish')) { die(); }

class users
{
        public $list = array();
        
	public function init()
	{
                $this->all();
		return true;
	}
	
	public function routes()
	{
                on('get', '/users', function(){
                        redirect('./login', 302, !obj('authentication')->check() );
                        
                        echo view('header');
                        echo view('users', array(
                                'list' => obj('users')->list
                        ));
                        echo view('footer');
                });
                
                on('post', '/users/add', function(){
                        if (strlen(post('user')) > 0 && strlen(post('pass')) > 0) 
                        {
                                obj('users')->add(post('user'), post('pass'));
                        }
                        
                        redirect('./users/');
                });
                
                on('get', '/users/delete/:alpha', function($user){
                        if (strlen($user) > 0) 
                        {
                                obj('users')->del($user);
                        }
                        
                        redirect('./users/');
                });
                
		return true;
	}
        
        public function all()
        {
                $list = array();
                
                $resource = starfish::obj('database')->query("select * from users");
                while ($row = starfish::obj('database')->fetch($resource))
                {
                        $list[] = $row;
                }
                
                $this->list = $list;
                
                // Ensure the is at least one user
                if (count($list) == 0) { $this->add('admin', 'admin'); $list = $this->all(); }
                
                return $list;
        }
        
        public function add($user, $pass)
        {
                starfish::obj('database')->query("insert into users(name, pass) values('".$user."', '".md5($pass)."')");
                $this->all();
                return true;
        }
        
        public function del($id)
        {
                starfish::obj('database')->query("delete from users where _id='".$id."'");
                $this->all();
                return true;
        }
}
?>