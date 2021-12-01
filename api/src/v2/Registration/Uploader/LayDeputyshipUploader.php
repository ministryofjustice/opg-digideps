<?php

namespace App\v2\Registration\Uploader;

use App\Entity\CasRec;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\DTO\LayDeputyshipDtoCollection;
use App\v2\Registration\SelfRegistration\Factory\CasRecCreationException;
use App\v2\Registration\SelfRegistration\Factory\CasRecFactory;
use Doctrine\ORM\EntityManagerInterface;

class LayDeputyshipUploader
{
    /** @var EntityManagerInterface */
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

    public function __construct(
        EntityManagerInterface $em,
        ReportRepository $reportRepository,
        CasRecFactory $casRecFactory
    ) {
        $this->em = $em;
        $this->reportRepository = $reportRepository;
        $this->casRecFactory = $casRecFactory;
    }

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

        foreach ($collection as $layDeputyshipDto) {
            $source = $layDeputyshipDto->getSource();
            break;
        }

        return [
            'added' => $added,
            'errors' => $errors,
            'report-update-count' => count($this->reportsUpdated),
            'cases-with-updated-reports' => $this->reportsUpdated,
            'source' => $source,
        ];
    }

    private function throwExceptionIfDataTooLarge(LayDeputyshipDtoCollection $collection): void
    {
        if ($collection->count() > self::MAX_UPLOAD) {
            throw new \RuntimeException(sprintf('Max %d records allowed in a single bulk insert', self::MAX_UPLOAD));
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    private function createAndPersistNewCasRecEntity(LayDeputyshipDto $layDeputyshipDto): CasRec
    {
        $casRecEntity = $this->casRecFactory->createFromDto($layDeputyshipDto);

        $this->em->persist($casRecEntity);

        return $casRecEntity;
    }

    /**
     * @throws \Exception
     */
    private function updateReportTypes(): LayDeputyshipUploader
    {
        $caseNumbers = array_keys($this->casRecEntriesByCaseNumber);
        $reports = $this->reportRepository->findAllActiveReportsByCaseNumbersAndRole($caseNumbers, User::ROLE_LAY_DEPUTY);

        foreach ($reports as $currentActiveReport) {
            $reportCaseNumber = $currentActiveReport->getClient()->getCaseNumber();
            $casRec = $this->casRecEntriesByCaseNumber[$reportCaseNumber];
            $determinedReportType = CasRec::getTypeBasedOnTypeofRepAndCorref($casRec->getTypeOfReport(), $casRec->getCorref(), CasRec::REALM_LAY);

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
