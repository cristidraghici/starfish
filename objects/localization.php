<?php
if (!class_exists('starfish')) { die(); }

/**
 * Localization object
 *
 * @package starfish
 * @subpackage starfish.objects.localization
 */
class localization
{	
    // Path to the translated files
    public $path = './';

    // Translated words
    public $words = array();

    // Loaded languages
    public $translations = array();

    // Current language
    public $language = '.';

    // Default language - The default language will be translated into itself
    public $default = 'en';

    /**
	 * Init the object
	 */
    public function init()
    {
        // Set the path to the translation files
        $this->path = starfish::config('_starfish', 'storage') . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;

        // Load the configurated translations
        $list = (config('_starfish', 'languages') != null) ? config('_starfish', 'languages') : array();
        if (count($list) > 0)
        {
            $count = 0;
            foreach ($list as $name=>$config)
            {
                // The default language - if not specified otherwise, the first language is the default one
                if ((isset($config['default']) && $config['default'] === true) || $count === 0)
                {
                    $this->language = $name;
                }

                // Load the file
                $this->load($name, $config['file']);

                $count++;
            }
        }

        // Check for previously set language in cookies
        if (cookie('language') != null && isset($this->translations[cookie('language')]))
        {
            $this->language = cookie('language');
        }
    }

    /**
     * Load a translations file
     * @param string $name Name of the language the translation belongs to
     * @param string $file File containing the translation
     */
    public function load($name, $file)
    {
        // Set the path to the file
        $file = $this->path . $file;

        // Init the translation
        $this->translations[$name] = $file;
        $this->words[$name] = array();

        if (file_exists($file))
        {
            // Get the content
            $content = parse_ini_file( $file, true);

            // Load the translation
            foreach ($content as $key=>$value)
            {
                $this->words[$name][$key] = $value;
            }
        }
    }

    /**
     * Translate the given text according to the existing translations
     * @param  string $text Text to translate
     * @return string Translated text
     */
    public function translate($text)
    {
        if ($this->language != '.')
        {
            // Make the translation string
            if (isset($this->words[$this->language][$text]) && strlen($this->words[$this->language][$text]) > 0)
            {
                return $this->words[$this->language][$text];
            }
            else
            {
                $this->add($text);
                return $text;
            }
        }
        else
        {
            return $text;
        }
    }


    /**
     * Add new text in the translation files
     * @param string $text Text to be added
     */
    public function add($text)
    {
        // Check if the localization folder exists
        if (!file_exists($this->path))
        {
            @mkdir($this->path, '0777');
        }

        foreach ($this->translations as $name=>$file)
        {
            if (!isset($this->words[$name][$text]) || strlen($this->words[$name][$text]) == 0)
            {
                if ($name != $this->default)
                {
                    $this->words[$name][$text] = '""';
                }
                else
                {
                    $this->words[$name][$text] = $text;
                }

                w($file, starfish::obj('common')->arr2ini($this->words[$name]));
            }
        }
    }

    /**
     * Change the current language of the script
     * @param string $name Name of the language, as specified in the key of the language list
     */
    public function change($name)
    {
        if (isset($this->translations[$name]))
        {
            $this->language = $name;
            cookie('language', $name);
        }
    }
}


/**
* Aliases used by class for easier programming
*/
function __() { return call_user_func_array(array(obj('localization'), 'translate'),    func_get_args()); }
?>