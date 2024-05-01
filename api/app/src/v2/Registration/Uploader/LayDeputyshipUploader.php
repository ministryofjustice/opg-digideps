<?php

namespace App\v2\Registration\Uploader;

use App\Entity\CourtOrder;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\v2\Registration\Assembler\CourtOrderDtoAssembler;
use App\v2\Registration\DTO\CourtOrderDto;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\DTO\LayDeputyshipDtoCollection;
use App\v2\Registration\SelfRegistration\Factory\CourtOrderCreationException;
use App\v2\Registration\SelfRegistration\Factory\CourtOrderFactory;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationCreationException;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $em,
        private readonly ReportRepository $reportRepository,
        private readonly PreRegistreportRepositoryrationFactory $preRegistrationFactory,
        private readonly LoggerInterface $logger,
        private readonly CourtOrderDtoAssembler $courtOrderAssembler,
        private readonly CourtOrderFactory $courtOrderFactory,
    ) {
    }

    public function upload(LayDeputyshipDtoCollection $collection): array
    {
        $this->throwExceptionIfDataTooLarge($collection);
        $this->preRegistrationEntriesByCaseNumber = [];
        $added = 0;
        $errors = [];
        $courtOrderUids = [];

        try {
            $this->em->beginTransaction();

            foreach ($collection as $layDeputyshipDto) {
                try {
                    $caseNumber = strtolower((string) $layDeputyshipDto->getCaseNumber());
                    $this->preRegistrationEntriesByCaseNumber[$caseNumber] = $this->createAndPersistNewPreRegistrationEntity($layDeputyshipDto);

                    if ($courtOrder = $this->findCourtOrderEntity($layDeputyshipDto->getCourtOrderUid())) {
                        if ($courtOrder->getOrderType() !== $layDeputyshipDto->getOrderType()) {
                            $courtOrder->setOrderType($layDeputyshipDto->getOrderType());
                        }

                        $courtOrder->setActive(true);
                    } else {
                        $courtOrder = $this->createCourtOrderEntity($layDeputyshipDto);
                    }

                    $this->persistCourtOrderEntity($courtOrder);
                    ++$added;
                } catch (PreRegistrationCreationException|CourtOrderCreationException $e) {
                    $message = str_replace(PHP_EOL, '', $e->getMessage());
                    $message = sprintf('ERROR IN LINE: %s', $message);
                    $this->logger->error($message);
                    $errors[] = $message;
                    continue;
                }

                $courtOrderUids[] = $layDeputyshipDto->getCourtOrderUid();
            }

            $this
                ->updateReportTypes()
                ->commitTransactionToDatabase();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            $errors[] = $e->getMessage();

            return ['added' => $added, 'errors' => $errors];
        }

        return [
            'added' => $added,
            'errors' => $errors,
            'report-update-count' => count($this->reportsUpdated),
            'cases-with-updated-reports' => $this->reportsUpdated,
            'source' => 'sirius',
            'court-orders' => $courtOrderUids,
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
        $reports = $this->findAllActiveReportsByCaseNumbersAndRole($caseNumbers, User::ROLE_LAY_DEPUTY);

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
            $this->logger->error(sprintf('Error whilst updating report type for report with ID: %d, for case number: %s', $currentActiveReportId, $reportCaseNumber));
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    private function commitTransactionToDatabase(): void
    {
        $this->em->flush();
        $this->em->commit();
        $this->em->clear();
    }

    private function findCourtOrderEntity(int $courtOrderUid): CourtOrder
    {
        return $this->em->getRepository(CourtOrder::class)->findOneBy(['courtOrderUid' => $courtOrderUid]);
    }

    private function createCourtOrderEntity(LayDeputyshipDto $layDeputyshipDto): CourtOrderDto
    {
        return $this->courtOrderAssembler->assembleFromLayDto($layDeputyshipDto);
    }

    private function persistCourtOrderEntity(CourtOrderDto $courtOrder): CourtOrder
    {
        $courtOrderEntity = $this->courtOrderFactory->createFromDto($courtOrder);

        $this->em->persist($courtOrderEntity);

        return $courtOrderEntity;
    }
}
