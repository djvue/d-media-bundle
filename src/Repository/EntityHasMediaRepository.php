<?php

namespace Djvue\DMediaBundle\Repository;

use Djvue\DMediaBundle\Entity\EntityHasMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntityHasMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityHasMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityHasMedia[]    findAll()
 * @method EntityHasMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityHasMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityHasMedia::class);
    }

    public function findByEntityWithMedias(string $entityType, int $entityId): array
    {
        $result = $this
            ->createQueryBuilder('mp')
            ->andWhere('mp.entityType = :entityType')
            ->setParameter('entityType', $entityType)
            ->andWhere('mp.entityId = :entityId')
            ->setParameter('entityId', $entityId)
            ->join('mp.media', 'm')
            ->select('mp, m')
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    public function findByEntityAndPropertyNameWithMedias(
        string $entityType,
        int $entityId,
        string $propertyName
    ): array {
        $result = $this->createQueryBuilder('mp')
            ->andWhere('mp.entityType = :entityType')
            ->setParameter('entityType', $entityType)
            ->andWhere('mp.entityId = :entityId')
            ->setParameter('entityId', $entityId)
            ->andWhere('mp.propertyName = :propertyName')
            ->setParameter('propertyName', $propertyName)
            ->join('mp.media', 'm')
            ->select('mp, m')
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }
}
