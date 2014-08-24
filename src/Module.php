<?php

namespace CupCake2;

class Module {

    public function getConfig() {
        return include(__DIR__ . '/config/main.php');
    }

}
