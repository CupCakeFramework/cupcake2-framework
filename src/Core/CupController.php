<?php

namespace CupCake2\Core;

class CupController extends CupCore {

    private $templateInicial = 'template_padrao';

    public function inicializar() {
        $this->renderer->template = $this->templateInicial;
        return parent::inicializar();
    }

    public function renderizar($nomeView, $variaveis, $retornar) {
        return $this->renderer->renderizar($nomeView, $variaveis, $retornar);
    }

    public function renderizarParcial($nomeView, $variaveis, $retornar) {
        return $this->renderer->renderizarParcial($nomeView, $variaveis, $retornar);
    }

    public function setTemplate($template) {
        $this->renderer->template = $template;
    }

}