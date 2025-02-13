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

    /**
     * @param bool $detailedInfo Set to true to get detailed info about changes made to the database
     */
    public function handleNewMultiClients(bool $detailedInfo = false): array
    {
        $preRegistrationNewClients = $this->em->getRepository(PreRegistration::class)->getNewClientsForExistingDeputiesArray();
        $numMultiClients = count($preRegistrationNewClients);

        $info = [
            // potential new clients, not necessarily added
            'new-clients-found' => $numMultiClients,

            // clients actually added from list of potential new clients
            'clients-added' => 0,

            'errors' => [],

            // entities added/changed; remains empty unless $detailedInfo is true
            'details' => [],
        ];

        if (0 == $numMultiClients) {
            $this->logger->info('No new multi clients to add');

            return $info;
        }

        $clientsAdded = [];
        $errors = [];
        $entityDetails = [];
        foreach ($preRegistrationNewClients as $preReg) {
            $layDeputyshipDto = $this->assembler->assembleFromArray($preReg);
            $deputyUid = $layDeputyshipDto->getDeputyUid();
            $caseNumber = $layDeputyshipDto->getCaseNumber();

            try {
                $this->em->beginTransaction();

                $user = $this->findUser($deputyUid);

                $clientHandleResult = $this->handleNewClient($layDeputyshipDto, $user);

                /** @var Client $client */
                $client = $clientHandleResult['client'];
                if ($clientHandleResult['isNewClient']) {
                    $clientsAdded[] = $caseNumber;
                }

                $report = $clientHandleResult['existingReport'];
                if (is_null($report)) {
                    $report = $this->handleNewReport($layDeputyshipDto, $client);
                }

                $this->commitTransactionToDatabase();

                if ($detailedInfo) {
                    $users = $client->getUsers();
                    $deputyUids = [];
                    foreach ($users as $user) {
                        $deputyUids[] = $user->getDeputyUid();
                    }

                    // this data reported from the database
                    $entityDetails[] = [
                        // was a new client created for this row?
                        'isNewClient' => $clientHandleResult['isNewClient'],

                        // ID of the client used for this row
                        'clientId' => $client->getId(),

                        // UIDs of deputies associated with the client (including the deputy from the row)
                        'clientDeputyUids' => $deputyUids,

                        // was a new report created for this row?
                        'isNewReport' => is_null($clientHandleResult['existingReport']),

                        // ID of the report used for this row
                        'reportId' => $report->getId(),

                        // type of the report used for this row; '102', '103', '104', '102-4', '103-4'
                        'reportType' => $report->getType(),

                        // type of the existing report which was updated for this row, e.g.
                        // if we received a '102-4' marked HYBRID and the existing report was a '102', this would
                        // contain '102' (the report type before we updated it to '102-4')
                        'oldReportType' => $clientHandleResult['oldReportType'],

                        // data from the CSV parsed into the DTO
                        'dto.caseNumber' => $layDeputyshipDto->getCaseNumber(),
                        'dto.deputyUid' => $layDeputyshipDto->getDeputyUid(),
                        'dto.orderType' => $layDeputyshipDto->getOrderType(),
                        'dto.typeOfReport' => $layDeputyshipDto->getTypeOfReport(),
                        'dto.orderDate' => $layDeputyshipDto->getOrderDate(),
                    ];
                }
            } catch (\Throwable $e) {
                $message = sprintf('Error when creating additional client for deputyUID %s for case %s: %s',
                    $layDeputyshipDto->getDeputyUid(),
                    $layDeputyshipDto->getCaseNumber(),
                    str_replace(PHP_EOL, '', $e->getMessage())
                );
                $this->logger->warning($message);
                $this->logger->error($e->getFile().' '.$e->getLine());
                $errors[] = $message;
                continue;
            }
        }

        $info['clients-added'] = count($clientsAdded);
        $info['errors'] = $errors;
        $info['details'] = $entityDetails;

        return $info;
    }

    /*
     * Assumption is that we can just associate all clients to the existing user in the dd_user table.
     * If there is no user in this table, we won't get any potential new clients as we don't have anything to attach
     * them to.
     */
    private function findUser(string $deputyUid): ?User
    {
        $userRepo = $this->em->getRepository(User::class);

        try {
            $primaryDeputyUser = $userRepo->findPrimaryUserByDeputyUid($deputyUid);
        } catch (NoResultException|NonUniqueResultException $e) {
            throw new \RuntimeException(sprintf('The primary user for deputy UID %s was either missing or not unique', $deputyUid));
        }

        return $primaryDeputyUser;
    }

    /*
     * @returns ['client' => Client, 'isNewClient' => bool, 'existingReport' => ?Report, 'oldReportType' => ?string]
     * client is either an existing client or a new one
     * isNewClient is true if client is a new one
     * existingReport is only set if an existing report is going to be used for this row/DTO
     * oldReportType is only set if the report type on the existing report was changed (i.e. it became a hybrid)
     */
    private function handleNewClient(LayDeputyshipDto $dto, User $newUser): array
    {
        $caseNumber = $dto->getCaseNumber();
        $existingClients = $this->em->getRepository(Client::class)->findByCaseNumberIncludingDischarged($caseNumber);

        if (!is_countable($existingClients)) {
            throw new \ValueError(sprintf('unable to find clients for case number %s', $caseNumber));
        }

        /* @var Client $existingClient */
        foreach ($existingClients as $existingClient) {
            // Loop through the existing clients to ensure we are not creating a client for a deputy already associated
            // with this case; NB this should already have been excluded by the
            // PreRegistrationRepository->getNewClientsForExistingDeputiesArray() query but this is a double check, I think
            foreach ($existingClient->getUsers() as $user) {
                if ($user->getDeputyUid() == $newUser->getDeputyUid()) {
                    if ($existingClient->isDeleted()) {
                        throw new \RuntimeException(sprintf('a discharged client with case number %s already exists that is associated with a user with deputy UID %s', $existingClient->getCaseNumber(), $newUser->getDeputyUid()));
                    }
                    throw new \RuntimeException(sprintf('an active client with case number %s already exists', $caseNumber));
                }
            }
        }

        $client = null;
        $existingReport = null;
        $oldReportType = null;
        $isNewClient = true;
        if (1 === count($existingClients)) {
            // If there is one Client, and the above checks were OK, we might be able to just use that Client,
            // rather than making a new one, providing this deputy can see that client's report as a co-deputy;
            // to work that out, we work out which report type we should be creating, then check whether the
            // existing client already has a report of a compatible type and that the row is marked as HYBRID.
            //
            // COMPATIBLE REPORT TYPES (incoming = in CSV row, existing = type of existing report on client)
            // incoming = 102, existing = 102
            // incoming = 103, existing = 103
            // incoming = 104, existing = 104
            // incoming = 102-4, existing = 102 or 102-4, incoming row marked as HYBRID (report will become a hybrid)
            // incoming = 103-4, existing = 103 or 103-4, incoming row marked as HYBRID (report will become a hybrid)
            /** @var Client $potentialClient */
            $potentialClient = $existingClients[0];
            $determinedReportType = PreRegistration::getReportTypeByOrderType(
                $dto->getTypeOfReport(),
                $dto->getOrderType(),
                PreRegistration::REALM_LAY,
            );

            $compatibleReport = str_starts_with($determinedReportType, $potentialClient->getCurrentReport()->getType());
            if (str_ends_with($determinedReportType, '-4')) {
                $compatibleReport &= 'HYBRID' === $dto->getHybrid();
            }

            if ($compatibleReport) {
                $client = $potentialClient;
                $isNewClient = false;
                $existingReport = $potentialClient->getCurrentReport();

                // set the report type if it needs to be converted to a hybrid report and store $oldReportType
                $existingReportType = $existingReport->getType();
                if ($existingReportType !== $determinedReportType) {
                    $oldReportType = $existingReportType;
                    $existingReport->setType($determinedReportType);
                }
            }
        }

        if (is_null($client)) {
            // only create a new client if one doesn't already exist;
            // or if all the clients are discharged, and we are creating a client for a deputy that is not associated
            // with this case number already
            $client = $this->clientAssembler->assembleFromLayDeputyshipDto($dto);
        }

        $client->addUser($newUser);
        $this->em->persist($client);

        return [
            'client' => $client,
            'existingReport' => $existingReport,
            'oldReportType' => $oldReportType,
            'isNewClient' => $isNewClient,
        ];
    }

    // we only call this if we created a new client; otherwise we are reusing an existing client and report
    private function handleNewReport(LayDeputyshipDto $dto, Client $newClient): Report
    {
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
