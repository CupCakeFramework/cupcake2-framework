<?php

namespace App\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use App\Models\CupSiteConfig;

class loadCupSiteConfigData implements FixtureInterface {

    public function load(ObjectManager $manager) {
        $siteConfig = new CupSiteConfig();
        $siteConfig->setEmail_contato('email.de.contato@gmail.com');
        $siteConfig->setEndereco('endereco');
        $manager->persist($siteConfig);
        $manager->flush();
    }

}
