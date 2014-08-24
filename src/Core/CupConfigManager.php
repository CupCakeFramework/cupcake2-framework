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
        $this->config = $this->loadAllModuleConfigs();
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

    public function loadAllModuleConfigs() {
        $config = array();
        foreach ($this->environment['modules'] as $module) {
            $moduleClassName = "\\$module\\Module";
            $moduleClass = new $moduleClassName;
            $config = array_merge($config, $moduleClass->getConfig());
        }
        print_r($config);
        die();
    }

    public function getEnvironmentConfigFromKey($key) {
        return $this->environment[$key];
    }

    public function getConfigFromKey($key) {
        return $this->config[$key];
    }

}
