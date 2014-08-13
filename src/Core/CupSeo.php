<?php

namespace CupCake2\Core;

class CupSeo extends CupCore{

    public function metatags() {
        $pagina = str_replace($this->baseUrl, '/', $_SERVER['REQUEST_URI']);
        $qry = mysql_query('select * from tbl_sys_seo where nome like "' . $pagina . '" or nome like "' . $pagina . '/" limit 1');
        $row = mysql_fetch_assoc($qry);

        $info = $this->arrayToUtf8($row);
        if (!empty($info)) {
            return $this->montaMetatags($info);
        } else {
            return $this->metatagsPadrao();
        }
    }

    public function metatagsPadrao() {
        $qry = mysql_query('select * from tbl_sys_seo where id = 1 limit 1');
        $d = $this->arrayToUtf8(mysql_fetch_assoc($qry));
        $d['seo_title'] .= ' - ' . $this->titulo;
        return $this->montaMetatags($d);
    }

    public function montaMetatags($d) {
        $retorno = '<title>' . $this->tituloSite;
        if (!empty($this->titulo)) {
            $retorno .= ' - ' . $this->titulo;
        }
        $retorno .= '</title>';
        $retorno .= '<meta name="Keywords" content="' . $d['seo_keywords'] . '"/>';
        $retorno .= '<meta name="Description" content="' . $d['seo_description'] . '"/>';
        $retorno .= '<meta name="Robots" content="ALL"/>';
        $retorno .= '<meta name="Robots" content="INDEX,FOLLOW"/>';
        $retorno .= '<meta name="Revisit-After" content="1 Days"/>';
        $retorno .= '<meta name="Rating" content="General"/>';

        return $retorno;
    }

    public function analytics() {
        $d = $this->siteDados();
        if (!empty($d['script_analytics'])) {
            return "<script type=\"text/javascript\">
                    var _gaq = _gaq || [];
                    _gaq.push(['_setAccount', '" . $d['script_analytics'] . "']);
                    _gaq.push(['_trackPageview']);
                    (function() {
                        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                    })();
                </script>";
        }
    }

}
