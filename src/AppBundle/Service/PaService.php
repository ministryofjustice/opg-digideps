<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Entity\Client;
use Doctrine\ORM\EntityManager;

class PaService
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->userRepository = $em->getRepository(User::class);
        $this->reportRepository = $em->getRepository(Report::class);
    }

    public function addFromCasrecRows(array $rows)
    {

    }

    public function getAddedRecords()
    {
        return 0;
    }

    public function getErrors()
    {
        return 0;
    }
}
