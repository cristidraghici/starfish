<?php
if (!class_exists('starfish')) { die(); }

/**
 * Pagination object
 *
 * @package starfish
 * @subpackage starfish.objects.pagination
 */

class pagination
{
        /**
         * Used variables
         * 
         * $pages - number of pages to show in the pagination links
         * $language - the default language
         * $template - the template to use for html. The default values are:
         *              - helpers-clean
         *              - helpers-bootstrap
         */
        public $pages = 5;
        public $language = 'en';
        public $template = 'helpers-clean';

        /**
	 * Init the script
	 */
        public function init()
        {
                // Load the default language (en) from the helper folder
                require_once( starfish::config('_starfish', 'root') . 'helpers/pagination/language/en.php' );

                return true;
        }

        /**
         * Create a pagination object
         * 
         * @param number $total Total number of results
         * @param number $row Total number of rows
         * @param number $page The current pages
         * @param string $link The link on which the pagination is applies. 
         *                      * Must contain the element {page}
         */
        public function nav($total, $rows, $page=1, $link='', $pages=null)
        {
                if ($pages == null)
                {
                        $pages = $this->pages;
                }


                // Make the limits
                $limits = $this->setLimits($total, $rows, $page);

                // Generate the links
                $links = $this->setLinks($limits, $page, $pages, $link);

                // Make and return the html
                return $this->makeHtml($links);
        }

        /**
         * Set the template for the pagination
         * 
         * @param string $name Name of the template to use for the pagination
         */
        public function setTemplate($name)
        {
                switch ($name)
                {
                        case 'helpers-bootstrap':
                        case 'helpers-clean':

                        $this->template = $name;
                        return true;

                        break;

                        default:
                        // Check if file exists inside the templates folder
                        if (file_exists(starfish::config('_starfish', 'template') . $name))
                        {
                                $this->template = $name;
                        }

                        return true;

                        break;
                }

                return false;
        }

        /**
         * Calculate the limits for the pagination
         * 
         * @param number $total Total number of results
         * @param number $row The current row
         * @param number $page The current page
         * 
         * @return array|boolean false Calculated values or false if error encountered
         */
        public function setLimits($total, $rows, $page=1)
        {
                if ($total > $rows)
                {
                        $nrpages = ceil($total/$rows);
                        if ( ($min = ($page - 1) * $rows) && $min == 0) { $min = 1; }
                        if ( ($max = $page * $rows) && $max > $total) { $max = $total; }

                        return array(
                                'nrpages' => $nrpages,
                                'min' => $min,
                                'max' => $max
                        );
                }

                return false;
        }

        /**
         * Put the pagination values inside HTML code
         * 
         * @param string $name Name of the language to use
         *              - en - can be used without string parameters
         * @param array $strings Values for the params
         *              - first_page
         *              - last_page
         */
        public function setLanguage($name, $strings=array() )
        {
                if (count($strings) == 0)
                {
                        $file = starfish::config('_starfish', 'root') . 'helpers/pagination/language/'.$name.'.php';
                        if (file_exists($file))
                        {
                                $this->language = $name;
                                require_once( $file );
                        }
                }
                elseif (count($strings) == 2)
                {
                        if (isset($strings['first_page']))
                        {
                                starfish::config('_helpers-pagination-'.$name, 'first_page', $strings['first_page']);
                        }
                        if (isset($strings['last_page']))
                        {
                                starfish::config('_helpers-pagination-'.$name, 'last_page', $strings['last_page']);
                        }

                        $this->language = $name;
                }

                return false;
        }


        /**
         * Make the link list
         * 
         * @param array $limits The limits for the links
         *              - nrpages
         *              - min
         *              - max
         * @param number $page The current page
         * @param number $pages This parameter is the number of pages shown in the pagination links list
         * @param string $link The base links
         * 
         * @return array The list of links and their classes
         */
        public function setLinks($limits, $page, $pages, $link)
        {
                // Variables
                $nrpages = $limits['nrpages'];
                $min = $limits['min'];
                $max = $limits['max'];

                // Alter the link form
                if (substr($link, 0, 2) == './')
                {
                        $link = starfish::config('_starfish', 'site_url') . substr($link, 2);
                }

                // The links list
                $output = array();

                for ($i=1; $i<=$nrpages; $i++)
                {
                        if ($i <= ($page - $pages) || $i >= ($page + $pages))
                        {
                                if ($i == 1)
                                {
                                        $text = starfish::config('_helpers-pagination-'.$this->language, 'first_page');

                                        // first
                                        $output['first'] = array(
                                                'name'  => $text,
                                                'link'  => str_replace('{page}', $i, $link),
                                                'class' => ''
                                        );
                                }
                                if ( $i == $nrpages )
                                {
                                        $text = starfish::config('_helpers-pagination-'.$this->language, 'last_page');

                                        // last
                                        $output['last'] = array(
                                                'name'  => $text,
                                                'link'  => str_replace('{page}', $i, $link),
                                                'class' => ''
                                        );
                                }
                        }

                        if ( $page > 1)
                        {
                                $prevpage = $page - 1;
                                $output['prev'] = array(
                                        'name'  => '&laquo;',
                                        'link'  => str_replace('{page}', $prevpage, $link),
                                        'class' => ''
                                );
                        }
                        if ( $i <= ($page + $pages) && $i >= ($page - $pages) )
                        {
                                if ( $page == $i )
                                {
                                        $output['pages'][] = array(
                                                'name'=>$i,
                                                'class'=>'active'
                                        );
                                }
                                else
                                {
                                        $output['pages'][] = array(
                                                'name'  => $i,
                                                'link'  => str_replace('{page}', $i, $link),
                                                'class' => ''
                                        );
                                }
                        }
                        if ( $page < $nrpages)
                        {
                                $nextpage = $page + 1;
                                $output['next'] = array(
                                        'name'  => '&raquo;',
                                        'link'  => str_replace('{page}', $nextpage, $link),
                                        'class' => ''
                                );
                        }
                }


                // Order the links
                $data = array();

                if (isset($output['first']) && is_array($output['first']))
                {
                        $data[] = $output['first'];
                }
                // show prev
                if (isset($output['prev']) && is_array($output['prev']))
                {
                        $data[] = $output['prev'];
                }
                // show pages
                if (isset($output['pages']) && is_array($output['pages']))
                {
                        foreach ($output['pages'] as $key=>$item)
                        {
                                $data[] = $item;
                        }
                }
                // show next
                if (isset($output['next']) && is_array($output['next']))
                {
                        $data[] = $output['next'];
                }
                // show last
                if (isset($output['last']) && is_array($output['last']))
                {
                        $data[] = $output['last'];
                }

                return $data;
        }

        /**
         * Put the pagination values inside HTML code
         * 
         * @param array $links The list of links to insert into the html
         * @return string The HTML code for the pagination
         */
        public function makeHtml($links)
        {
                // Set the template filename
                $file = '';
                switch ($this->template)
                {
                        case 'helpers-bootstrap':
                        $file = starfish::config('_starfish', 'root') . 'helpers/pagination/themes/bootstrap/pagination.tpl.php';
                        break;

                        case 'helpers-clean':
                        $file = starfish::config('_starfish', 'root') . 'helpers/pagination/themes/clean/pagination.tpl.php';
                        break;

                        default:
                        $file = starfish::config('_starfish', 'template') . $this->template;
                        break;
                }

                // Load the template file                
                return reg::obj('tpl')->view($file, array(
                        'data'=>$links
                ));
        }


}
?>