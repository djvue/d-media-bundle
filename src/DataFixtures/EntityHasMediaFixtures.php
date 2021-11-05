<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\DataFixtures;

use Djvue\DMediaBundle\Entity\EntityHasMedia;
use Djvue\DMediaBundle\Entity\Media;
use Doctrine\Persistence\ObjectManager;

/**
 * @internal
 * @group init
 */
class EntityHasMediaFixtures extends BaseFixture
{
    public function loadData(ObjectManager $manager): void
    {
        /** @var Media $media */
        $media = $this->getReference(MediaFixtures::FIRST_MEDIA_REFERENCE);
        $entityHasMedia = new EntityHasMedia();
        $entityHasMedia->setMedia($media);
        $entityHasMedia->setEntityType('workspace');
        $entityHasMedia->setEntityId(2);
        $entityHasMedia->setListOrder(0);
        $entityHasMedia->setPropertyName('mainImage');
        $manager->persist($entityHasMedia);

        $manager->flush();
    }
}
