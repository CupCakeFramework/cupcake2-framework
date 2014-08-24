<?php

namespace App\Models;

/**
 * @Entity @Table(name="cup_admin")
 * */
class UsuarioAdmin {

    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="string") * */
    protected $nome;

    /** @Column(type="string") * */
    protected $email;

    /** @Column(type="string") * */
    protected $login;

    /** @Column(type="string") * */
    protected $senha;

    /** @Column(type="boolean") * */
    protected $ativo;

    public function getNome() {
        return $this->nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getLogin() {
        return $this->login;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function setSenha($senha) {
        $this->senha = password_hash($senha, PASSWORD_DEFAULT);
    }

    public function getAtivo() {
        return $this->ativo;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }

}
