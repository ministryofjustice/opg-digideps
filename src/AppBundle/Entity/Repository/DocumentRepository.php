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

    /**
     * @param $id document ID
     */
    public function hardDeleteDocument($id)
    {
        $filter = $this->_em->getFilters()->enable('softdeleteable');
        $filter->disableForEntity(Document::class);

        /** @var $document EntityDir\Report\Document */
        $document = $this->_em->getRepository(Document::class)->find($id);
        $this->_em->remove($document);
        $this->_em->flush();

        $this->_em->getFilters()->enable('softdeleteable');
    }

}
