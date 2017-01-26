<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityRepository;

class ReportService
{
    /** @var EntityRepository */
    protected $reportRepository;

    public function __construct(EntityRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    public function findById($id)
    {
        return $this->reportRepository->findOneBy(['id' => $id]);
    }
}
