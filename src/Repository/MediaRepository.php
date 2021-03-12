<?php

namespace Djvue\DMediaBundle\Repository;

use Djvue\DMediaBundle\DTO\MediaGetListParametersDTO;
use Djvue\DMediaBundle\Entity\EntityHasMedia;
use Djvue\DMediaBundle\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    private function getParametersQuery(MediaGetListParametersDTO $dto): QueryBuilder
    {
        $query = $this->createQueryBuilder('m');
        $query->andWhere('m.type = :type')->setParameter('type', $dto->getType());
        if ('' !== $dto->getSearch()) {
            $query
                ->andWhere('m.name LIKE :search OR m.altText LIKE :search OR m.caption LIKE :search')
                ->setParameter('search', '%'.$dto->getSearch().'%')
            ;
        }
        $i = 0;
        foreach ($dto->getEntities() as $entityType => $entityIds) {
            if (!is_array($entityIds) || count($entityIds) === 0) {
                continue;
            }
            $entityTypeParam = 'entityType'.$i;
            $entityIdsParam = 'entityIds'.$i;
            $query
                ->innerJoin(EntityHasMedia::class, 'ehm', Expr\Join::WITH, $query->expr()->andX(
                    $query->expr()->eq('ehm.media', 'm'),
                    $query->expr()->eq('ehm.entityType', ':'.$entityTypeParam),
                    $query->expr()->in('ehm.entityId', ':'.$entityIdsParam)
                ))
                ->setParameter($entityTypeParam, $entityType)
                ->setParameter($entityIdsParam, $entityIds)
            ;
            $i++;
        }
        $query->orderBy('m.id', 'DESC');
        return $query;
    }

    /**
     * @param MediaGetListParametersDTO $dto
     * @return Media[]
     */
    public function findByParameters(MediaGetListParametersDTO $dto): array
    {
        $query = $this->getParametersQuery($dto);
        $offset = ($dto->getPage() - 1) * $dto->getLimit();
        $query->setFirstResult($offset);
        if (null !== $dto->getLimit()) {
            $query->setMaxResults($dto->getLimit());
        }
        /**
         * @var Media[] $items
         */
        $items = $query->getQuery()->getResult();

        return $items;
    }

    public function getCountByParameters(MediaGetListParametersDTO $dto): int
    {
        $query = $this->getParametersQuery($dto);
        $paginator = new Paginator($query);
        return $paginator->count();
    }
}
