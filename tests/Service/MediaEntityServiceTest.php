<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Tests\Service;

use Djvue\DMediaBundle\DataFixtures\EntityHasMediaFixtures;
use Djvue\DMediaBundle\DataFixtures\MediaFixtures;
use Djvue\DMediaBundle\Entity\EntityHasMedia;
use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Repository\EntityHasMediaRepository;
use Djvue\DMediaBundle\Service\MediaEntityService;
use Djvue\DMediaBundle\Tests\BaseWebTestCase;

/**
 * @internal
 * @covers \Djvue\DMediaBundle\Service\MediaEntityService
 * @group init
 */
final class MediaEntityServiceTest extends BaseWebTestCase
{
    protected array $fixtures = [
        MediaFixtures::class,
        EntityHasMediaFixtures::class,
    ];

    public function testGetEntityType(): void
    {
        $entityType = self::$container->get(MediaEntityService::class)->getEntityType(Media::class);
        self::assertEquals('media', $entityType);
    }

    public function testSyncEntityMedias(): void
    {
        $this->createEntityHasMedia(1);
        $this->createEntityHasMedia(2);
        $this->entityManager->flush();

        $medias = [
            ['id' => 2],
            $this->entityManager->getReference(Media::class, 3),
        ];

        self::$container->get(MediaEntityService::class)
            ->syncEntityMedias('workspace', 10, 'mainImage', $medias);

        $entityHasMedias = self::$container->get(EntityHasMediaRepository::class)->findBy([
            'entityType' => 'workspace',
            'entityId' => 10,
            'propertyName' => 'mainImage',
        ]);
        usort($entityHasMedias, static fn($a, $b) => $a->getId() <=> $b->getId());

        self::assertCount(2, $entityHasMedias);
        self::assertEquals(2, $entityHasMedias[0]->getMedia()->getId());
        self::assertEquals(3, $entityHasMedias[1]->getMedia()->getId());
    }

    private function createEntityHasMedia(
        int $mediaId,
        string $entityType = 'workspace',
        int $entityId = 10,
        string $propertyName = 'mainImage',
        int $listOrder = 0,
    ): EntityHasMedia {
        $media = $this->entityManager->getReference(Media::class, $mediaId);
        $entityHasMedia = new EntityHasMedia();
        $entityHasMedia->setMedia($media);
        $entityHasMedia->setEntityType($entityType);
        $entityHasMedia->setEntityId($entityId);
        $entityHasMedia->setPropertyName($propertyName);
        $entityHasMedia->setListOrder($listOrder);
        $this->entityManager->persist($entityHasMedia);
        return $entityHasMedia;
    }
}
