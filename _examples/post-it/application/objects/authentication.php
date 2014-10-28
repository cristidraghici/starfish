<?php
if (!class_exists('starfish')) { die(); }

class authentication
{
        public $status = false;
        
        public function init()
        {
                if (session('authentication') == true)
                {
                        $time = session('time');
                        if ( time() - $time <= 3600 && session('id') == session_id() )
                        {
                                $this->status = true;
                        }
                }

                return true;
        }

        public function routes()
        {
                on('get', '/login', function(){
                        redirect('./notes', 302, $this->status == true );
                        
                        echo 'login';
                });
                on('get', '/logout', function(){
                        echo 'logout';
                });
                return true;
        }


        public function check()
        {
                if ($this->status == true)
                {
                        return true;
                }

                return false;
        }

        public function login($user, $pass, $encode, $encrypt)
        {
                if ($this->status == true) { return true; }
                $list = obj('users')->list;
                
                if (isset($list[$user]) && $list[$user] == md5($pass))
                {
                        session('authentication', true);
                        session('user', $user);
                        
                        session('encode', $encode);
                        session('encrypt', $encrypt);

                        session('time', time() );
                        session('id', session_id() );

                        return true;
                }
                
                return false;
        }

        public function logout()
        {
                if ($this->status == true)
                {
                        session('authentication', false);
                        session('user', null);
                        
                        session('encode', null);
                        session('encrypt', null);

                        session('time', null );
                        session('id', null );

                        return true;
                }

                return false;
        }
}
?>