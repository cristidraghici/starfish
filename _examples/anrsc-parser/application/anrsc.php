<?php
if (!class_exists('starfish')) { die(); }

class anrsc
{
    function get_judete($html)
    {
        $html = html_entity_decode($html);
        /*
        1 - coords
        2 - nume judet
        3 - link
        4 - titlu

        // <area coords="([^"]+)" alt="([^"]+)" shape="poly" href="([^"]+)" title="([^"]+)" />
        */

        $data = array();
        preg_match_all('#<area coords="([^"]+)" alt="([^"]+)" shape="poly" href="([^"]+)" title="([^"]+)" />#', $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $key=>$value)
        {
            if (strlen($value[3]) > 0)
            {
                $value[3] = 'http://www.anrsc.ro' . $value[3];
                $data[] = $value;
            }
        }

        return $data;
    }

    function get_orase($html)
    {
        $html = html_entity_decode($html);
        /*
        1 - coords
        2 - link
        // <area shape="poly" coords="([^"]+)" href="([^"]+)" />
        */

        $links = array();
        preg_match_all('#<area shape="poly" coords="([^"]+)" href="([^"]+)" />#i', $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $key=>$value)
        {
            $links[ 'http://www.anrsc.ro' . $value[2] ] = 'http://www.anrsc.ro' . $value[2];
        }
        preg_match_all('#<area href="([^"]+)" coords="([^"]+)" shape="poly" />#i', $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $key=>$value)
        {
            $links[ 'http://www.anrsc.ro' . $value[1] ] = 'http://www.anrsc.ro' . $value[1];
        }

        $links = array_values($links);
        return $links;
    }

    function get_detalii($html)
    {
        $html = html_entity_decode($html);
        $data = array();

        $html = get_html($html, '<body', '</body>');
        $html = trim(escapeBadHTML($html, 'h1|h2|h3|h4|h5|div|p|a|body'));
        $html = str_replace(array('<p></p>'), '', $html);


        // Get the tabs
        $tabs = get_html($html, '<div class="jwts_tabber" id="jwts_tab1">', '</body>');

        // The rest
        $html = substr($html, 0, -strlen($tabs) );
        //$data['oras_text'] = $html;

        $html = str_replace(array('<p>', '</p>'), array('<td>', '</td>'), $html);    
        $tds = get_td($html);
        $data['oras'] = $tds[0];

        preg_match('#(CIF|Fiscal)+([^0-9]*)([0-9]+)</td>#i', $html, $match);
        $data['cif'] = $match[3];
        preg_match('#SIRUTA([^0-9]*)([0-9]+)</td>#i', $html, $match);
        $data['siruta'] = $match[2];

        $tabs = cleanHTML($tabs);
        $tabs = trim(escapeBadHTML($tabs, 'h2|p'));
        $parts = explode("<h2>", $tabs);

        foreach ($parts as $key=>$value)
        {
            // Salubrizare
            if (stristr($value, 'Salubrizare'))
            {
                if (stristr($value, 'Nu se presteaz')) {
                    $data['salubrizare'] = 0;
                }
                else
                {
                    $data['salubrizare'] = 1;
                }

                $data['salubrizare_text'] = $value;
            }

            // Iluminat public
            if (stristr($value, 'Iluminat public'))
            {
                if (stristr($value, 'Nu se presteaz')) {
                    $data['iluminat'] = 0;
                }
                else
                {
                    $data['iluminat'] = 1;
                }

                $data['iluminat_text'] = $value;
            }

            // Transport local
            if (stristr($value, 'Transport local'))
            {
                if (stristr($value, 'Nu se presteaz') || stristr($value, 'Nu presteaz') ) {
                    $data['transport'] = 0;
                }
                else
                {
                    $data['transport'] = 1;
                }

                $data['transport_text'] = $value;
            }

            // Energie termica
            if (stristr($value, 'Energie Termic'))
            {
                if (stristr($value, 'Nu se presteaz') || stristr($value, 'Nu presteaz') ) {
                    $data['termic'] = 0;
                }
                else
                {
                    $data['termic'] = 1;
                }

                $data['termic_text'] = $value;
            }

            // Alimentare cu apa
            if (stristr($value, 'Alimentare cu Ap'))
            {
                if (stristr($value, 'Nu se presteaz') || stristr($value, 'Nu presteaz') ) {
                    $data['apa'] = 0;
                }
                else
                {
                    $data['apa'] = 1;
                }

                $data['apa_text'] = $value;
            }
        }

        return $data;
    }
}
?>