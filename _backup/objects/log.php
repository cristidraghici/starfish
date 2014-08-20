<?php
if (!class_exists('starfish')) { die(); }

class log
{
    public function saveLog($file, $data)
    {
        $data = @json_encode($data);
        starfish::obj('files')->w($file, $data . PHP_EOL, 'a');
        return true;
    }
    
    public function saveRequest($file='_requests.log')
    {
		if ($file == '_requests.log')
		{
			$file = starfish::$config['root'].'storage/'.$file;
		}
	
        $request = array(
            'timestamp' => date("Y-m-d H:i:s"),
            'starfish'   => starfish::$exec
        );
        
		$this->saveLog($file, $request);
        return true;
    }
}

?>