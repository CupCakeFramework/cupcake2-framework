<?php

namespace CupCake2\Core;

use Exception;

class CupRenderer {

    private $pastaTemplates = 'app/views/templates/';
    private $pastaViews = 'app/views/';
    public $template;

    /**
     * @var SiteController 
     */
    public $app;

    function __construct($app) {
        $this->app = $app;
    }

    public function renderizar($nomeView, $variaveis = array(), $retornar = false) {
        if (!is_array($variaveis)) {
            throw new Exception("Variável ''$variaveis'' não é um array.");
        }

        $view = $this->pastaViews . $nomeView . '.php';
        if (!file_exists($view)) {
            throw new Exception("A View $view não foi encontrada");
        }
        $template = $this->pastaTemplates . $this->template . '.php';
        if (!file_exists($template)) {
            throw new Exception("O template $template não foi encontrado");
        }
        
        $conteudo = $this->render($view, $variaveis, true);
        $variaveis['conteudo'] = $conteudo;
        return $this->render($template, $variaveis, $retornar);
    }

    public function renderizarParcial($nomeView, $variaveis = array(), $retornar = false) {
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
        return $this->render($view, $variaveis, $retornar);
    }

    protected function render($arquivoParaRenderizar, $variaveis = array(), $retornar = false) {
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
