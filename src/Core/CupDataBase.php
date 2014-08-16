<?php

namespace CupCake2\Core;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class CupDataBase {

    /**
     * EntityManager do Projeto 
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;

    public function __construct(array $dbParams, $isDevMode = false) {
        $paths = array(
            "/app/models",
            "/cupcake2/models",
        );
        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        $this->entityManager = EntityManager::create($dbParams, $config);
    }

    public function buscarUm($entidade, $id) {
        return $this->getEntityManager()->find($entidade, $id);
    }

    public function buscarTodos($entidade) {
        return $this->getEntityManager()->getRepository($entidade)->findAll();
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->entityManager;
    }

}
