<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Masao;
use App\Entity\Om;
use Symfony\Component\Validator\Constraints\DateTime;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for($i=1 ; $i<6 ; $i++){
            $masao = new Masao();
            $masao -> setDateCreation( new \DateTime());
            $masao -> setRef($i);
            $masao -> setDateSoumission("22/04/2020");
            $masao -> setMiseAJour("25/10/2020");

            $manager -> persist($masao);
            $manager->flush();
        }

        for($i=3; $i<8 ; $i++){
            $masao = new OM();
            $masao -> setDateCreation( new \DateTime());
            $masao -> setRef($i);
            $masao -> setDateSoumission("22/04/2020");
            $masao -> setMiseAJour("25/10/2020");

            $manager -> persist($masao);
            $manager->flush();
        }

        
    }
}
