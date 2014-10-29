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
                                session('time', time());
                        }
                        else
                        {
                                starfish::obj('errors')->message('authentication_error', 'The authentication has expired.');
                        }
                }

                return true;
        }

        public function routes()
        {
                on('get', '/login', function(){
                        redirect('./notes', 302, obj('authentication')->check() );
                        
                        echo view('header');
                        echo view('login');
                        echo view('footer');
                });
                
                on('post', '/login', function(){
                        redirect('./notes', 302, obj('authentication')->login(post('user'), post('pass'), post('encode'), post('encrypt')) );
                        
                        starfish::obj('errors')->message('authentication_error', 'The authentication information is not correct.');
                        redirect('./login');
                });
                
                on('get', '/logout', function(){
                        obj('authentication')->logout();
                        
                        starfish::obj('errors')->message('authentication_error', 'You have been logged out from your account.');
                        redirect('./login');
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
                $users = obj('users')->list;
                $list = array();
                $ids = array();
                
                foreach ($users as $key=>$value)
                {
                        $list[$value['name']] = $value['pass'];
                        $ids[$value['name']] = $value['_id'];
                }
                
                if (isset($list[$user]) && $list[$user] == md5($pass))
                {
                        session('authentication', true);
                        session('user', $user);
                        session('user_id', $ids[$user]);
                        
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
                        session('user_id', null);
                        
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