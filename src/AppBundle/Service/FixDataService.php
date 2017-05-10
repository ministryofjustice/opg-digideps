<?php

namespace AppBundle\Service;

use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\EntityManager;

class FixDataService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * FixDataService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->reportRepo = $this->em->getRepository(Report::class);
        $this->odrRepo = $this->em->getRepository(Odr::class);
    }

    public function fixReports()
    {
        $this->reportRepo = $this->em->getRepository(Report::class);
        foreach ($this->reportRepo->findAll() as $entity) {
            $debtsAdded = $this->reportRepo->addDebtsToReportIfMissing($entity);
            if ($entity->getType() == Report::TYPE_103) {
                $shortMoneyCatsAdded = $this->reportRepo->addMoneyShortCategoriesIfMissing($entity);
                $this->messages[] = "Report {$entity->getId()}: $debtsAdded debts, $shortMoneyCatsAdded money short cars added";
            }
        }

        foreach ($this->odrRepo->findAll() as $entity) {
            $debtsAdded = $this->odrRepo->addDebtsToOdrIfMissing($entity);
            $incomeBenefitsAdded = $this->odrRepo->addIncomeBenefitsToOdrIfMissing($entity);
            $this->messages[] = "Report {$entity->getId()}: $debtsAdded debts, $incomeBenefitsAdded income benefits added";
        }

        $this->em->flush();

        return $this;
    }


    public function fixNdrs()
    {
        foreach ($this->odrRepo->findAll() as $entity) {
            $debtsAdded = $this->odrRepo->addDebtsToOdrIfMissing($entity);
            $incomeBenefitsAdded = $this->odrRepo->addIncomeBenefitsToOdrIfMissing($entity);
            $this->messages[] = "Report {$entity->getId()}: $debtsAdded debts, $incomeBenefitsAdded income benefits added";
        }

        $this->em->flush();

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }



}
