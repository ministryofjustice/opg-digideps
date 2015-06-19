<?php
namespace AppBundle\Filter;

use Doctrine\ORM\QueryBuilder;

interface UserFilterInterface
{
    public static function applyUserFilter(QueryBuilder $qb,$userId);
}