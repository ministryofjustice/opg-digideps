<?php

namespace App\v2\Registration\Uploader;

use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\DTO\LayDeputyshipDtoCollection;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationCreationException;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationFactory;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

class LayDeputyshipUploader
{
    /** @var array */
    private $reportsUpdated = [];

    /** @var array */
    private $preRegistrationEntriesByCaseNumber = [];

    /** @var int */
    public const MAX_UPLOAD = 10000;

    /** @var int */
    public const FLUSH_EVERY = 5000;

    public function __construct(
        private EntityManagerInterface $em,
        private ReportRepository $reportRepository,
        private PreRegistrationFactory $preRegistrationFactory,
        private LoggerInterface $verboseLogger
    ) {
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
                    $caseNumber = strtolower((string) $layDeputyshipDto->getCaseNumber());
                    $this->preRegistrationEntriesByCaseNumber[$caseNumber] = $this->createAndPersistNewPreRegistrationEntity($layDeputyshipDto);
                    ++$added;
                } catch (PreRegistrationCreationException $e) {
                    $message = sprintf('ERROR IN LINE %d: %s', $index + 2, $e->getMessage());
                    $this->verboseLogger->error($message);
                    $errors[] = $message;
                    continue;
                }
            }

            $this
                ->updateReportTypes()
                ->commitTransactionToDatabase();
        } catch (\Throwable $e) {
            $this->verboseLogger->error($e->getMessage());

            return ['added' => $added, 'errors' => [$e->getMessage()]];
        }

        return [
            'added' => $added,
            'errors' => $errors,
            'report-update-count' => count($this->reportsUpdated),
            'cases-with-updated-reports' => $this->reportsUpdated,
            'source' => 'sirius',
        ];
    }

    private function throwExceptionIfDataTooLarge(LayDeputyshipDtoCollection $collection): void
    {
        if ($collection->count() > self::MAX_UPLOAD) {
            throw new \RuntimeException(sprintf('Max %d records allowed in a single bulk insert', self::MAX_UPLOAD));
        }
    }

    /**
     * @throws ORMException
     */
    private function createAndPersistNewPreRegistrationEntity(LayDeputyshipDto $layDeputyshipDto): PreRegistration
    {
        $preRegistrationEntity = $this->preRegistrationFactory->createFromDto($layDeputyshipDto);

        $this->em->persist($preRegistrationEntity);

        return $preRegistrationEntity;
    }

    /**
     * @throws \Exception
     */
    private function updateReportTypes(): LayDeputyshipUploader
    {
        $reportCaseNumber = '';
        $currentActiveReportId = null;
        $caseNumbers = array_keys($this->preRegistrationEntriesByCaseNumber);
        $reports = $this->reportRepository->findAllActiveReportsByCaseNumbersAndRole($caseNumbers, User::ROLE_LAY_DEPUTY);

        try {
            /** @var Report $currentActiveReport */
            foreach ($reports as $currentActiveReport) {
                $reportCaseNumber = strtolower($currentActiveReport->getClient()->getCaseNumber());
                $currentActiveReportId = $currentActiveReport->getId();
                /** @var PreRegistration $preRegistration */
                $preRegistration = $this->preRegistrationEntriesByCaseNumber[$reportCaseNumber];
                $determinedReportType = PreRegistration::getReportTypeByOrderType($preRegistration->getTypeOfReport(), $preRegistration->getOrderType(), PreRegistration::REALM_LAY);

                // For Dual Cases, deputy uid needs to match for the report type to be updated
                if (PreRegistration::DUAL_TYPE == $preRegistration->getHybrid()) {
                    if ($currentActiveReport->getClient()->getUsers()[0]->getDeputyNo() == $preRegistration->getDeputyUid()) {
                        if ($currentActiveReport->getType() != $determinedReportType) {
                            $currentActiveReport->setType($determinedReportType);
                            $this->reportsUpdated[] = $reportCaseNumber;
                        }
                    }
                } else {
                    if ($currentActiveReport->getType() != $determinedReportType) {
                        $currentActiveReport->setType($determinedReportType);
                        $this->reportsUpdated[] = $reportCaseNumber;
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->verboseLogger->error(sprintf('Error whilst updating report type for report with ID: %d, for case number: %s', $currentActiveReportId, $reportCaseNumber));
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @throws MappingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function commitTransactionToDatabase(): void
    {
        $this->em->flush();
        $this->em->commit();
        $this->em->clear();
    }
}
