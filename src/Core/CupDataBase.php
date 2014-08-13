<?php

namespace CupCake2\Core;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class CupDataBase {

    /**
     * EntityManager do Projeto 
     * @var Doctrine\ORM\EntityManager 
     */
    public $entityManager;

    public function __construct(array $dbParams, $isDevMode = false) {
        $paths = array(
            "/App/Models",
            "/Cupcake2/Models",
        );
        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        $this->entityManager = EntityManager::create($dbParams, $config);
    }

    public function buscarUm($entidade, $id) {
        return $this->entityManager->find($entidade, $id);
    }

    public function buscarTodos($entidade) {
        return $this->entityManager->getRepository($entidade)->findAll();
    }

}
