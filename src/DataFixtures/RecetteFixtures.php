<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Recette;
use App\Entity\Tag;
use App\Entity\Comment;

class RecetteFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        
        $ing = '<p>' . join($faker->paragraphs(1), '</p><p>') . '</p>';
        $step = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';

        for($i = 1; $i <= 10; $i++){
            $recette = new Recette();
            $recette->setTitle($faker->sentence())
                    ->setIngredient($ing)
                    ->setStep($step)
                    ->setImage("https://www.fillmurray.com/640/360")
                    ->setCreatedAt($faker->dateTimeBetween('-6 months'));

            $manager->persist($recette);        
        }

        $manager->flush();
    }
}
