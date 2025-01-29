<?php

namespace App\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\v2\Assembler\ClientAssembler;
use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\DTO\LayDeputyshipDtoCollection;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationCreationException;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationFactory;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

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
        private LoggerInterface $logger,
        private SiriusToLayDeputyshipDtoAssembler $assembler,
        private ClientAssembler $clientAssembler,
    ) {
    }

    public function upload(LayDeputyshipDtoCollection $collection): array
    {
        $this->throwExceptionIfDataTooLarge($collection);
        $this->preRegistrationEntriesByCaseNumber = [];
        $added = 0;
        $errors = [];

        try {
            $this->em->beginTransaction();

            foreach ($collection as $layDeputyshipDto) {
                try {
                    $caseNumber = strtolower((string) $layDeputyshipDto->getCaseNumber());
                    $this->preRegistrationEntriesByCaseNumber[$caseNumber] = $this->createAndPersistNewPreRegistrationEntity($layDeputyshipDto);
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

    public function handleNewMultiClients(): array
    {
        $errors = [];

        $preRegistrationNewClients = $this->em->getRepository(PreRegistration::class)->getNewClientsForExistingDeputiesArray();
        $numMultiClients = count($preRegistrationNewClients);

        if (0 == $numMultiClients) {
            $this->logger->info('No new multi clients to add');

            return [
                'new-clients-found' => $numMultiClients,
                'clients-added' => 0,
                'errors' => $errors,
            ];
        }

        foreach ($preRegistrationNewClients as $preReg) {
            $layDeputyshipDto = $this->assembler->assembleFromArray($preReg);
            $deputyUid = $layDeputyshipDto->getDeputyUid();
            $caseNumber = $layDeputyshipDto->getCaseNumber();

            try {
                $this->em->beginTransaction();

                $user = $this->handleNewUser($deputyUid);

                $client = $this->handleNewClient($layDeputyshipDto, $user);
                $clientsAdded[] = $caseNumber;

                $this->handleNewReport($layDeputyshipDto, $client);

                $this->commitTransactionToDatabase();
            } catch (\Throwable $e) {
                $message = sprintf('Error when creating additional client for deputyUID %s for case %s: %s',
                    $layDeputyshipDto->getDeputyUid(),
                    $layDeputyshipDto->getCaseNumber(),
                    str_replace(PHP_EOL, '', $e->getMessage())
                );
                $this->logger->warning($message);
                $errors[] = $message;
                continue;
            }
        }

        return [
            // potential new clients, not necessarily added
            'new-clients-found' => count($preRegistrationNewClients),

            // clients actually added from list of potential new clients
            'clients-added' => count($clientsAdded),

            'errors' => $errors,
        ];
    }

    private function handleNewUser(string $deputyUid): ?User
    {
        $userRepo = $this->em->getRepository(User::class);

        try {
            $primaryDeputyUser = $userRepo->findPrimaryUserByDeputyUid($deputyUid);
        } catch (NoResultException|NonUniqueResultException $e) {
            throw new \RuntimeException(sprintf('The primary user for deputy UID %s was either missing or not unique', $deputyUid));
        }

        $newSecondaryUser = clone $primaryDeputyUser;

        // give duplicate user a unique, non-functional email address, with their deputy UID in it
        // for future de-duplication
        $uuid = str_replace('-', '', Uuid::uuid4()->toString());
        $newSecondaryUser->setEmail($deputyUid.'-'.$uuid.'@dupe');

        $newSecondaryUser->setIsPrimary(false);

        $this->em->persist($newSecondaryUser);

        return $newSecondaryUser;
    }

    private function handleNewClient(LayDeputyshipDto $dto, User $newUser): ?Client
    {
        $caseNumber = $dto->getCaseNumber();
        $existingClients = $this->em->getRepository(Client::class)->findByCaseNumberIncludingDischarged($caseNumber);

        if (!is_countable($existingClients)) {
            throw new \ValueError(sprintf('unable to find clients for case number %s', $caseNumber));
        }

        if (count($existingClients) > 0) {
            /* @var Client $existingClient */
            // If there is an existing active client, we shouldn't create a new instance of the client
            foreach ($existingClients as $existingClient) {
                if (!$existingClient->isDeleted()) {
                    throw new \RuntimeException(sprintf('an active client with case number %s already exists', $caseNumber));
                }
            }
            // Loop through the discharged clients to ensure we are not creating an account for a deputy associated with a discharged client
            foreach ($existingClients as $existingClient) {
                foreach ($existingClient->getUsers() as $user) {
                    if ($user->getDeputyUid() == $newUser->getDeputyUid()) {
                        throw new \RuntimeException(sprintf('a discharged client with case number %s already exists that is associated with a user with deputy UID %s', $existingClient->getCaseNumber(), $newUser->getDeputyUid()));
                    }
                }
            }
        }

        // Only create a new instance of the client if one doesn't already exist;
        // or if all the clients are discharged, and we are creating an account for a deputy that is not associated
        // with this case number already.
        $newClient = $this->clientAssembler->assembleFromLayDeputyshipDto($dto);
        $newClient->addUser($newUser);
        $this->em->persist($newClient);

        return $newClient;
    }

    private function handleNewReport(LayDeputyshipDto $dto, Client $newClient): ?Report
    {
        $existingReport = $newClient->getCurrentReport();

        if ($existingReport instanceof Report) {
            throw new \RuntimeException('report already exists');
        } else {
            $determinedReportType = PreRegistration::getReportTypeByOrderType($dto->getTypeOfReport(), $dto->getOrderType(), PreRegistration::REALM_LAY);

            $reportStartDate = clone $dto->getOrderDate();
            $reportEndDate = clone $reportStartDate;
            $reportEndDate->add(new \DateInterval('P364D'));

            $newReport = new Report(
                $newClient,
                $determinedReportType,
                $reportStartDate,
                $reportEndDate,
                false
            );

            $newReport->setClient($newClient);
            $this->em->persist($newReport);
        }

        return $newReport;
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
            $this->logger->error(sprintf('Error whilst updating report type for report with ID: %d, for case number: %s', $currentActiveReportId, $reportCaseNumber));
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
