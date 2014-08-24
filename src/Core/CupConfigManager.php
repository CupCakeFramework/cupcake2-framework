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
    }

    public function getConfig() {
        return $this->config;
    }

    public function loadAllModuleConfigs() {
        $config = array();
        foreach ($this->environment['modules'] as $module) {
            $moduleClassName = "\\$module\\Module";
            $moduleClass = new $moduleClassName;
            $config = array_merge_recursive($config, $moduleClass->getConfig());
        }
        CupUtils::debug($config);
    }

    public function getEnvironmentConfigFromKey($key) {
        return $this->environment[$key];
    }

    public function getConfigFromKey($key) {
        return $this->config[$key];
    }

}
