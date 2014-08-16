<?php

namespace CupCake2\Core;

class CupMessenger {

    public function adicionarMensagemErro($mensagem) {
        $this->adicionarMensagem($mensagem, 2);
    }

    public function adicionarMensagemSucesso($mensagem) {
        $this->adicionarMensagem($mensagem, 1);
    }

    public function adicionarMensagem($mensagem, $tipo = 0) {
        /*
         * Tipos
         * 0 = Neutro
         * 1 = Sucesso
         * 2 = Erro
         */
        switch ($tipo) {
            case 0:
            default:
                $classeErro = 'info';
                break;
            case 1:
                $classeErro = 'success';
                break;
            case 2:
                $classeErro = 'error';
                break;
        }
        if (empty($_SESSION[$this->siteUrl])) {
            $_SESSION[$this->siteUrl] = array();
        }

        array_push($_SESSION[$this->siteUrl], array('mensagem' => $mensagem, 'classe' => $classeErro));
    }

    public function listarMensagens() {
        $mensagens = $_SESSION[$this->siteUrl];
        $this->removerMensagens();
        return $mensagens;
    }

    public function existeMensagens() {
        return !empty($_SESSION[$this->siteUrl]);
    }

    public function exibirMensagens() {
        $mensagens = $this->listarMensagens();

        foreach ((array) $mensagens as $mensagem) {
            echo '<div class="alert alert-' . $mensagem['classe'] . '">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    ' . $mensagem['mensagem'] . '
                  </div>';
        }
    }

    public function removerMensagens() {
        unset($_SESSION[$this->siteUrl]);
    }

}

