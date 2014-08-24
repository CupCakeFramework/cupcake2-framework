<?php

namespace CupCake2\Core;

use CupCake2\Core\CupRouter;
use CupCake2\Core\CupDataBase;
use CupCake2\Core\CupSeo;
use CupCake2\Core\CupRequestDispatcher;
use CupCake2\Core\CupRenderer;
use CupCake2\Core\CupUtils;
use CupCake2\Core\CupConfigManager;

class CupCore {

    public $baseUrl;
    public $siteUrl;
    public $titulo;
    public $tituloSite;
    public $publicAssetsUrl;

    /**
     *
     * @var CupConfigManager;
     */
    public $config;

    /**
     * @var CupRouter 
     */
    public $router;

    /**
     * @var CupRequestDispatcher 
     */
    public $request;

    /**
     * @var CupDataBase 
     */
    public $db;

    /**
     * @var CupSeo 
     */
    public $seo;

    /**
     * @var CupRenderer 
     */
    public $renderer;

    /**
     *
     * @var CupUtils 
     */
    public $utils;
    
    /**
     * Arquivo de configurações do ambiente
     * @var Array 
     */
    public $environment;

    public function __construct($environment) {
        $this->environment = $environment;
        $this->config = new CupConfigManager($this->environment);
        $this->publicAssetsUrl = $this->url(array('public_assets'));
        $this->renderer = new CupRenderer($this);
        $this->db = new CupDataBase($this->config);
        $this->router = new CupRouter();
        $this->seo = new CupSeo($this->db, $this->baseUrl, $this->tituloSite);
        $this->request = new CupRequestDispatcher($this, $this->renderer);
        $this->utils = new CupUtils();
    }

    
    public function inicializar() {
        ob_start();
        session_start();
        $this->request->dispatch();
        ob_end_flush();
    }

    /**
     * Gera uma URL para o site.
     * @param array $caminho Caminho cada item corresponde a um diretório. Ex: array('caminho','parametro') = http://seuprojeto.com/caminho/parametro/
     * @param mixed $urlBase A BaseUrl para gerar a url. Por padrão é utilizado a constante BASE_URL.
     * @return string A Url Gerada
     */
    public function url($caminho = '', $urlBase = '') { //Caminho em branco para retornar por padrão a "home"
        $url = empty($urlBase) ? $this->baseUrl : $urlBase;
        if (is_array($caminho)) {
            foreach ($caminho as $value) {
                if (strpos($value, '.') === false) {
                    $url .= $value . '/';
                } else {
                    $url .= $value;
                }
            }
        } else {
            $url .= $caminho;
        }
        return $url;
    }

}
