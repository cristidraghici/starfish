<?php
if (!class_exists('starfish')) { die(); }

class notes
{
        public $list = array();

        public function init()
        {
                $this->all();
                return true;
        }

        public function routes()
        {
                on('get', '/notes', function(){
                        redirect('./login', 302, !obj('authentication')->check() );

                        echo view('header');
                        echo view('notes', array(
                                'categories'=>obj('categories')->list,
                                'notes'=>obj('notes')->list
                        ));
                        echo view('footer');
                });


                on('post', '/notes/add', function(){
                        if (strlen(post('content')) > 0 && strlen(post('category_id')) > 0) 
                        {
                                obj('notes')->add(post('content'), post('category_id'));
                        }
                        
                        redirect('./notes/');
                });
                
                on('get', '/notes/delete/:alpha', function($id){
                        if (strlen($id) > 0) 
                        {
                                obj('notes')->del($id);
                        }
                        
                        redirect('./notes/');
                });

                return true;
        }

        public function all()
        {
                $list = array();

                $resource = starfish::obj('database')->query("select * from notes where owner_id='".session('user_id')."'");
                while ($row = starfish::obj('database')->fetch($resource))
                {
                        $list[] = $row;
                }

                $this->list = $list;
                
                return $list;
        }

        public function add($content, $category_id)
        {
                starfish::obj('database')->query("insert into notes(content, category_id, owner_id) values('".$content."', '".$category_id."', '".session('user_id')."')");
                $this->all();
                
                return true;
        }

        public function del($id)
        {
                starfish::obj('database')->query("delete from notes where _id='".$id."' and owner_id='".session('user_id')."'");
                $this->all();
                
                return true;
        }
}
?>