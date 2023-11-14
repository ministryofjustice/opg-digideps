<?php

namespace App\Service\Search;

use App\Entity\Client;
use Doctrine\ORM\QueryBuilder;

class ClientSearchFilter
{
    public function handleSearchTermFilter(string $searchTerm, QueryBuilder $qb, string $alias): void
    {
        if (Client::isValidCaseNumber($searchTerm)) {
            $qb->andWhere('lower('.$alias.'.caseNumber) = :cn');
            $qb->setParameter('cn', strtolower($searchTerm));
        } else {
            $searchTerms = explode(' ', $searchTerm);

            if (1 === count($searchTerms)) {
                $this->addBroadMatchFilter($searchTerm, $qb, $alias);
            } else {
                $this->addFullNameExactMatchFilter($searchTerms[0], $searchTerms[1], $qb, $alias);
            }
        }
    }

    private function addBroadMatchFilter(string $query, QueryBuilder $qb, string $alias): void
    {
        $qb->andWhere('lower('.$alias.'.firstname) LIKE :qLike OR lower('.$alias.'.lastname) LIKE :qLike');
        $qb->setParameter('qLike', '%'.strtolower($query).'%');
    }

    private function addFullNameExactMatchFilter(string $firstName, string $lastname, QueryBuilder $qb, string $alias): void
    {
        $qb->andWhere('(lower('.$alias.'.firstname) = :firstname AND lower('.$alias.'.lastname) = :lastname)');
        $qb->setParameter('firstname', strtolower($firstName));
        $qb->setParameter('lastname', strtolower($lastname));
    }
}
