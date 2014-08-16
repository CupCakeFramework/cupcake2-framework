<?php

namespace CupCake2\Core;

use CupCake2\Core\CupRenderer;
use CupCake2\Core\CupCore;

class CupRequestDispatcher {

    const sulfixo_controle = 'control_';

    public $paginaSolicitada;
    public $paginaAtual;
    public $request;

    /**
     * @var CupCore 
     */
    private $app;

    /**
     * @var CupRenderer
     */
    public $renderer;

    function __construct(CupCore $app, CupRenderer $renderer) {
        $this->app = $app;
        $this->renderer = $renderer;
        $this->request = $_GET;
    }

    public function erro_404() {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        header("Status: 404 Not Found");
        $_SERVER['REDIRECT_STATUS'] = 404;
        $this->renderer->renderizar('404');
    }

    public function redirect($url, $interno = true) {
        //Caso parametro URL esteja em branco será redirecionado para a raíz (Home)
        if ($interno) {
            header('Location: ' . BASE_URL . $url);
        } else {
            header('Location: ' . $url);
        }
        exit;
    }

    public function dispatch() {
        if (empty($this->request['a'])) {
            $this->request['a'] = 'home';
        }
        $this->paginaSolicitada = str_replace('-', '_', $_GET['a']);
        if (empty($this->paginaSolicitada)) {
            $this->paginaSolicitada = 'home';
        }
        $this->paginaAtual = $this->request['a'];
        $acao = self::sulfixo_controle . $this->paginaSolicitada;
        if (method_exists($this->app, $acao)) {
            $reflection = new ReflectionMethod($this->app, $acao);
            $qtdArgumentos = $reflection->getNumberOfParameters();
            $parametros = array();
            $i = 0;
            foreach ($_GET as $key => $value) {
                if (!empty($value) && $key != 'a' && $i <= $qtdArgumentos)
                    $parametros[$i] = $value;
                $i++;
            }

            if (count($parametros) < $qtdArgumentos) {
                $i = $qtdArgumentos - count($parametros);
                for ($index = 0; $index < $i; $index++) {
                    $parametros[$index + count($parametros) + 1] = '';
                }
            }

            call_user_func_array(array($this->app, $acao), $parametros);
        } else {
            $this->erro_404();
        }
    }

}
