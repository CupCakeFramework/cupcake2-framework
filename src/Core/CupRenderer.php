<?php

namespace CupCake2\Core;

use Exception;

class CupRenderer {

    /**
     * Array with all templates folder
     * @var Array 
     */
    public $templatesFolder;

    /**
     * Array with all views folder
     * @var Array 
     */
    public $viewsFolder;

    /**
     * The template file to be rendered
     * @var String 
     */
    public $templateFile;

    function __construct(Array $templatesFolder, Array $viewsFolder) {
        $this->templatesFolder = $templatesFolder;
        $this->viewsFolder = $viewsFolder;
    }

    public function getTemplateFile() {
        return $this->templateFile;
    }

    public function setTemplateFile(String $templateFile) {
        $this->templateFile = $templateFile;
    }

    private function resolveTemplate($templateFile) {
        foreach ($this->templatesFolder as $templateFolder) {
            if (file_exists($templateFolder . $templateFile)) {
                return $templateFolder . $templateFile;
            }
        }
        throw new Exception("O template $templateFile não foi encontrado em nenhum diretório do mapa de diretórios");
    }

    private function resolveView($viewFile) {
        foreach ($this->viewsFolder as $viewFolder) {
            if (file_exists($viewFolder . $viewFile)) {
                return $viewFolder . $viewFile;
            }
        }
        throw new Exception("A View $viewFile não foi encontrada em nenhum diretório do mapa de diretórios");
    }

    public function render($viewFile, $variaveis = array(), $retornar = false) {
        $view = $this->resolveView($viewFile);
        $template = $this->resolveTemplate($this->templateFile);
        $conteudo = $this->_render($view, $variaveis, true);
        $variaveis['conteudo'] = $conteudo;
        return $this->_render($template, $variaveis, $retornar);
    }

    public function renderPartial($nomeView, $variaveis = array(), $retornar = false) {
        if (!is_array($variaveis)) {
            throw new Exception("Variável \$variaveis não é um array");
        }
        $view = $this->pastaViews . $nomeView . '.php';
        if (!file_exists($view)) {
            $view = $this->pastaTemplates . $nomeView . '.php';
            if (!file_exists($view)) {
                throw new Exception("A View $view não foi encontrada");
            }
        }
        return $this->_render($view, $variaveis, $retornar);
    }

    protected function _render($arquivoParaRenderizar, $variaveis = array(), $retornar = false) {
        ob_start();
        if (!empty($variaveis) && is_array($variaveis)) {
            extract($variaveis);
        }
        include($arquivoParaRenderizar);
        $retorno = ob_get_contents();
        ob_end_clean();
        if ($retornar) {
            return $retorno;
        } else {
            print $retorno;
        }
    }

}
