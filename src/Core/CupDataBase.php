<?php

namespace CupCake2\Core;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use CupCake2\Core\CupConfigManager;

class CupDataBase {

    /**
     * EntityManager do Projeto 
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;

    /**
     *
     * @var CupConfigManager 
     */
    private $configManager;
    
    public function __construct(CupConfigManager $config, $isDevMode = false) {
        $this->configManager = $config;
        $dbParams = $this->configManager->getEnvironmentConfigFromKey('dbParams');
        $paths = $this->getEntityPaths();
        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        $this->entityManager = EntityManager::create($dbParams, $config);
    }
    
    public function getEntityPaths(){
        return $this->configManager->getConfigFromKey('models_dir');
    }

    public function buscarUmPorId($entidade, $id) {
        return $this->getEntityManager()->find($entidade, $id);
    }

    public function buscarUmPorCriteria($entity, array $criteria, array $orderBy = null) {
        return $this->getEntityManager()->getRepository($entity)->findOneBy($criteria, $orderBy);
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
