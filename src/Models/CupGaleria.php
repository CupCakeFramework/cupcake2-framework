<?php

namespace App\Models;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="cup_galeria")
 * */
class CupGaleria {

    /** @Id @Column(type="integer") @GeneratedValue * */
    protected $id;

    /** @OneToMany(targetEntity="CupImagem", mappedBy="galeria") */
    private $imagens;

    public function __construct() {
        $this->imagens = new ArrayCollection();
    }

    public function getImagens() {
        return $this->imagens;
    }

    public function setImagens($imagens) {
        $this->imagens = $imagens;
    }

}
