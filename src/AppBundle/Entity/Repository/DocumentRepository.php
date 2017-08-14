<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Report\Document;
use Doctrine\ORM\EntityRepository;

class DocumentRepository extends EntityRepository
{
    /**
     * Get soft-deleted documents
     *
     * @return Document[]
     */
    public function retrieveSoftDeleted()
    {
        $qb = $this->createQueryBuilder('d');

        $filter = $this->_em->getFilters()->enable('softdeleteable');
        $filter->disableForEntity(Document::class);
        $records = $qb->getQuery()->getResult(); /* @var $records Document[] */
        $this->_em->getFilters()->enable('softdeleteable');

        return $records;
    }
}
