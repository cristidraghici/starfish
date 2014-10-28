<?php
if (!class_exists('starfish')) { die(); }

class notes
{
	public function init()
	{
		return true;
	}
	
	public function routes()
	{
                on('get', '/notes', function(){
                        redirect('./login', 302, !obj('authentication')->check() );
                        
                        echo 'notes';
                });
		return true;
	}
        
        public function all()
        {
                $list = array();
                
                $resource = starfish::obj('database')->query("select * from notes where owner='".session('user')."'");
                while ($row = starfish::obj('database')->fetch($resource))
                {
                        $list[] = $row;
                }
                
                $this->list = $list;
                
                return $list;
        }
        
        public function add($name)
        {
                starfish::obj('database')->query("insert into notes(name, owner) values('".$name."', '".session('user')."')");
                $this->all();
                return true;
        }
        
        public function del($name)
        {
                starfish::obj('database')->query("delete from notes where name='".$name."' and owner='".session('user')."'");
                $this->all();
                return true;
        }
}
?>