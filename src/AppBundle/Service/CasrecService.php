<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CasrecService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ReportService
     */
    protected $reportService;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * CasrecService constructor.
     *
     * @param EntityManager      $em
     * @param LoggerInterface    $logger
     * @param ReportService      $reportService
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManager $em, LoggerInterface $logger, ReportService $reportService, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->reportService = $reportService;
        $this->validator = $validator;
    }

    /**
     * @param array $data
     *
     * @return array [added=>, errors=>]
     */
    public function addBulk(array $data)
    {
        $maxRecords = 10000; // memory failure above this limit
        $persistEvery = 5000; //optimised for performances

        $count = count($data);

        if (!$count) {
            throw new \RuntimeException('No record received from the API');
        }
        if ($count > $maxRecords) {
            throw new \RuntimeException("Max $maxRecords records allowed in a single bulk insert");
        }

        $this->logger->notice(__METHOD__ . ': Received ' . count($data) . ' records');

        $retErrors = [];
        try {
            $this->em->beginTransaction();
            $added = 1;

            //  Load up the data array into an array of CasRec entities
            $casRecEntities = [];

            foreach ($data as $dataIndex => $row) {
                //  Create a CasRec entity from the data and add it to the array of entities
                $casRecEntities[] = $casRecEntity = new CasRec($row);

                //  Validate the entity before adding it the entity manager to persist
                $errors = $this->validator->validate($casRecEntity);

                if (count($errors) > 0) {
                    $retErrors[] = 'ERROR IN LINE ' . ($dataIndex + 2) . ' :' . str_replace('Object(AppBundle\Entity\CasRec).', '', (string) $errors);
                    unset($casRecEntity);
                } else {
                    $casRecEntity->setUpdatedAt(new \DateTime());
                    $this->em->persist($casRecEntity);

                    if (($added++ % $persistEvery) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                    }
                }
            }

            $this->em->flush();
            $this->logger->notice(__METHOD__ . ': flushed');

            //  Before committing the CasRec entities use the report service to update any report types if necessary
            $this->reportService->updateCurrentReportTypes($casRecEntities, User::ROLE_LAY_DEPUTY);
            $this->logger->notice(__METHOD__ . ': report types updated');

            $this->em->commit();
            $this->em->clear();
        } catch (\Throwable $e) {
            return ['added' => $added - 1, 'errors' => [$e->getMessage()]];
        }

        return ['added' => $added - 1, 'errors' => $retErrors];
    }
}
