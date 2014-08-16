<?php

namespace CupCake2\Core;

class CupUtils {

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

    /* Função que retorna o mês em três letras ------------------------------------------------------------------------------------------ */

    public function formatarData($data, $formato = 'd/m/Y - H:i') {
        return date_format(date_create($data), $formato);
    }

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

    public function removeAcentos($var) {
        return $this->normaliza($var);
    }

    public function isValidMd5($md5) {
        return !empty($md5) && preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    public function checkHttpFromUrl($url) {
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

}
