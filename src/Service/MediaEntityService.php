<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Service;

use Djvue\DMediaBundle\Entity\EntityHasMedia;
use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Repository\EntityHasMediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

class MediaEntityService implements MediaEntityServiceInterface
{
    public function __construct(
        private array $filterableEntities,
        private EntityManagerInterface $entityManager,
        private EntityHasMediaRepository $entityHasMediaRepository,
    ) {
    }

    /**
     * @param class-string $entityClass
     * @return string
     */
    public function getEntityType(string $entityClass): string
    {
        return $this->entityManager->getClassMetadata($entityClass)->getTableName();
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @param string $propertyName
     * @param Media[]|array{id: int}[] $medias
     * @throws ORMException
     */
    public function syncEntityMedias(string $entityType, int $entityId, string $propertyName, array $medias): void
    {
        $ids = array_filter(
            array_map(static fn($media) => is_array($media) ? (int)$media['id'] : $media->getId(), $medias)
        );
        $mediaProjectRelations = $this->entityHasMediaRepository->findByEntityAndPropertyNameWithMedias(
            $entityType,
            $entityId,
            $propertyName
        );
        $relationIdMap = [];
        foreach ($mediaProjectRelations as $relation) {
            /** @var int $relationId */
            $relationId = $relation->getMedia()->getId();
            if (!in_array($relationId, $ids, true)) {
                $this->entityManager->remove($relation);
            }
            $relationIdMap[$relationId] = $relation;
        }
        foreach ($ids as $index => $id) {
            if (!isset($relationIdMap[$id])) {
                $this->addMediaToEntity($entityType, $entityId, $propertyName, $id, $index);
                continue;
            }
            $mediaProject = $relationIdMap[$id];
            $mediaProject->setListOrder($index);
            $this->entityManager->persist($mediaProject);
        }
        $this->entityManager->flush();
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @param string $propertyName
     * @param int $mediaId
     * @param int $listOrder
     * @throws ORMException
     */
    public function addMediaToEntity(
        string $entityType,
        int $entityId,
        string $propertyName,
        int $mediaId,
        int $listOrder = 0
    ): void {
        $entityHasMedia = new EntityHasMedia();
        $entityHasMedia->setEntityType($entityType);
        $entityHasMedia->setEntityId($entityId);
        /** @var Media $media */
        $media = $this->entityManager->getReference(Media::class, $mediaId);
        $entityHasMedia->setMedia($media);
        $entityHasMedia->setPropertyName($propertyName);
        $entityHasMedia->setListOrder($listOrder);
        $this->entityManager->persist($entityHasMedia);
    }

    public function cloneMedias(string $entityType, int $entityId, int $newEntityId): void
    {
        $mediaProjectRelations = $this->entityHasMediaRepository->findBy(
            [
                'entityType' => $entityType,
                'entityId' => $entityId,
            ]
        );
        foreach ($mediaProjectRelations as $relation) {
            $newRelation = clone $relation;
            $newRelation->setEntityId($newEntityId);
            $this->entityManager->persist($newRelation);
        }
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @return array<string, Media[]>
     */
    public function getMediasByPropertyArray(string $entityType, int $entityId): array
    {
        /** @var EntityHasMedia[] $mediaEntities */
        $mediaEntities = $this->entityHasMediaRepository->findByEntityWithMedias($entityType, $entityId);
        $mediasByType = [];
        foreach ($mediaEntities as $mediaEntity) {
            $property = $mediaEntity->getPropertyName();
            $mediasByType[$property] ??= [];
            $mediasByType[$property][$mediaEntity->getListOrder()] = $mediaEntity->getMedia();
        }
        foreach ($mediasByType as &$medias) {
            ksort($medias);
            $medias = array_values($medias);
        }

        return $mediasByType;
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @param string $propertyName
     * @return Media|null
     */
    public function getOneMedia(string $entityType, int $entityId, string $propertyName = ''): ?Media
    {
        /** @var ?EntityHasMedia $entityHasMedia */
        $entityHasMedia = $this->entityHasMediaRepository
            ->findOneBy(
                [
                    'entityType' => $entityType,
                    'entityId' => $entityId,
                    'propertyName' => $propertyName,
                ]
            );

        return $entityHasMedia?->getMedia();
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @param string $propertyName
     * @return Media[]
     */
    public function getMedias(string $entityType, int $entityId, string $propertyName = ''): array
    {
        // TODO: join media with entity_has_media for optimization (N + 1 requests to db -> 1 request to db)
        $entityHasMedias = $this->entityHasMediaRepository
            ->findBy(
                [
                    'entityType' => $entityType,
                    'entityId' => $entityId,
                    'propertyName' => $propertyName,
                ]
            );

        return array_map(
            static fn (EntityHasMedia $entityHasMedia) => $entityHasMedia->getMedia(),
            $entityHasMedias
        );
    }

    /**
     * @param Media $media
     * @return array<string, int[]>
     */
    public function getEntities(Media $media): array
    {
        $criteria = [
            'media' => $media,
        ];
        $filterableEntities = $this->filterableEntities;
        if ($filterableEntities !== []) {
            $criteria['entityType'] = $filterableEntities;
        }
        $entityHasMedias = $this->entityHasMediaRepository->findBy($criteria);
        $groups = [];
        foreach ($entityHasMedias as $entityHasMedia) {
            $groups[$entityHasMedia->getEntityType()] ??= [];
            $groups[$entityHasMedia->getEntityType()][] = $entityHasMedia->getEntityId();
        }
        return $groups;
    }

    /**
     * @param Media $media
     * @param string $entityType
     * @return EntityHasMedia[]
     */
    public function getEntitiesOfType(Media $media, string $entityType): array
    {
        $criteria = [
            'media' => $media,
            'entityType' => $entityType,
        ];

        return $this->entityHasMediaRepository->findBy($criteria);
    }

    /**
     * @param Media $media
     * @param string $entityType
     * @param int[] $entityIds
     * @param string $propertyName
     */
    public function syncMediaEntities(Media $media, string $entityType, array $entityIds, string $propertyName = ''): void
    {
        $entityHasMedias = $this->entityHasMediaRepository->findBy([
            'media' => $media,
            'entityType' => $entityType,
            'propertyName' => $propertyName,
        ]);
        $hasIds = array_map(static fn($el) => $el->getEntityId(), $entityHasMedias);
        foreach ($hasIds as $index => $hasId) {
            if (!in_array($hasId, $entityIds, true)) {
                $this->entityManager->remove($entityHasMedias[$index]);
            }
        }
        foreach ($entityIds as $index => $entityId) {
            if (!in_array($entityId, $hasIds, true)) {
                $entityHasMedia = new EntityHasMedia();
                $entityHasMedia->setMedia($media);
                $entityHasMedia->setListOrder($index);
                $entityHasMedia->setPropertyName($propertyName);
                $entityHasMedia->setEntityType($entityType);
                $entityHasMedia->setEntityId($entityId);
                $this->entityManager->persist($entityHasMedia);
            }
        }
    }
}
