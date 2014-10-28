<?php
if (!class_exists('starfish')) { die(); }

class categories
{
	public function init()
	{
		return true;
	}
	
	public function routes()
	{
		return true;
	}
        
        
        public function all()
        {
                $list = array();
                
                $resource = starfish::obj('database')->query("select * from categories where owner='".session('user')."'");
                while ($row = starfish::obj('database')->fetch($resource))
                {
                        $list[] = $row;
                }
                
                $this->list = $list;
                
                return $list;
        }
        
        public function add($name)
        {
                starfish::obj('database')->query("insert into categories(name, owner) values('".$name."', '".session('user')."')");
                $this->all();
                return true;
        }
        
        public function del($name)
        {
                starfish::obj('database')->query("delete from categories where name='".$name."' and owner='".session('user')."'");
                $this->all();
                return true;
        }
}
?>