<?php

namespace CupCake2\Models;

/**
 * @Entity @Table(name="cup_seo")
 * */
class Seo {

    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @Column(type="string") * */
    protected $url;

    /** @Column(type="string") * */
    protected $keywords;

    /** @Column(type="string") * */
    protected $description;

    /** @Column(type="boolean") * */
    protected $ativo;

    public function getId() {
        return $this->id;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getKeywords() {
        return $this->keywords;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getAtivo() {
        return $this->ativo;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setKeywords($keywords) {
        $this->keywords = $keywords;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }

}
