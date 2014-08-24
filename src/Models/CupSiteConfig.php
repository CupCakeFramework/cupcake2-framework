<?php

namespace App\Models;

/**
 * @Entity @Table(name="cup_config")
 * */
class CupSiteConfig {

    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="text") * */
    protected $endereco;

    /** @Column(type="string") * */
    protected $email_contato;

    public function getId() {
        return $this->id;
    }

    public function getEndereco() {
        return $this->endereco;
    }

    public function getEmail_contato() {
        return $this->email_contato;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    public function setEmail_contato($email_contato) {
        $this->email_contato = $email_contato;
    }

}
