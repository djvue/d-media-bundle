<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

/**
 * @internal
 * @group init
 */
abstract class BaseFixture extends Fixture
{
    protected ObjectManager $manager;
    protected Generator $faker;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
        $this->loadData($manager);
    }

    abstract protected function loadData(ObjectManager $manager): void;
}
