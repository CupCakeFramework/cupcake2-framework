<?php

namespace Config\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use CupCake2\Models\Seo;
use CupCake2\Core\CupSeo;

class loadSeoData implements FixtureInterface {

    public function load(ObjectManager $manager) {
        $seo = new Seo();
        $seo->setUrl(CupSeo::UrlMetaTagPadrao);
        $seo->setDescription('Description do projeto');
        $seo->setKeywords('keywords do projeto');
        $seo->setAtivo(true);
        $manager->persist($seo);
        $manager->flush();
    }

}
