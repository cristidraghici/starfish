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
                on('post', '/users/add', function(){
                        if (strlen(post('user')) > 0 && strlen(post('pass')) > 0) 
                        {
                                $this->add(post('user'), post('pass'));
                        }
                        
                        redirect('./users/');
                });
                
                on('get', '/users/delete/:alpha', function($user){
                        if (strlen($user) > 0) 
                        {
                                $this->del($user);
                        }
                        
                        redirect('./users/');
                });
                
                
                on('get', '/users', function(){
                        echo 'Userlist';
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
                
                return $list;
        }
        
        public function add($user, $pass)
        {
                starfish::obj('database')->query("insert into users(name, pass) values('".$user."', '".md5($pass)."')");
                $this->all();
                return true;
        }
        
        public function del($user)
        {
                starfish::obj('database')->query("delete from users where name='".$user."'");
                $this->all();
                return true;
        }
}
?>