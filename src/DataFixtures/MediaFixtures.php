<?php

namespace Djvue\DMediaBundle\DataFixtures;

use Djvue\DMediaBundle\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @internal
 * @group init
 */
class MediaFixtures extends BaseFixture
{
    public const FIRST_MEDIA_REFERENCE = 'first_media';

    public function __construct(
        private EntityManagerInterface $manager,
    ) {
    }

    public function loadData(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $media = new Media();
            $media->setName($this->faker->unique()->name);
            $media->setPath($this->faker->unique()->slug);
            $media->setType(Media::TYPE_IMAGES);
            $manager->persist($media);
            if ($i === 0) {
                $this->addReference(self::FIRST_MEDIA_REFERENCE, $media);
            }
        }
        for ($i = 0; $i < 1; ++$i) {
            $media = new Media();
            $media->setName($this->faker->unique()->name);
            $media->setPath($this->faker->unique()->slug);
            $media->setType(Media::TYPE_FILES);
            $manager->persist($media);
        }

        $manager->flush();
    }
}
