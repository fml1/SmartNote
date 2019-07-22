<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Category;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $cat = new Category();
        $cat->setName('Категория первая');
        $cat->setNote('Описание первой категории');

        $manager->persist($cat);

        $manager->flush();
    }
}
