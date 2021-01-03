<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Graph;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GraphFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $graph = new Graph();
        $graph->setUser($this->getReference('user'));
        $graph->setName('Demo Graph');

        $manager->persist($graph);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class];
    }
}
