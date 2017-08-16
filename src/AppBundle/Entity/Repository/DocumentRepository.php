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
        $qb = $this->createQueryBuilder('d')
                ->where('d.deletedAt IS NOT NULL');

        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(Document::class);
        $records = $qb->getQuery()->getResult(); /* @var $records Document[] */
        $this->_em->getFilters()->enable('softdeleteable');

        return $records;
    }

    /**
     * @param $id document ID
     */
    public function hardDeleteDocument($id)
    {
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(Document::class);

        /** @var $document EntityDir\Report\Document */
        $document = $this->_em->getRepository(Document::class)->find($id);
        if (!$document->getDeletedAt()) {
            throw new \RuntimeException("Can't hard delete document $id, as it's not soft-deleted");
        }
        $this->_em->remove($document);

        $this->_em->getFilters()->enable('softdeleteable');

        $this->_em->flush($document);
    }

}
