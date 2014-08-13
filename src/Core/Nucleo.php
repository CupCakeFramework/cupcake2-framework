<?php

namespace CupCake2\Core;

use CupCake2\Core\Router;
use CupCake2\Core\CupORM;
use ReflectionMethod;
use stdClass;

class Nucleo {

    const sulfixo_controle = 'control_';
    public $baseUrl;
    public $siteUrl;
    private $site;
    private $pastaTemplates = 'App/Views/Templates/';
    private $pastaViews = 'App/Views/';
    public $titulo;
    public $tituloSite;
    public $template;
    public $paginaAtual;
    public $request;
    public $publicAssetsUrl;
    public $router;
    public $config;
    public $db;

    public function __construct(array $config) {
        $this->config = $config;
        $this->loadConfig();
    }

    public function loadConfig() {
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
            die('Por favor configure seu arquivo "Config/main.php" corretamente');
        }
    }

    public function inicializar() {
        @session_start();
        $this->db = new CupORM($this->config['database']);
        $this->router = new Router();
        if (empty($_GET['a']))
            $_GET['a'] = 'home';
        $this->request = $this->array_to_object($_GET);
        $paginaSolicitada = str_replace('-', '_', $_GET['a']);
        $this->publicAssetsUrl = $this->url(array('public_assets'));
        if (empty($paginaSolicitada))
            $paginaSolicitada = 'home';
        $this->paginaAtual = $_GET['a'];
        $acao = self::sulfixo_controle . $paginaSolicitada;
        if (method_exists($this, $acao)) {
            $reflection = new ReflectionMethod($this, $acao);
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

            call_user_func_array(array($this, $acao), $parametros);
        } else {
            $this->erro_404();
        }
    }

    public function site() {
        if (empty($this->site)) {
            $this->site = $this->array_to_object($this->siteDados());
        }
        return $this->site;
    }

    public function setUrlRetorno($url = array()) {
        $_SESSION['urlRetorno'] = $this->url($url, $this->siteUrl);
    }

    public function urlRetorno() {
        return $_SESSION['urlRetorno'];
    }

    public function resetUrlRetorno() {
        $this->setUrlRetorno();
    }

    public function tags($tabela) {
        $sSql = "SELECT lower(tags) as tags FROM " . $tabela . ' where ativo = "Sim"';
        $resultado = mysql_query($sSql);
        // agora pegamos a resposta de nosso sql e transformamo em um array, que tem como índice as tags que usaremos e como valor as quantidades
        if (mysql_num_rows($resultado) == 0) {
            return '';
        } else {
            while ($row = mysql_fetch_array($resultado)) {
                $tgs = explode(';', $row['tags']);
                foreach ($tgs as $key => $value) {
                    if (!isset($array_tags)) {
                        $array_tags = array(ltrim($value));
                    } else {
                        array_push($array_tags, ltrim($value));
                    }
                }
            }
        }
        $tags_nome = array_unique($array_tags, SORT_STRING);
        $tags_qtd = array_count_values($array_tags);
        foreach ($array_tags as $key => $value) {
            $tags[$value] = $tags_qtd[$value];
            //por exemplo poderíamos montar um array de IDs se necessário da mesma forma $id[$row['tag']] = = $row['id'];
        }
        // Aqui setamos os tamanhos das fontes, usando porcentagens
        $max = 350; // máximo %
        $min = 100; // mínimo %
        // pegamos o maior e o menor número de vezes que as palavras aparecem pela quantidade no array
        $max_qtd = max(array_values($tags));
        $min_qtd = min(array_values($tags));
        // achamos a variação nos valores
        $variacao = $max_qtd - $min_qtd;
        if (0 == $variacao) // pra evitar divisão por 0
            $variacao = 1;
        // determinamos os incrementos nos tamanhos das fontes
        // sempre respeitando a quantidade de vezes que a tag aparecer
        $passo = ($max - $min) / ($variacao);
        //Navegando pelo array
        foreach ($tags as $key => $value) {
            // calculando o tamanho da fonte para o CSS
            $tam = $min + (($value - $min_qtd) * $passo);
            // No lugar de # vc coloca o caminho caso queira abrir algum caminho com a tag
            // Agora divirta-se e formate a saída da forma que achar conveniente
            $retorno[$i] = '<a href="' . BASE_URL . 'busca/' . urlencode($key) . '" style="font-size: ' . $tam . '%">';
            $retorno[$i] .= utf8_encode($key) . '</a> ';
            $i++;
            //caso tivéssemos montado nosso array de ID poderíamos fazer assim
            //$id[key] lhe daria o ID da tag atual por exemplo
        }
        return array('registros' => $retorno, 'sql' => $sSql, 'tags_nome' => $tags_nome, 'tags_qtd' => $tags_qtd, 'array_tags' => $array_tags);
    }

    /* Função de retorno padrão------------------------------------------------------------------------------------------ */
    /*     * ************************------------------------------------------------------------------------------------------ */

    public function retornoRegistroPadrao($tabela, $url = '', $pagina = 1, $qtd_registros = 0, $where_custom = 'where ativo = "Sim"', $campo_ordem = 'ordem', $campo_group = '') {
        //Adaptação do Where_Custom para array
        if (is_array($where_custom)) {
            foreach ($where_custom as $key => $value) {
                if (!empty($whereTemp))
                    $whereTemp .= ' and ';
                else
                    $whereTemp = ' where ';
                $whereTemp .= $key . ' = "' . $value . '" ';
            }
            $where_custom = $whereTemp;
        }

        $pasta_imagem = end(explode('tbl_', $tabela));
        $sql = 'SELECT tbl.* FROM `' . $tabela . '` tbl ' . $where_custom;
        if (!empty($campo_group)) {
            $sql .= ' group by ' . $campo_group;
        }

        if (trim(strtolower($campo_ordem)) == 'rand()') {
            $sql .=' order by ' . $campo_ordem . ' ';
        } else {
            $sql .=' order by tbl.' . $campo_ordem . ' ';
        }


        if (empty($pagina)) {
            $pagina = 1;
        }
        $primeiro_registro = ($pagina * $qtd_registros) - $qtd_registros;
        if ($primeiro_registro < 0) {
            $primeiro_registro = 0;
        }
        if ($qtd_registros != 0) {
            $sql_limit = ' LIMIT ' . $primeiro_registro . ',' . $qtd_registros;
        }
        $qry = mysql_query($sql . $sql_limit . ';') or die('SQL EXECUTADO : ' . $sql . $sql_limit . ' ---------- ERROR : ' . mysql_error());
        $erro = mysql_error();


        if (mysql_num_rows($qry) > 0) {
            $retorno = array();
            while ($row = mysql_fetch_assoc($qry)) {
                foreach ($row as $key => $value) {
                    switch ($key) {
                        case 'nome' :
                            $row['nome'] = utf8_encode($row['nome']);
                            $row['nome_url'] = $this->geraUrl($row['nome']);
                            break;
                        case 'descricao':
                            $row['resumo'] = utf8_encode($this->resumirStr($row['descricao']) . '...');
                            $row['descricao'] = utf8_encode($row['descricao']);
                            break;
                        case 'galeria' :
                            $row['galeria'] = $this->gerarGaleria($row['galeria'], $pasta_imagem);
                            if (!empty($row['galeria'])) {
                                $row['galeria_capa'] = reset($row['galeria']);
                            }
                            break;
                        case 'video_url' :
                            $row['video'] = $this->gerarVideo($row['video_url']);
                            break;
                        case 'imagem' :
                            $row['imagem_original'] = $row['imagem'];
                            $row['imagem'] = $this->gerarGaleria($row['imagem'], $pasta_imagem);
                            if (!empty($row['imagem'])) {
                                $row['imagem'] = end($row['imagem']); //Fix para não ter que ficar rodando como so fosse galeria
                            }
                            break;
                        case 'data_envio':
                            $row['data_envio'] = $this->data_agenda($row['data_envio']);
                            break;
                        default :
                            $row[$key] = utf8_encode($value);
                            break;
                    }
                }
                $row['tabela'] = $tabela;
                array_push($retorno, $row);
            }
        }


        /* paginacao------------------------------------------------------------------------------------------ */
        if ($qtd_registros != 0) {
            $sql_qtd = 'SELECT * FROM `' . $tabela . '` tbl ' . $where_custom;
            if (!empty($campo_group)) {
                $sql_qtd .= ' group by tbl.' . $campo_group;
            }

            if (trim(strtolower($campo_ordem)) == 'rand()') {
                $sql_qtd .=' order by ' . $campo_ordem . ' ';
            } else {
                $sql_qtd .=' order by tbl.' . $campo_ordem . ' ';
            }

            $qry_qtd = mysql_query($sql_qtd);

            $total = mysql_num_rows($qry_qtd);
            if ($total > $qtd_registros) {
                if ($qtd_registros != 0) {
                    $total_paginas = $total / $qtd_registros;
                } else {
                    $total_paginas = 2;
                }
                $prev = $pagina - 1;
                $next = $pagina + 1;
                $categoria = '';
                $total_paginas = ceil($total_paginas);
                $painel = "";
                $f = $pagina + 2;
                $f = ($f > $total_paginas) ? $total_paginas : $f;
                $n = $pagina - 2;
                $n = ($n < 1) ? 1 : $n;
                if ($n == 1 && $total_paginas > 5) {
                    $f = 5;
                } else {
                    $f = $pagina + 2;
                    $f = ($f <= $total_paginas) ? $f : $total_paginas;
                }
                for ($x = 1; $x <= $total_paginas; $x++) {
                    if ($x == $pagina) {
                        $painel .= '<li class="active"><a href="' . $url . $x . '">' . $x . '</a></li>';
                    } else {
                        $painel .= '<li><a class="paginacao-links" href="' . $url . $x . '">' . $x . '</a></li>';
                    }
                }
                /* Montagem da paginação em si conforme classes do bootstrap */
                if (!empty($painel)) {
                    $paginacao = '<ul class="pagination">';

                    if ($prev != $pagina && $prev >= 1) {
                        $paginacao .= '<li class="first"> <a class="typcn typcn-arrow-left" href="' . $url . $prev . '"></a></li>';
                    } else {
                        //$paginacao .= '<li class="disabled"> <a href="' . $url . $prev . '">&laquo;</a></li>';
                    }

                    $paginacao .= $painel;

                    if ($next != $pagina && $next <= $total_paginas) {
                        $paginacao .= '<li class="last"> <a class="typcn typcn-arrow-right" href="' . $url . $next . '"></a></li>';
                    } else {
                        //$paginacao .= '<li class="disabled"> <a href="' . $url . $next . '">&raquo;</a></li>';
                    }

                    $paginacao .= '</ul>';
                }
            }
        }
        return array(
            'registros' => $retorno,
            'paginacao' => $paginacao,
            'pagina' => $pagina,
            'pasta_imagem' => $pasta_imagem,
            'tabela' => $tabela,
            'sql' => $sql,
            'erro_sql' => mysql_error());
    }

    public function verRegistroPadrao($tabela, $id = 0) {
        $pasta_imagem = end(explode('tbl_', $tabela));
        if (empty($id) || $id == 0) {
            $id = @mysql_result(mysql_query('select id from `' . $tabela . '` where ativo = "Sim" order by ordem'), 0);
        } else {
            $id = intval($id);
        }
        $sqlQry = 'SELECT * FROM `' . $tabela . '` where ativo = "Sim" and id = ' . $id . '  order by ordem';
        $qry = mysql_query($sqlQry);
        $row = @mysql_fetch_assoc($qry);
        if (!empty($row)) {
            foreach ($row as $key => $value) {
                switch ($key) {
                    case 'nome' :
                        $row['nome'] = utf8_encode($row['nome']);
                        $row['nome_url'] = $this->geraUrl($row['nome']);
                        break;
                    case 'descricao':
                        $row['resumo'] = utf8_encode($this->resumirStr($row['descricao']) . '...');
                        $row['descricao'] = utf8_encode($row['descricao']);
                        break;
                    case 'galeria' :
                        $row['galeria'] = $this->gerarGaleria($row['galeria'], $pasta_imagem);
                        if (!empty($row['galeria'])) {
                            $row['galeria_capa'] = reset($row['galeria']);
                        }
                        break;
                    case 'video_url' :
                        $row['video'] = $this->gerarVideo($row['video_url']);
                        break;
                    case 'imagem' :
                        $row['imagem_original'] = $row['imagem'];
                        $row['imagem'] = $this->gerarGaleria($row['imagem'], $pasta_imagem);
                        if (!empty($row['imagem'])) {
                            $row['imagem'] = end($row['imagem']); //Fix para não ter que ficar rodando como so fosse galeria
                        }
                        break;
                    case 'data_envio':
                        $row['data_envio'] = $this->data_agenda($row['data_envio']);
                        break;
                    default :
                        $row[$key] = utf8_encode($value);
                        break;
                }
            }
            $row['tabela'] = $tabela;
        } else {
            return; //Cai aqui quando estiver vazio
        }

        return $row;
    }

    /**
     * FUNÇÃO QUE RETORNA O verRegistroPadrao em forma de objeto
     */
    public function ver($tabela, $id = 0, $checar = false) {
        $d = $this->array_to_object($this->verRegistroPadrao($tabela, $id));
        if ($checar && $id != $d->id) {
            $this->erro_404();
            return;
        } else {
            return $d;
        }
    }

    /**
     * FUNÇÃO QUE RETORNA O retornoRegistroPadrao em forma de objeto
     */
    public function listar($tabela, $url = '', $pagina = 1, $qtd_registros = 0, $where_custom = 'where ativo = "Sim"', $campo_ordem = 'ordem', $campo_group = '') {
        return $this->array_to_object($this->retornoRegistroPadrao($tabela, $url, $pagina, $qtd_registros, $where_custom, $campo_ordem, $campo_group));
    }

    /*
     * 
     * Função padrão de inserção de registros
     * 
     */

    public function inserirRegistroPadrao($tabela, $dados = array(), $converter_utf8 = true) {
        if ($converter_utf8) {
            $dados = $this->arrayFromUtf8($dados);
        }
        if (!is_array($dados) || empty($dados)) {
            return false;
        }
        unset($dados['id']);
        $sql_campos = '`id`';
        $sql_valores = 'NULL';

        foreach ($dados as $key => $value) {
            $sql_campos .= ', `' . $key . '`';
            $sql_valores .= ", '" . $value . "'";
        }

        $sqlInsert = 'INSERT INTO `' . $tabela . '` (' . $sql_campos . ') VALUES (' . $sql_valores . ')';

        if (mysql_query($sqlInsert)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Função que retorna se um id existe em uma tabela
     */

    public function registroExiste($tabela, $id) {
        $tmp = $this->verRegistroPadrao($tabela, $id);
        return !empty($tmp);
    }

    /* Função padrão para API do Flickr------------------------------------------------------------------------------------------ */

    public function retornaGaleriaflickr() {
        $site = site_dados();
        require_once("flickr/phpFlickr.php");
        $f = new phpFlickr("KEY", "SECRET");
        $person = $f->people_findByUsername($flickr_channel);
        // Get the friendly URL of the user's photos
        $photos_url = $f->urls_getUserPhotos($person['id']);
        // Get the user's first 36 public photos
        $photos = $f->people_getPublicPhotos($person['id'], NULL, NULL, 9);
        $i = 0;
        foreach ((array) $photos['photos']['photo'] as $photo) {
            $retorno[$i]['photo'] = $photo;
            $retorno[$i]['photos_url'] = $photos_url;
            $retorno[$i]['url']['square'] = $f->buildPhotoURL($photo, "square");
            $retorno[$i]['url']['thumbnail'] = $f->buildPhotoURL($photo, "thumbnail");
            $retorno[$i]['url']['small'] = $f->buildPhotoURL($photo, "small");
            $retorno[$i]['url']['medium'] = $f->buildPhotoURL($photo, "medium");
            $retorno[$i]['url']['large'] = $f->buildPhotoURL($photo, "large");
            $retorno[$i]['url']['original'] = $f->buildPhotoURL($photo, "original");
            $i++;
        }
        return $retorno;
    }

    /* Funçoes referentes a redirecionamento------------------------------------------------------------------------------------------ */

    public function redirect($url, $interno = true) {
        //Caso parametro URL esteja em branco será redirecionado para a raíz (Home)
        if ($interno) {
            header('Location: ' . BASE_URL . $url);
        } else {
            header('Location: ' . $url);
        }
        exit;
    }

    /* Funções relacionadas a Busca padrão de registros passando apenas as tabelas para busca------------------------------------------------------------------------------------------ */

    public function busca($string = '', $tabelas = array('Nome da tabela:' => 'nometabela')) {
        $string = $this->trataStringBusca($string);
        foreach ($tabelas as $key => $value) {
            $result = $this->buscaPadrao($string, $value);
            if (!empty($result))
                $retorno[$key] = $result;
        }
//        return $retorno;
        return $this->array_to_object($retorno);
    }

    /* Funções referentes ao tratamento de strings para busca */

    public function trataStringBusca($string) {
        $string = urldecode($string);
        if ((strpos($string, "'") === TRUE) or ( strpos($string, '"') === TRUE)) {
            $string = addslashes($string);
        }
        return $string;
    }

    /* Função referente a busca */

    public function buscaPadrao($string, $tabela) {
        $pagina = $tabela;
        $sql = 'select * from tbl_' . $tabela . ' where ativo="Sim" ' . $this->geraBuscaWhere('tbl_' . $tabela, $string) . ' order by ordem DESC';
        $qry = mysql_query($sql);
        $i = 1;
        //correção das pastas
        if (!empty($area)) {
            $tabela = $area . '_produto';
        }
        while ($row = mysql_fetch_assoc($qry)) {
            $result[$i] = $this->verRegistroPadrao('tbl_' . $tabela, $row['id']);


//            foreach ($row as $key => $value) {
//                $result[$i][$key] = $value;
//            }
            $result[$i]['pagina'] = $pagina;
//            $result[$i]['id'] = $row['id'];
//            $result[$i]['nome'] = utf8_encode($row['nome']);
//            $result[$i]['descricao'] = $this->resumirStr(utf8_encode($row['descricao']), 20) . '...';
//            $result[$i]['galeria_original'] = $row['galeria'];
//            $result[$i]['imagem_original'] = $row['imagem'];
//            $result[$i]['imagem_destaque_original'] = $row['imagem_destaque'];
//
//            $result[$i]['galeria'] = $this->gerarGaleria($row['galeria'], $tabela);
//            $result[$i]['imagem'] = $this->gerarGaleria($row['imagem'], $tabela);
//            $result[$i]['imagem_destaque'] = $this->gerarGaleria($row['imagem_destaque'], $tabela);

            $i++;
        }
        return $result;
    }

    /*
     * Função que exibe os campos de uma tabela em formato de array
     */

    public function infoTabela($tabela) {
        $retorno = array();
        if (!empty($tabela)) {
            $sql = 'SHOW COLUMNS FROM  `' . $tabela . '`';
            $qry = mysql_query($sql) or die(mysql_error());
            while ($row = mysql_fetch_assoc($qry)) {
                array_push($retorno, $row);
            }
        }
        return $retorno;
    }

    /*
     * Função que trata os campos para a busca
     */

    public function geraBuscaWhere($tabela, $valor) {
        $campos = $this->infoTabela($tabela);
        foreach ($campos as $key => $value) {
            switch ($value['Type']) {
                case 'varchar(255)':
                case 'longtext':
                case 'mediumtext':
                case 'text':
                    $retorno .= (empty($retorno)) ? ' and ' : ' or ';
                    $retorno .= $value['Field'] . ' like "%' . $valor . '%" ';
                    break;
            }
        }
        return $retorno;
    }

    public function dbg($var, $tipo = 2) {
        self::debug($var, $tipo);
    }

    public static function debug($var, $tipo = 2) {
        if ($tipo == 0) {
            echo '<div id="debug_pdebug' . date('cu') . '" style="position:fixed;left:25%;z-index:9999;cursor:pointer;top:0;background:#FFF;color:#000;">
                <div id="link_debug129839">Exibir</div>
                <div id="debug_div19823982" style="display:none;width:700px;height:800px;overflow:auto;">
                <pre>';
            print_r($var);
            echo '</pre></div></div>';
            echo '<script type="text/javascript">
            $("#link_debug129839").click(function(){
                if($("#debug_div19823982").is(":visible")){
                    $("#debug_div19823982").hide();
                } else {
                    $("#debug_div19823982").show();
                }
            })  
        </script>';
        } else if ($tipo == 1) {
            echo '<pre style="background-color: black;color: white;font-size: 15px;font-family: monospace;">';
            print_r($var);
            echo '</pre>';
        } else if ($tipo == 2) {
            echo '<pre style="background-color: black;color: white;font-size: 15px;font-family: monospace;">';
            var_dump($var);
            echo '</pre>';
        }
    }

    public function replaceEmbeed($string, $largura, $altura) {
        $string = preg_replace("/height=\"[0-9]*\"/", "height='" . $altura . "'", $string);
        $string = preg_replace("/width=\"[0-9]*\"/", "width='" . $largura . "'", $string);
        return $string;
    }

    public function siteDados($campo = '') {
        $qry = mysql_query('select * from tbl_sys_config limit 1;');
        $dados = mysql_fetch_assoc($qry);
        $info = $this->ArrayToUtf8($dados);
        if (empty($campo)) {
            return $info;
        } else {
            return $info[$campo];
        }
    }

    public function arrayToUtf8($dados) {
        if (!empty($dados)) {
            foreach ((array) $dados as $key => $value) {
                if (is_array($value)) {
                    $dados[$key] = arrayToUtf8($value); //Recursividade é legal pq recursividade é legal
                } else {
                    $dados[$key] = utf8_encode($value);
                }
            }
        }
        return $dados;
    }

    public function arrayFromUtf8($dados) {
        if (!empty($dados)) {
            foreach ((array) $dados as $key => $value) {
                if (is_array($value)) {
                    $dados[$key] = arrayFromUtf8($value); //Recursividade é legal pq recursividade é legal
                } else {
                    $dados[$key] = utf8_decode($value);
                }
            }
        }
        return $dados;
    }

    public function metatags() {
        $pagina = str_replace(BASE_URL, '/', $_SERVER['REQUEST_URI']);
        $qry = mysql_query('select * from tbl_sys_seo where nome like "' . $pagina . '" or nome like "' . $pagina . '/" limit 1');
        $row = mysql_fetch_assoc($qry);

        $info = $this->arrayToUtf8($row);
        if (!empty($info)) {
            return $this->montaMetatags($info);
        } else {
            return $this->metatagsPadrao();
        }
    }

    public function metatagsPadrao() {
        $qry = mysql_query('select * from tbl_sys_seo where id = 1 limit 1');
        $d = $this->arrayToUtf8(mysql_fetch_assoc($qry));
        $d['seo_title'] .= ' - ' . $this->titulo;
        return $this->montaMetatags($d);
    }

    public function montaMetatags($d) {
        $retorno = '<title>' . $this->tituloSite;
        if (!empty($this->titulo)) {
            $retorno .= ' - ' . $this->titulo;
        }
        $retorno .= '</title>';
        $retorno .= '<meta name="Keywords" content="' . $d['seo_keywords'] . '"/>';
        $retorno .= '<meta name="Description" content="' . $d['seo_description'] . '"/>';
        $retorno .= '<meta name="Robots" content="ALL"/>';
        $retorno .= '<meta name="Robots" content="INDEX,FOLLOW"/>';
        $retorno .= '<meta name="Revisit-After" content="1 Days"/>';
        $retorno .= '<meta name="Rating" content="General"/>';

        return $retorno;
    }

    public function analytics() {
        $d = $this->siteDados();
        if (!empty($d['script_analytics'])) {
            return "<script type=\"text/javascript\">
                    var _gaq = _gaq || [];
                    _gaq.push(['_setAccount', '" . $d['script_analytics'] . "']);
                    _gaq.push(['_trackPageview']);
                    (function() {
                        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                    })();
                </script>";
        }
    }

    public function renderizar($nomeView, $variaveis = array(), $retornar = false) {
        if (!is_array($variaveis)) {
            throw new Exception("Variável ''$variaveis'' não é um array.");
        }

        $view = $this->pastaViews . $nomeView . '.php';
        if (!file_exists($view)) {
            $view = 'app/content/sys_views/' . $nomeView . '.php';
            if (!file_exists($view)) {
                die('Erro interno no componente renderizador !');
            }
        }
        $template = $this->pastaTemplates . $this->template . '.php';

        $conteudo = $this->render($view, $variaveis, true);
        $variaveis['conteudo'] = $conteudo;

        return $this->render($template, $variaveis, $retornar);
    }

    public function renderizarParcial($nomeView, $variaveis = array(), $retornar = false) {
        if (!is_array($variaveis)) {
            throw new Exception('Variável "$variaveis" não é um array.');
        }
        $view = $this->pastaViews . $nomeView . '.php';
        if (!file_exists($view)) {
            $view = $this->pastaTemplates . $nomeView . '.php';
            if (!file_exists($view)) {
                die('Erro interno no componente renderizador !');
            }
        }
        return $this->render($view, $variaveis, $retornar);
    }

    public function render($arquivoParaRenderizar, $variaveis = array(), $retornar = false) {
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

    /* Função que retorna o mês em três letras ------------------------------------------------------------------------------------------ */

    public function formatarData($data, $formato = 'd/m/Y - H:i') {
        return date_format(date_create($data), $formato);
    }

    /* Função que trata qualquer embeed ou link de vídeo e transforma em um embeed------------------------------------------------------------------------------------------ */
    /* e retorna o tipo, o thumb e o id único do vídeo------------------------------------------------------------------------------------------ */

    public function gerarVideo($video_string, $tamanho = array(540, 320)) {
        if (empty($video_string)) {
            return;
        } else {
            if ((strpos($video_string, 'youtube') == false) and ( strpos($video_string, 'youtu.be') == false)) {
                // Caso seja VIMEO
                if (preg_match('#(http://vimeo.com)/([0-9]+)#i', $video_string, $match)) {
                    $retorno['video_id'] = $match[2];
                    if (empty($retorno['video_id'])) {
                        $retorno['video_id'] = $match[1];
                    }
                    $imgid = $retorno['video_id'];
                    $hash = unserialize(@file_get_contents("http://vimeo.com/api/v2/video/$imgid.php"));
                    $retorno['thumb'] = $hash[0]['thumbnail_large'];
                    //Com autoplay
                    //$retorno['src'] = 'http://player.vimeo.com/video/' . $imgid . '?byline=0&amp;portrait=0&amp;color=ff9933&amp;autoplay=1';
                    $retorno['src'] = 'http://player.vimeo.com/video/' . $imgid . '?byline=0&amp;portrait=0&amp';
                    $retorno['src_autoplay'] = 'http://player.vimeo.com/video/' . $imgid . '?byline=0&amp;portrait=0&amp&autoplay=1';
                }
                $retorno['tipo'] = 'vimeo';
            } else {
                // Caso realmente seja youtube
                if (preg_match('#(?:<\>]+href=\")?(?:http://)?((?:[a-zA-Z]{1,4}\.)?youtube.com/(?:watch)?\?v=(.{11}?))[^"]*(?:\"[^\<\>]*>)?([^\<\>]*)(?:)?#', $video_string, $match)) {
                    $retorno['video_id'] = $match[2];
                    if (empty($retorno['video_id'])) {
                        $retorno['video_id'] = $match[1];
                    }
                    $retorno['tipo'] = 'youtube';
                    $retorno['thumb'] = 'http://img.youtube.com/vi/' . $retorno['video_id'] . '/0.jpg';
                    $retorno['src'] = 'http://www.youtube.com/embed/' . $retorno['video_id'];
                    $retorno['src_autoplay'] = 'http://www.youtube.com/embed/' . $retorno['video_id'] . '?amp&autoplay=1';
                } else if (preg_match('%(?:youtube\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_string, $match)) {
                    $retorno['video_id'] = $match[1];
                    if (empty($retorno['video_id'])) {
                        $retorno['video_id'] = $match[2];
                    }
                    $retorno['tipo'] = 'youtube';
                    $retorno['thumb'] = 'http://img.youtube.com/vi/' . $retorno['video_id'] . '/0.jpg';
                    $retorno['src'] = 'http://www.youtube.com/embed/' . $retorno['video_id'];
                    $retorno['src_autoplay'] = 'http://www.youtube.com/embed/' . $retorno['video_id'] . '?amp&autoplay=1';
                }
            }
            $retorno['matchs'] = $match;
            $retorno['embeed'] = '<iframe width="' . $tamanho[0] . '" height="' . $tamanho[1] . '" src="' . addslashes($retorno['src']) . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
            $retorno['entrada'] = $video_string;
            return $retorno;
        }
    }

    /* Função que gera a galeria de imagens de um determinado campo------------------------------------------------------------------------------------------ */

    public function gerarGaleria($data, $caminho = '', $retorno_unico = false) {
        if (isset($data)) {
            $imagens = explode(';', trim($data));
            foreach ($imagens as $key => $value) {
                if (!empty($value)) {
                    $img_cat[$key]['nome_arquivo'] = $value;
                    $img_cat[$key]['nome'] = reset(explode('.', $value));
                    $img_cat[$key]['ext'] = end(explode('.', $value));
                    if (!empty($caminho)) {
                        $img_cat[$key]['url_arquivo'] = BASE_URL . 'uploads/' . $caminho . '/' . $value;
                        $img_cat[$key]['embeed'] = '<img src="' . BASE_URL . 'uploads/' . $caminho . '/' . $value . '"';
                        $img_cat[$key]['embeed1'] = '<img src="' . BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_1.' . $img_cat[$key]['ext'] . '">';
                        $img_cat[$key]['embeed2'] = '<img src="' . BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_2.' . $img_cat[$key]['ext'] . '">';
                        $img_cat[$key]['embeed3'] = '<img src="' . BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_3.' . $img_cat[$key]['ext'] . '">';
                        $img_cat[$key]['embeed4'] = '<img src="' . BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_4.' . $img_cat[$key]['ext'] . '">';
                        $img_cat[$key]['embeed5'] = '<img src="' . BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_5.' . $img_cat[$key]['ext'] . '">';
                        $img_cat[$key]['caminho'] = BASE_URL . 'uploads/' . $caminho . '/' . $value;
                        $img_cat[$key]['caminho1'] = BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_1.' . $img_cat[$key]['ext'];
                        $img_cat[$key]['caminho2'] = BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_2.' . $img_cat[$key]['ext'];
                        $img_cat[$key]['caminho3'] = BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_3.' . $img_cat[$key]['ext'];
                        $img_cat[$key]['caminho4'] = BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_4.' . $img_cat[$key]['ext'];
                        $img_cat[$key]['caminho5'] = BASE_URL . 'uploads/' . $caminho . '/' . $img_cat[$key]['nome'] . '_5.' . $img_cat[$key]['ext'];
                    }
                }
            }

            if ($retorno_unico == true)
                return $img_cat[0];
            else
                return $img_cat;
        }
    }

    /* Função para transformar uma string em url------------------------------------ */

    public function geraUrl($input_str) {
        $input_str = $this->removeAcentos($input_str);
        $input_str = strtolower($input_str);
        $input_str = preg_replace("/[^a-z0-9_\s-]/", "", $input_str);
        $input_str = preg_replace("/[\s-]+/", " ", $input_str);
        $input_str = preg_replace("/[\s_]/", "-", $input_str);
        return $input_str;
    }

    public function stringUrlAmigavel($phrase, $maxLength = 50) {
        $result = strtolower($phrase);
        $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
        $result = trim(preg_replace("/[\s-]+/", " ", $result));
        $result = trim(substr($result, 0, $maxLength));
        $result = preg_replace("/\s/", "-", $result);
        return $result;
    }

    /* Função que retorna o e-mail para o envia_email------------------------------- */

    public function retornaEmail($tipo = 'contato') {
        $qry = mysql_query('select email_' . $tipo . ' from tbl_sys_config limit 1');
        return mysql_result($qry, 0);
    }

    /* =================== FUNÇÕES REFERENTES A VALIDAÇÃO DE USUÁRIO============================= */
    /* Funções */

    public function normaliza($string) {
        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ
ßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $b = 'aaaaaaaceeeeiiiidnoooooouuuuy
bsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
        $string = utf8_decode($string);
        $string = strtr($string, utf8_decode($a), $b);
        $string = strtolower($string);
        return utf8_encode($string);
    }

    /* ========================================================================================== */
    /* Função para resumir descrição */

    public function resumirStr($texto, $n = 20) {
        $texto = strip_tags($texto);
        $texto = trim(preg_replace("/\s+/", " ", $texto));
        $word_array = explode(" ", $texto);
        if (count($word_array) <= $n)
            return implode(" ", $word_array);
        else {
            $texto = '';
            foreach ($word_array as $length => $word) {
                $texto.=$word;
                if ($length == $n)
                    break;
                else
                    $texto.=" ";
            }
        }
        return $texto;
    }

    public function jout($msg) {
        $retorno = '<script type="text/javascript">
                    alert("';
        $retorno .= addslashes($msg);
        $retorno .='")
                </script>';
        return $retorno;
    }

    public function isPost($pagina) {
        if ($_POST) {
            $url_referer = parse_url($_SERVER[HTTP_REFERER]);
            $url_referer = explode('/', $url_referer[path]);
            $url_referer = end($url_referer);
            return ($pagina == $url_referer);
        } else {
            return false;
        }
    }

    public function array_to_object($array) {
        if (!empty($array)) {
            $obj = new stdClass;
            foreach ((array) $array as $k => $v) {
                if (is_array($v)) {
                    $obj->{$k} = $this->array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
            return $obj;
        }
    }

    /*
     * Função para remover acentos de uma string
     */

    public function removeAcentos($var) {
        return $this->normaliza($var);
    }

    public function listarErros($erros = '', $classes = array('div' => 'erroContainer clearfix', 'ul' => 'flashes', 'li' => 'flash-erro')) {
        if (!empty($erros)) {
            $retorno .= '<div class="' . $classes['div'] . '">';
            $retorno .= '<ul class="' . $classes['ul'] . '">';
            foreach ($erros as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $campo => $msg) {
                        $retorno .= '<li class="' . $classes['li'] . '">' . $campo . ' - ' . $msg . '</li>';
                    }
                } else {
                    $retorno .= '<li class="' . $classes['li'] . '">' . $key . ' - ' . $value . '</li>';
                }
            }
            $retorno .= '</ul>';
            $retorno .= '</div>';
            return $retorno;
        }
    }

    public function isValidMd5($md5) {
        return !empty($md5) && preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    public function salvarNewsletter($email, $nome = '') {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $dados = $this->retornoRegistroPadrao('tbl_sys_newsletter', '', 1, 0, ' where email like "' . $email . '"', 'id');
            if (empty($dados['registros'])) {
                if (mysql_query("INSERT INTO  `tbl_sys_newsletter` (`id` ,`nome` ,`email`) VALUES (NULL ,  '" . utf8_decode($nome) . "',  '" . $email . "');")) {
                    $this->adicionarMensagemSucesso('Email cadastrado com sucesso !');
                    return true;
                } else {
                    $this->adicionarMensagemErro('Ocorreu um erro e seu email não foi cadastrado em nosso banco de dados');
                    return false;
                }
            } else {
                $this->adicionarMensagem('Email já cadastrado em nosso banco de dados', 2);
                return false;
            }
        } else {
            $this->adicionarMensagemErro('Email inválido');
            return false;
        }
    }

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

    public function html_sysImg($idImagem, $resource, $idTamanho = '1', $opcoesHtml = array()) {
        $_opcoes = '';
        if (!empty($opcoesHtml) && is_array($opcoesHtml)) {
            foreach ($opcoesHtml as $key => $value) {
                $_opcoes .= $key . '="' . $value . '"';
            }
        }
        return '<img src="' . $resource[$idImagem]['caminho' . $idTamanho] . '" ' . $_opcoes . ' >';
    }

    public function html_img($srcImg, $alt = '', $opcoesHtml = array()) {
        $_opcoes = '';
        if (!empty($opcoesHtml) && is_array($opcoesHtml)) {
            foreach ($opcoesHtml as $key => $value) {
                $_opcoes .= $key . '="' . $value . '"';
            }
        }
        return '<img src="' . $srcImg . '" alt="' . $alt . '" ' . $_opcoes . ' />';
    }

    public function html_link($conteudo, $href, $opcoesHtml = array()) {
        $_opcoes = '';
        if (!empty($opcoesHtml) && is_array($opcoesHtml)) {
            foreach ($opcoesHtml as $key => $value) {
                $_opcoes .= $key . '="' . $value . '"';
            }
        }
        if (!is_array($href) && (strpos($href, '.') === false)) {
            $href = explode('/', $href);
        }
        $href = $this->url($href);

        return '<a href="' . $href . '" ' . $_opcoes . ' >' . $conteudo . '</a>';
    }

    public function html_trataUrl($url) {
        if (empty($url)) {
            return '#';
        }
        $http = 'http://';
        $https = 'https://';
        if (strpos($url, $http) === false && strpos($url, $https) === false) {
            $url = str_replace('http://', '', $url);
            return 'http://' . $url;
        } else {
            return $url;
        }
    }

    public function html_script($arquivo, $arquivoExterno = false) {
        $url = ($arquivoExterno == true) ? $arquivo : $this->url($arquivo);
        return '<script src="' . $url . '"></script>';
    }

    public function html_css($arquivo, $arquivoExterno = false) {
        $url = ($arquivoExterno == true) ? $arquivo : $this->url($arquivo);
        return '<link rel="stylesheet" src="' . $url . '">';
    }

    /*
     * 
     * SEÇÃO MESTRE - > MÉTODOS ESTÁTICOS AUXILIARES DO PAGSEGURO
     * 
     */

    public static function pagSeguro($campo) {
        $qry = mysql_query('select ' . $campo . '_pagseguro from tbl_sys_config limit 1;');
        $dados = mysql_fetch_assoc($qry);
        return $dados[$campo . '_pagseguro'];
    }

    /*
     * Erros
     */

    public function erro_404() {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        header("Status: 404 Not Found");
        $_SERVER['REDIRECT_STATUS'] = 404;
        $this->renderizar('nao_existe');
    }

    /*
     * Upload de fotos
     */

    public function uploadImagem($arquivo, $larguraMax, $alturaMax, $destino, $nome_destino) {
        //----------------------------------------------------------------
        // Crop-to-fit PHP-GD
        // Revision 2 [2009-06-]
        // Corrected aspect ratio of the output image
        //----------------------------------------------------------------

        $source_path = $arquivo['tmp_name'];


        list( $source_width, $source_height, $source_type ) = getimagesize($source_path);

        switch ($source_type) {
            case IMAGETYPE_GIF:
                $source_gdim = imagecreatefromgif($source_path);
                break;

            case IMAGETYPE_JPEG:
                $source_gdim = imagecreatefromjpeg($source_path);
                break;

            case IMAGETYPE_PNG:
                $source_gdim = imagecreatefrompng($source_path);
                break;
        }

        $source_gdim = $this->imagetranstowhite($source_gdim);

        $source_aspect_ratio = $source_width / $source_height;
        $desired_aspect_ratio = $larguraMax / $alturaMax;

        if ($source_aspect_ratio > $desired_aspect_ratio) {
            $temp_height = $alturaMax;
            $temp_width = (int) ( $alturaMax * $source_aspect_ratio );
        } else {
            $temp_width = $larguraMax;
            $temp_height = (int) ( $larguraMax / $source_aspect_ratio );
        }

        //
        // Resize the image into a temporary GD image
        //

  $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
        imagecopyresampled(
                $temp_gdim, $source_gdim, 0, 0, 0, 0, $temp_width, $temp_height, $source_width, $source_height
        );

        //
        // Copy cropped region from temporary image into the desired GD image
        //

    $x0 = ( $temp_width - $larguraMax ) / 2;
        $y0 = ( $temp_height - $alturaMax ) / 2;

        $desired_gdim = imagecreatetruecolor($larguraMax, $alturaMax);
        imagecopy(
                $desired_gdim, $temp_gdim, 0, 0, $x0, $y0, $larguraMax, $alturaMax
        );

        //
        // Render the image
        // Alternatively, you can save the image in file-system or database
        //

    //header('Content-type: image/jpeg');
        //imagejpeg($desired_gdim);

        imagejpeg($desired_gdim, $destino . $nome_destino, 100);

        //
        // Add clean-up code here
//
    }

    public function imagetranstowhite($trans) {
        // Create a new true color image with the same size
        $w = imagesx($trans);
        $h = imagesy($trans);
        $white = imagecreatetruecolor($w, $h);

        // Fill the new image with white background
        $bg = imagecolorallocate($white, 255, 255, 255);
        imagefill($white, 0, 0, $bg);

        // Copy original transparent image onto the new image
        imagecopy($white, $trans, 0, 0, 0, 0, $w, $h);
        return $white;
    }

    public function encodeSEOString($string) {
        $string = preg_replace("`\[.*\]`U", "", $string);
        $string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $string);
        $string = preg_replace(array("`[^a-z0-9]`i", "`[-]+`"), "-", $string);
        return strtolower(trim($string, '-'));
    }

    public function decodeSEOString($string) {
        return str_replace('-', ' ', $string);
    }

}
