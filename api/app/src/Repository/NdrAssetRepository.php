<?php

namespace App\Repository;

use App\Entity\Ndr\Asset;
use App\Entity\Ndr\AssetOther;
use App\Entity\Ndr\AssetProperty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NdrAssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    public function getSumOfAssets(
        string $assetType = AssetOther::class,
        ?\DateTime $after = null,
        array $excludeByClientId = []
    ): int {
        if (!in_array($assetType, [AssetProperty::class, AssetOther::class])) {
            throw new \InvalidArgumentException('Only "AssetProperty" or "AssetOther" assets are supported');
        }

        $selectQuery = AssetOther::class === $assetType ? 'SUM(a.value)' : 'SUM(a.value * (a.ownedPercentage / 100))';

        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select($selectQuery)
            ->from($assetType, 'a')
            ->leftJoin('a.ndr', 'ndr');

        if ($after) {
            $query
                ->andWhere('ndr.submitDate > :after')
                ->setParameter('after', $after);
        }

        if (!empty($excludeByClientId)) {
            $query
                ->leftJoin('ndr.client', 'c')
                ->andWhere('c.id NOT IN (:clientIds)')
                ->setParameter('clientIds', $excludeByClientId);
        }

        return intval($query->getQuery()->getSingleScalarResult());
    }
}
