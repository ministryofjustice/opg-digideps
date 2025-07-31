<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report\Asset;
use App\Entity\Report\AssetOther;
use App\Entity\Report\AssetProperty;
use App\Entity\Report\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    public function getSumOfAssets(
        string $assetType = AssetOther::class,
        ?string $deputyType = null,
        ?\DateTime $after = null
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
            ->leftJoin('a.report', 'r');

        if ($after) {
            $query
                ->andWhere('r.submitDate > :after')
                ->setParameter('after', $after);
        }

        if ($deputyType) {
            $types = match (strtoupper($deputyType)) {
                'LAY' => Report::getAllLayTypes(),
                'PROF' => Report::getAllProfTypes(),
                'PA' => Report::getAllPaTypes(),
                default => [],
            };

            $query
                ->andWhere('r.type IN (:types)')
                ->setParameter('types', $types);
        }

        return intval($query->getQuery()->getSingleScalarResult());
    }
}
