<?php
if (!class_exists('starfish')) { die(); }

/**
 * HTML Parser
 *
 * @package starfish
 * @subpackage starfish.objects.htmlparser
 * 
 * @see https://github.com/cristidraghici/PHPparser
 */
class htmlparser
{	
        #####################
        # Create and use a runner
        #####################
        
        // add urls
        
        // download the urls
        
        // process the url
        
        
        #####################
        # Manipulate the HTML
        #####################
        
        /**
         * Extract a html block
         * 
         * @param string $html The HTML code where to search
         * @param string $start The which starts the block
         * @param string $end The string which ends the block
         * 
         * @return string The block, if it exists
         */
        function get_html($html, $start, $end) 
        {
                $row_s = strpos ( $html, $start);
                $row_e = strpos($html, $end, $row_s);
                $row_e = $row_e - $row_s + strlen($end);
                $thtml = substr($html,$row_s,$row_e);
                if ($row_s > 0 && $row_e < strlen($html))
                {
                        $html = $thtml;
                }
                else
                {
                        $html = "";
                }
                return $html;
        }
        /**
         * Get the list of tables inside a code
         * 
         * @param string $html The HTML code where to search
         * @return array A list of all the tables inside the code
         */
        function get_table($html)
        {
                $pos = 0;
                $a = 0;
                preg_match_all('/<table([^>]*)>/',$html,$matches);
                foreach ($matches[0] as $item) {
                        $start = $item;
                        $end   = "</table>";
                        $row_s = strpos ($html,$start,$pos);
                        if ( $row_s != 0 )
                        {
                                $row_e = strpos($html, $end, $row_s);
                                $row_e = $row_e - $row_s + strlen($end);
                                $text = substr($html,$row_s+strlen($start),$row_e-strlen($start)-strlen($end));
                                $pos = $row_s + $row_e;
                        }
                        $result[$text] = $text;
                }
                if (is_array($result))
                {
                        $result = @array_values($result);
                }
                else
                {
                        $result = array();
                }
                return $result;
        }
        /**
         * Get the list of TRs inside a html table
         * 
         * @param string $html The HTML code where to search
         * @return array A list of all the TRs inside a table
         */
        function get_tr($html)
        {
                $pos = 0;
                $a = 0;
                preg_match_all('/<tr([^>]*)>/',$html,$matches);
                foreach ($matches[0] as $item) {
                        $start = $item;
                        $end   = "</tr>";
                        $row_s = strpos ($html,$start,$pos);
                        if ( $row_s != 0 )
                        {
                                $row_e = strpos($html, $end, $row_s);
                                $row_e = $row_e - $row_s + strlen($end);
                                $text = substr($html,$row_s+strlen($start),$row_e-strlen($start)-strlen($end));
                                $pos = $row_s + $row_e;
                        }
                        $result[$text] = $text;
                }
                if (is_array($result))
                {
                        $result = @array_values($result);
                }
                else
                {
                        $result = array();
                }
                return $result;
        }
        /**
         * Get the list of TDs inside a TR
         * 
         * @param string $html The HTML code where to search
         * @return array A list of all the TDs inside a TR
         */
        function get_td ($html)
        {
                // make content corrections
                $html = " ".$html;
                // get tds
                $result = array();
                $pos = 0;
                preg_match_all('/<td([^>]*)>/',$html,$matches);
                foreach ($matches[0] as $item)
                {
                        $start = $item;
                        $end   = "</td>";
                        $row_s = strpos ($html, $start, $pos);
                        if ( $row_s != 0 )
                        {
                                $row_e = strpos($html, $end, $row_s);
                                $row_e = $row_e - $row_s + strlen($end);
                                $text = substr($html,$row_s+strlen($start),$row_e-strlen($start)-strlen($end));
                                $pos = $row_s + $row_e;
                        }
                        $result[] = $text;
                }

                return $result;
        }
        /**
         * Remove the unnecessary HTML tags
         * 
         * @param string $html The HTML code where to search
         * @return string The new HTML code
         */
        function escapeBadHTML($str,$allowed='tr|td|br')
        {
                $str = preg_replace("/<((?!\/?($allowed)\b)[^>]*)>/xis", "", $str);  
                $str = str_replace(array("\r","\n","\t","&nbsp;"),"",$str);    
                return $str;
        }
        
        /**
         * Make a uniformization for the HTML code
         * 
         * @param string $html The HTML code where to search
         * @return string The new HTML code
         */
        function simplifyHTML($html)
        {
                $html = $this->closetags($html);
                
                // Uniformization of spaces
                $html = str_replace(array("\t", "\r"), "\n", $html);
                $html = preg_replace("/\n+/", "\n", $html);
                
                // Remove extra spaces
                $html = str_replace(array("&nbsp;", "&nbsp"), " ", $html);
                $html = preg_replace("/\s+/", " ", $html);
                
                // All links in the same format
                // @see http://www.mkyong.com/regular-expressions/how-to-extract-html-links-with-regular-expression/
                $html = preg_replace('#\s*(?i)href\s*=\s*(\"([^"]*\")|\'[^\']*\'|([^\'">\s]+))#', ' href="{1}" ', $html);
                $html = preg_replace('#\s*(?i)src\s*=\s*(\"([^"]*\")|\'[^\']*\'|([^\'">\s]+))#', ' src="{1}" ', $html);
                
                // Turn the content of all tags to lowecase
                
                // Remove table details
                $html = preg_replace("#<table([^>]*)>#is", "<table>", $html);
                $html = preg_replace("#<tr([^>]*)>#is", "<tr>", $html);
                $html = preg_replace("#<td([^>]*)>#is", "<td>", $html);
                
                
                // remove the script tags
                $html = preg_replace("/<script[^>]*>.*?< *script[^>]*>/i", "", $html);
                // Prevent linking to source files
                $html = preg_replace("/<script[^>]*>/i", "", $html);
                
                return $html;
                
        }

        function cleanHTML($html)
        {
                $html = $this->closetags($html);
                
                // remove tag classes and comments
                $html = preg_replace("#<([^>\s]*)([^>]*)>#i", "<$1>", $html);

                // Remove extra spaces
                $html = str_replace(array("&nbsp;", "&nbsp"), " ", $html);
                $html = preg_replace("/\s+/", " ", $html);

                // remove the script tags
                $html = preg_replace("/<script[^>]*>.*?< *script[^>]*>/i", "", $html);
                // Prevent linking to source files
                $html = preg_replace("/<script[^>]*>/i", "", $html);

                // return the code
                return $html;
        }
        
        /**
         * Close open HTML tags inside the provided code
         * 
         * @param string $html The HTML code
         * @return string The code with the new formatiing
         */
        function closetags ( $html )
        {
                #put all opened tags into an array
                preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
                $openedtags = $result[1];
                #put all closed tags into an array
                preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
                $closedtags = $result[1];
                $len_opened = count ( $openedtags );
                # all tags are closed
                if( count ( $closedtags ) == $len_opened )
                {
                        return $html;
                }
                $openedtags = array_reverse ( $openedtags );
                # close tags
                for( $i = 0; $i < $len_opened; $i++ )
                {
                        if ( !in_array ( $openedtags[$i], $closedtags ) )
                        {
                                $html .= "</" . $openedtags[$i] . ">";
                        }
                        else
                        {
                                unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
                        }
                }
                return $html;
        }
}
?>