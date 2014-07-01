<?php
if (!class_exists('starfish')) { die(); }

class jsonrpc
{
    public function quickAsk($url, $method, $data=array(), $id=null)
    {
        if ($id == null) {  $id = mt_rand(); }
        
        $call = array(
            'jsonrpc'   => '2.0',
            'method'    => $method,
            'id'        => $id
        );
        
        if (count($data) > 0)
        {
            $call['params'] = $data;
        }
        
        $call = json_encode($call);
        
        $result = starfish::obj('curl')->get($url, $call, 'post');
        
        $decoded = @json_decode($result, true);
        if ($decoded != null)
        {
            $result = $decoded;
        }
        
        
        return $result;
    }
}

?>