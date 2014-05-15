<?php
if (!class_exists('starfish')) { die(); }

class tpl
{
	public $path = '';
	public $parts = array();
	
	private $init = false;
	private $dump = false;
	
	public function tpl()
	{
		if ($this->init == false)
		{
			$this->init();
		}
	}
	
	public function init($path=null)
	{
		if ($this->init == false)
		{
			$this->init = true;
			
			if ($path != null)
			{
				$this->path = $path;
			}
			else
			{
				$this->path = starfish::$config['tpl'];
			}
			
			$this->path = trim($this->path, '/') . '/';
			
			$this->set('site_url', 			starfish::$config['site_url']);
			$this->set('/', 				starfish::$config['site_url']);
			
			$this->set('site_title', 		starfish::$config['site_title']);
			$this->set('site_description', 	starfish::$config['site_description']);
		}
		
		return true;
	}
	
	public function set($variable, $value)
	{
		return starfish::regArr('TPLtemplateSystem', $variable, $value);
	}
	public function get($variable)
	{
		return starfish::regArr('TPLtemplateSystem', $variable);
	}
	
	public function view($file, $variables=null)
	{
		$html = '';
		
        if (is_array($variables))
        {
            extract($variables);
        }
		
		$tplFile = $this->path . $file . '.tpl.php';
		
		if (file_exists($tplFile))
        {
			// Read the template
			$tplContent = starfish::obj('files')->r($tplFile);
			
			// Remove safeguards
			$tplContent = str_replace(array(
				'<'.'?php /* Starfish Framework Template protection */ die(); ?'.'>' . PHP_EOL,
				'<'.'?php /* Starfish Framework Template protection */ die(); ?'.'>',
			), '', $tplContent);
			
			// Include predefined variables
			$tplContent = $this->variables($tplContent);
			
			// Remove spaces for SWITCH to work properly
			$tplContent = preg_replace('#\?>([\s]+)<\?php#is', '?><?php', $tplContent);
            
            ob_start();
            eval(' ?>'.$tplContent.'<?php ');
            $html = ob_get_clean();
		}
		
		return $html;
	}
	public function dump()
	{
		if ($this->dump == false)
		{
			$this->dump = true;
			ob_start();
		}
		else
		{
			$html = ob_get_clean();
			ob_start();
			
			// Include predefined variables
			$html = $this->variables($html);
			
			// Remove empty var spaces
			$html = preg_replace('#{([A-Za-z0-9_^}\w]+)}#i', '', $html);
			
			// Move meta to head
			$meta = '';
			preg_match_all('/<meta([^>]+)>/i', $html, $matches);
			foreach ($matches[0] as $key=>$value)
			{
				$html = str_replace($value, '', $html);
				$meta .= PHP_EOL.$value;
			}
			$html = str_ireplace('<head>', "<head>".PHP_EOL.$meta, $html);
			
			echo $html;
		}
	}
	private function variables($html)
	{
		$variables = starfish::regArr('TPLtemplateSystem');
		foreach ($variables as $key=>$value)
		{
			$html = str_replace('{'.$key.'}', $value, $html);
		}
		return $html;
	}
}
?>