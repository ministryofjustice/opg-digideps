<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Report\Document;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;

class DocumentRepository extends AbstractEntityRepository
{
    /**
     * Get soft-deleted documents
     *
     * @return Document[]
     */
    public function retrieveSoftDeleted()
    {
        $qb = $this->createQueryBuilder('d')
                ->where('d.deletedAt IS NOT NULL');

        /** @var SoftDeleteableFilter $softDeleteableFilter */
        $softDeleteableFilter = $this->_em->getFilters()->getFilter('softdeleteable');
        $softDeleteableFilter->disableForEntity(Document::class);

        $records = $qb->getQuery()->getResult(); /* @var $records Document[] */
        $this->_em->getFilters()->enable('softdeleteable');

        return $records;
    }
}
