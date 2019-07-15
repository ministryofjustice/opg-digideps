<?php

namespace AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\Entity\User;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use AppBundle\v2\Registration\DTO\LayDeputyshipDtoCollection;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecCreationException;
use AppBundle\v2\Registration\SelfRegistration\Factory\CasRecFactory;
use Doctrine\ORM\EntityManager;

class LayDeputyshipUploader
{
    /** @var EntityManager */
    protected $em;

    /** @var ReportRepository */
    protected $reportRepository;

    /** @var CasRecFactory */
    private $casRecFactory;

    /** @var array */
    private $reportsUpdated = [];

    /** @var array */
    private $casRecEntriesByCaseNumber = [];

    /** @var int */
    const MAX_UPLOAD = 10000;

    /** @var int */
    const FLUSH_EVERY = 5000;

    /**
     * @param EntityManager $em
     * @param ReportRepository $reportRepository
     * @param CasRecFactory $casRecFactory
     */
    public function __construct(
        EntityManager $em,
        ReportRepository $reportRepository,
        CasRecFactory $casRecFactory
    ) {
        $this->em = $em;
        $this->reportRepository = $reportRepository;
        $this->casRecFactory = $casRecFactory;
    }

    /**
     * @param LayDeputyshipDtoCollection $collection
     * @return array
     */
    public function upload(LayDeputyshipDtoCollection $collection): array
    {
        $this->throwExceptionIfDataTooLarge($collection);

        $added = 0;
        $errors = [];

        try {
            $this->em->beginTransaction();

            foreach ($collection as $index => $layDeputyshipDto) {

                try {
                    $caseNumber = (string) $layDeputyshipDto->getCaseNumber();
                    $this->casRecEntriesByCaseNumber[$caseNumber] = $this->createAndPersistNewCasRecEntity($layDeputyshipDto);
                    ++$added;
                } catch (CasRecCreationException $e) {
                    $errors[] = sprintf('ERROR IN LINE %d: %s', $index + 2, $e->getMessage());
                    continue;
                }
            }

            $this
                ->updateReportTypes()
                ->commitTransactionToDatabase();

        } catch (\Throwable $e) {
            return ['added' => $added, 'errors' => [$e->getMessage()]];
        }

        return [
            'added' => $added,
            'errors' => $errors,
            'report-update-count' => count($this->reportsUpdated),
            'cases-with-updated-reports' => $this->reportsUpdated
        ];
    }

    /**
     * @param LayDeputyshipDtoCollection $collection
     */
    private function throwExceptionIfDataTooLarge(LayDeputyshipDtoCollection $collection): void
    {
        if ($collection->count() > self::MAX_UPLOAD) {
            throw new \RuntimeException(sprintf(
                'Max %d records allowed in a single bulk insert',
                self::MAX_UPLOAD
            ));
        }
    }

    /**
     * @param LayDeputyshipDto $layDeputyshipDto
     * @return CasRec
     * @throws \Doctrine\ORM\ORMException
     */
    private function createAndPersistNewCasRecEntity(LayDeputyshipDto $layDeputyshipDto): CasRec
    {
        $casRecEntity = $this->casRecFactory->createFromDto($layDeputyshipDto);

        $this->em->persist($casRecEntity);

        return $casRecEntity;
    }

    /**
     * @return LayDeputyshipUploader
     * @throws \Exception
     */
    private function updateReportTypes(): LayDeputyshipUploader
    {
        $caseNumbers = array_keys($this->casRecEntriesByCaseNumber);
        $reports = $this->reportRepository->findAllActiveReportsByCaseNumbersAndRole($caseNumbers, User::ROLE_LAY_DEPUTY);

        foreach ($reports as $currentActiveReport) {
            $reportCaseNumber = $currentActiveReport->getClient()->getCaseNumber();
            $casRec = $this->casRecEntriesByCaseNumber[$reportCaseNumber];
            $determinedReportType = CasRec::getTypeBasedOnTypeofRepAndCorref($casRec->getTypeOfReport(), $casRec->getCorref(), User::ROLE_LAY_DEPUTY);

            if ($currentActiveReport->getType() != $determinedReportType) {
                $currentActiveReport->setType($determinedReportType);
                $this->reportsUpdated[] = $reportCaseNumber;
            }
        }

        return $this;
    }

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function commitTransactionToDatabase(): void
    {
        $this->em->flush();
        $this->em->commit();
        $this->em->clear();
    }
}
