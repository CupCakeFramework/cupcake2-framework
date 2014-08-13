<?php

namespace CupCake2\Core;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class CupORM {

    /**
     * EntityManager do Projeto 
     * @var EntityManager 
     */
    public $entityManager;

    public function __construct(array $dbParams, $isDevMode = false) {
        $paths = array("/App/Models");
        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        $this->entityManager = EntityManager::create($dbParams, $config);
    }

}
