<?php

namespace CupCake2\Core;

class CupConfigManager {

    private $config;
    private $environment;

    function __construct($environment) {
        $this->environment = $environment;
        $this->loadConfig();
    }

    public function loadConfig() {
        $this->config = $this->loadAllConfigFiles();
        $this->checkValidConfig();
    }

    public function checkValidConfig() {
        if (!empty($this->config['BASE_URL'])) {
            $this->baseUrl = $this->config['BASE_URL'];
        }
        if (!empty($this->config['SITE_URL'])) {
            $this->siteUrl = $this->config['SITE_URL'];
        }
        if (!empty($this->config['TITULO_SITE'])) {
            $this->tituloSite = $this->config['TITULO_SITE'];
        }

        if (empty($this->baseUrl) || empty($this->siteUrl) || empty($this->tituloSite)) {
            die('Por favor configure seu arquivo "config/main.php" corretamente');
        }
    }

    public function getConfig() {
        return $this->config;
    }

    public function loadAllConfigFiles() {
        print_r($this->environment);
        die();
    }
    
    public function getEnvironmentConfigFromKey($key){
        return $this->environment[$key];
    }
    
    public function getConfigFromKey($key){
        return $this->config[$key];
    }

}
