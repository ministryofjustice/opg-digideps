<?php

namespace App\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\v2\Assembler\ClientAssembler;
use App\v2\Registration\DTO\LayPreRegistrationDto;
use App\v2\Registration\DTO\LayPreRegistrationDtoCollection;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationCreationException;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationFactory;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

class LayDeputyshipUploader
{
    private array $reportsUpdated = [];

    private array $preRegistrationEntriesByCaseNumber = [];

    /** @var int */
    public const MAX_UPLOAD = 10000;

    /** @var int */
    public const FLUSH_EVERY = 5000;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ReportRepository $reportRepository,
        private readonly PreRegistrationFactory $preRegistrationFactory,
        private readonly ClientAssembler $clientAssembler,
        private readonly LoggerInterface $logger
    ) {
    }

    public function upload(LayPreRegistrationDtoCollection $collection): array
    {
        $this->throwExceptionIfDataTooLarge($collection);
        $this->preRegistrationEntriesByCaseNumber = [];
        $added = 0;
        $errors = [];

        try {
            $this->em->beginTransaction();

            foreach ($collection as $layPreRegistrationDto) {
                try {
                    $caseNumber = strtolower((string) $layPreRegistrationDto->getCaseNumber());
                    $this->preRegistrationEntriesByCaseNumber[$caseNumber] = $this->createAndPersistNewPreRegistrationEntity($layPreRegistrationDto);
                    ++$added;
                } catch (PreRegistrationCreationException $e) {
                    $message = str_replace(PHP_EOL, '', $e->getMessage());
                    $message = sprintf('ERROR IN LINE: %s', $message);
                    $this->logger->error($message);
                    $errors[] = $message;
                    continue;
                }
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
        ];
    }

    private function throwExceptionIfDataTooLarge(LayPreRegistrationDtoCollection $collection): void
    {
        if ($collection->count() > self::MAX_UPLOAD) {
            throw new \RuntimeException(sprintf('Max %d records allowed in a single bulk insert', self::MAX_UPLOAD));
        }
    }

    /**
     * @throws ORMException
     */
    private function createAndPersistNewPreRegistrationEntity(LayPreRegistrationDto $layPreRegistrationDto): PreRegistration
    {
        $preRegistrationEntity = $this->preRegistrationFactory->createFromDto($layPreRegistrationDto);

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
        $reports = $this->reportRepository->findAllActiveReportsByCaseNumbersAndRole(
            $caseNumbers, 
            User::ROLE_LAY_DEPUTY
        );

        try {
            /** @var Report $currentActiveReport */
            foreach ($reports as $currentActiveReport) {
                $reportCaseNumber = strtolower($currentActiveReport->getClient()->getCaseNumber());
                $currentActiveReportId = $currentActiveReport->getId();
                /** @var PreRegistration $preRegistration */
                $preRegistration = $this->preRegistrationEntriesByCaseNumber[$reportCaseNumber];
                $determinedReportType = PreRegistration::getReportTypeByOrderType(
                    $preRegistration->getTypeOfReport(), 
                    $preRegistration->getCourtOrderType(), 
                    PreRegistration::REALM_LAY
                );

                // For Dual Cases, deputy uid needs to match for the report type to be updated
                if (PreRegistration::DUAL_TYPE == $preRegistration->getHybrid()) {
                    $existingDeputyUid = $currentActiveReport->getClient()->getUsers()[0]->getDeputyNo();

                    if (empty($existingDeputyUid)) {
                        $existingDeputyUid = $currentActiveReport->getClient()->getUsers()[0]->getDeputyUid();
                    }

                    if ($existingDeputyUid == $preRegistration->getDeputyUid()) {
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
            $this->logger->error(
                sprintf(
                    'Error whilst updating report type for report with ID: %d, for case number: %s', 
                    $currentActiveReportId, 
                    $reportCaseNumber
                ));
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
    
    public function multiClientCreation(): void
    {
        $userRepo = $this->em->getRepository(User::class);
        $deputyRepo = $this->em->getRepository(Deputy::class);
        $preRegRepo = $this->em->getRepository(PreRegistration::class);
        $preRegClients = $preRegRepo->findExistingDeputiesMissingAddtionalClients();
        
        if (count($preRegClients) >= 1) {
            foreach ($preRegClients as $preRegRow) {
                file_put_contents('php://stderr', print_r($preRegRow, TRUE));
                
                /** @var $user User */
                $user = $userRepo->findOneBy(['deputyUid' => $preRegRow['deputy_uid']]);
                /** @var $deputy Deputy */
                $deputy = $deputyRepo->findOneBy(['user' => $user]);
                $client = $this->clientAssembler->assembleFromPreRegistrationData($preRegRow);
                
                if ($client instanceof Client) {
                    $client->setDeputy($deputy);

                    try {
                        $this->em->beginTransaction();
                        $this->em->persist($client);
                    } catch (\Throwable $e) {
                        $this->logger->error(
                            sprintf(
                                'Error whilst try to create new client for deputy ID: %d using PreRegistration row: %d',
                                $deputy->getId(),
                                $preRegRow['id']
                            ));
                        throw new \Exception($e->getMessage());
                    }
                }
            }

            try {
                $this->commitTransactionToDatabase();
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
