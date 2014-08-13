<?php

namespace CupCake2\Core;

use CupCake2\Core\CupDataBase;
use CupCake2\Models\Seo;

class CupSeo {

    
    /**
     * @var CupDataBase 
     */
    private $db;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string 
     */
    private $tituloSite;

    /**
     *
     * @var string 
     */
    public $titulo;

    function __construct(CupDataBase $db, $baseUrl, $tituloSite) {
        $this->db = $db;
        $this->baseUrl = $baseUrl;
        $this->tituloSite = $tituloSite;
    }

    public function metatags() {
        $pagina = str_replace($this->baseUrl, '/', $_SERVER['REQUEST_URI']);
        $metatags = $em->getRepository("CupCake2\Models\Seo")->createQueryBuilder('o')
                ->where('o.url = :url')
                ->setParameter('url', "%$pagina%")
                ->getQuery()
                ->getResult();
        if ($metatags !== null) {
            return $this->montaMetatags($metatags);
        } else {
            return $this->metatagsPadrao();
        }
    }

    public function metatagsPadrao() {
        $metatags = $this->db->getEntityManager()->find('CupCake2\Models\Seo', 1);
        return $this->montaMetatags($metatags);
    }

    public function montaMetatags(Seo $metatags) {
        $retorno = '<title>' . $this->tituloSite;
        if (!empty($this->titulo)) {
            $retorno .= ' - ' . $this->titulo;
        }
        $retorno .= '</title>';
        $retorno .= '<meta name="Keywords" content="' . $metatags->getKeywords() . '"/>';
        $retorno .= '<meta name="Description" content="' . $metatags->getDescription() . '"/>';
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

    public function encodeSEOString($string) {
        $string = preg_replace("`\[.*\]`U", "", $string);
        $string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $string);
        $string = preg_replace(array("`[^a-z0-9]`i", "`[-]+`"), "-", $string);
        return strtolower(trim($string, '-'));
    }

    public function decodeSEOString($string) {
        return str_replace('-', ' ', $string);
    }

}
