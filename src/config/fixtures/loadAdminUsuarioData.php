<?php

namespace App\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use App\Models\UsuarioAdmin;

class loadAdminUsuarioData implements FixtureInterface {

    public function load(ObjectManager $manager) {
        $usuario = new UsuarioAdmin();
        $usuario->setNome('Admin Master');
        $usuario->setEmail('admin@cupcakeorg.com');
        $usuario->setLogin('admin');
        $usuario->setSenha('123456');
        $usuario->setAtivo(true);
        $manager->persist($usuario);
        $manager->flush();
    }

}
