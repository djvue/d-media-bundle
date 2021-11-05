<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Service;

use Djvue\DMediaBundle\Entity\EntityHasMedia;
use Djvue\DMediaBundle\Entity\Media;
use Doctrine\ORM\ORMException;

interface MediaEntityServiceInterface
{
    /**
     * @param class-string $entityClass
     */
    public function getEntityType(string $entityClass): string;

    /**
     * @param Media[]|array{id: int}[] $medias
     * @throws ORMException
     */
    public function syncEntityMedias(string $entityType, int $entityId, string $propertyName, array $medias): void;

    /**
     * @throws ORMException
     */
    public function addMediaToEntity(
        string $entityType,
        int $entityId,
        string $propertyName,
        int $mediaId,
        int $listOrder = 0
    ): void;

    public function cloneMedias(string $entityType, int $entityId, int $newEntityId): void;

    /**
     * @return array<string, Media[]>
     */
    public function getMediasByPropertyArray(string $entityType, int $entityId): array;

    public function getOneMedia(string $entityType, int $entityId, string $propertyName = ''): ?Media;

    /**
     * @return Media[]
     */
    public function getMedias(string $entityType, int $entityId, string $propertyName = ''): array;

    /**
     * @return array<string, int[]>
     */
    public function getEntities(Media $media): array;

    /**
     * @return EntityHasMedia[]
     */
    public function getEntitiesOfType(Media $media, string $entityType): array;

    /**
     * @param int[] $entityIds
     */
    public function syncMediaEntities(Media $media, string $entityType, array $entityIds, string $propertyName = ''): void;
}
