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
                
                
                on('get', '/notes/edit/:num', function($id){
                        redirect('./login', 302, !obj('authentication')->check() );
                        
                        $item = array();
                        foreach ($this->list as $key=>$value)
                        {
                                if ($value['_id'] == $id) {
                                        $item = $this->list[$key];
                                }
                        }
                        
                        echo view('header');
                        echo view('notes', array(
                                'categories'=>obj('categories')->list,
                                'notes'=>obj('notes')->list,
                                'item'=>$item
                        ));
                        echo view('footer');
                });

                on('post', '/notes/add', function(){
                        if (strlen(post('content')) > 0 && strlen(post('category_id')) > 0) 
                        {
                                obj('notes')->add(post('content'), post('category_id'), post('_id'));
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

                on('get', '/notes/export', function(){
                        header("Content-type: text/csv");
                        header("Content-Disposition: attachment; filename=export.csv");
                        header("Pragma: no-cache");
                        header("Expires: 0");
                        
                        // Send to the output buffer
                        $output = @fopen("php://output", 'w');
                        foreach(obj('notes')->export() as $val) 
                        {
                                @fputcsv($output, $val);
                        }
                        @fclose($output);
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

        public function add($content, $category_id, $id=null)
        {
                if ($id == null)
                {
                        starfish::obj('database')->query("insert into notes(content, category_id, owner_id) values('".$content."', '".$category_id."', '".session('user_id')."')");
                }
                else
                {
                        starfish::obj('database')->query("update notes set content='".$content."', category_id='".$category_id."' where _id='".$id."'");
                }
                $this->all();
                
                return true;
        }

        public function del($id)
        {
                starfish::obj('database')->query("delete from notes where _id='".$id."' and owner_id='".session('user_id')."'");
                $this->all();

                return true;
        }

        public function export()
        {                
                $notes = $this->list;

                $categories = array();
                $categoriesList = obj('categories')->list;
                foreach ($categoriesList as $key=>$value)
                {
                        $categories[$value['_id']] = $value['name'];
                }

                foreach ($notes as $key=>$value)
                {
                        $notes[$key]['category'] = $categories[$value['category_id']];
                        unset($notes[$key]['category_id']);
                        unset($notes[$key]['_id']);
                        unset($notes[$key]['owner_id']);
                }

                return $notes;
        }
}
?>