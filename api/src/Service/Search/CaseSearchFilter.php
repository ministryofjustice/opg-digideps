<?php

namespace App\Service\Search;

use App\Entity\Client;
use Doctrine\ORM\QueryBuilder;

class CaseSearchFilter
{
    public function handleSearchTermFilter(string $searchTerm, QueryBuilder $qb, string $alias): void
    {
        if (Client::isValidCaseNumber($searchTerm)) {
            $qb->andWhere('lower('.$alias.'.caseNumber) = :cn');
            $qb->setParameter('cn', strtolower($searchTerm));
        } else {
            $qb->andWhere('lower('.$alias.'.clientLastname) LIKE :qLike');
            $qb->setParameter('qLike', '%'.strtolower($searchTerm).'%');
        }
    }
}
