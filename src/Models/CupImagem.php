<?php

namespace App\Models;

/**
 * @Entity @Table(name="cup_galeria_imagem")
 * */
class CupImagem {

    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @ManyToOne(targetEntity="CupGaleria") */
    private $galeria;

    /**
     * @return CupGaleria
     */
    public function getGaleria() {
        return $this->galeria;
    }

    public function setGaleria($galeria) {
        $this->galeria = $galeria;
    }

}
