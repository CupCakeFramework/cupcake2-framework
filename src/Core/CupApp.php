<?php

namespace CupCake2\Core;

use CupCake2\Core\CupCore;

class CupApp extends CupCore {

    private $templateInicial = 'main_template';

    public function inicializar() {
        $this->renderer->template = $this->templateInicial;
        return parent::inicializar();
    }

    public function renderizar($nomeView, $variaveis = array(), $retornar = false) {
        return $this->renderer->render($nomeView, $variaveis, $retornar);
    }

    public function renderizarParcial($nomeView, $variaveis = array(), $retornar = false) {
        return $this->renderer->renderPartial($nomeView, $variaveis, $retornar);
    }

    public function setTemplate($template) {
        $this->renderer->template = $template;
    }

    public function metatags() {
        return $this->seo->metatags();
    }

    public function dbg($var, $tipo = 2) {
        return $this->utils->dbg($var, $tipo);
    }

}
