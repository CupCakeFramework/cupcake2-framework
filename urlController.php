<?php

/* Inicialização URL amigável */
$inputs = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
if ($_GET['a']) {
    $get = explode("/", $_GET['a']);
    foreach ($get as $key => $value) {
        if (!empty($value))
            $_GET[$inputs[$key]] = $value;
    }
}
/* Inicialização - Evitar sql inject */
foreach ($_POST as $key => $input_arr) {
    if (!is_array($_POST[$key])) {
        $_POST[$key] = addslashes($input_arr);
    }
}
foreach ($_GET as $key => $input_arr) {
    $_GET[$key] = addslashes($input_arr);
}
?>
