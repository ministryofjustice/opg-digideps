<?php

declare(strict_types=1);

namespace App\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\v2\Assembler\ClientAssembler;
use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use App\v2\Registration\DTO\LayDeputyshipDto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;

/**
 * Processor for an individual row in the lay CSV file.
 */
class LayCSVRowProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SiriusToLayDeputyshipDtoAssembler $layDeputyAssembler,
        private readonly ClientAssembler $clientAssembler,
        private readonly LayCSVClientMatcher $clientMatcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array $preReg An array as produced by PreRegistrationRepository->getNewClientsForExistingDeputiesArray()
     */
    public function processRow(array $preReg): array
    {
        $layDeputyshipDto = $this->layDeputyAssembler->assembleFromArray($preReg);
        $deputyUid = $layDeputyshipDto->getDeputyUid();

        $entityDetails = [];
        $errorMessage = null;

        try {
            $this->em->beginTransaction();

            $user = $this->findUser($deputyUid);

            $clientHandleResult = $this->handleNewClient($layDeputyshipDto);

            /** @var Client $client */
            $client = $clientHandleResult['client'];
            $client->addUser($user);

            /** @var ?Report $report */
            $report = $clientHandleResult['existingReport'];
            if (is_null($report)) {
                $report = $this->handleNewReport($layDeputyshipDto, $client);
            }

            $this->em->persist($report);
            $this->em->persist($client);
            $this->em->flush();
            $this->em->commit();
            $this->em->clear();

            // record what has been added to the db
            $users = $client->getUsers();
            $deputyUids = [];
            foreach ($users as $user) {
                $deputyUids[] = $user->getDeputyUid();
            }

            $entityDetails = $this->getEntityDetails(
                $clientHandleResult,
                $deputyUids,
                $report,
                $layDeputyshipDto
            );
        } catch (\Throwable $e) {
            $errorMessage = sprintf('Error when creating entities for deputyUID %s for case %s: %s',
                $layDeputyshipDto->getDeputyUid(),
                $layDeputyshipDto->getCaseNumber(),
                str_replace(PHP_EOL, '', $e->getMessage())
            );
            $this->logger->warning($errorMessage);
            $this->logger->error($e->getFile().' '.$e->getLine());
        }

        return [
            'entityDetails' => $entityDetails,
            'error' => $errorMessage,
        ];
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

        if (is_null($primaryDeputyUser)) {
            throw new \RuntimeException(sprintf('The primary user for deputy UID %s was either missing or not unique', $deputyUid));
        }

        return $primaryDeputyUser;
    }

    /*
     * @returns ['client' => Client, 'isNewClient' => bool, 'existingReport' => ?Report, 'oldReportType' => ?string]
     * client is either an existing active client or a new one
     * isNewClient is true if client is a new one
     * existingReport is only set if an existing report is going to be used for this row/DTO
     * oldReportType is only set if the report type on the existing report was changed (i.e. it became a hybrid)
     */
    private function handleNewClient(LayDeputyshipDto $dto): array
    {
        $clientMatch = $this->clientMatcher->matchDto($dto);
        $client = $clientMatch->client;

        $isNewClient = false;
        if (is_null($client)) {
            // only create a new client if one doesn't already exist;
            // or if all the clients are discharged, and we are creating a client for a deputy that is not associated
            // with this case number already
            $client = $this->clientAssembler->assembleFromLayDeputyshipDto($dto);
            $isNewClient = true;
        }

        return [
            'client' => $client,
            'existingReport' => $clientMatch->report,
            'reportTypeWasChangedFrom' => $clientMatch->reportTypeWasChangedFrom,
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

        return $newReport;
    }

    private function getEntityDetails(
        array $clientHandleResult,
        array $deputyUids,
        Report $report,
        LayDeputyshipDto $layDeputyshipDto,
    ): array {
        /** @var Client $client */
        $client = $clientHandleResult['client'];

        return [
            // was a new client created for this row?
            'isNewClient' => $clientHandleResult['isNewClient'],

            // ID of the client used for this row
            'clientId' => $client->getId(),

            // case number of the client
            'clientCaseNumber' => $client->getCaseNumber(),

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
            'reportTypeWasChangedFrom' => $clientHandleResult['reportTypeWasChangedFrom'],

            // data from the CSV parsed into the DTO
            'dto.caseNumber' => $layDeputyshipDto->getCaseNumber(),
            'dto.deputyUid' => $layDeputyshipDto->getDeputyUid(),
            'dto.orderType' => $layDeputyshipDto->getOrderType(),
            'dto.typeOfReport' => $layDeputyshipDto->getTypeOfReport(),
            'dto.orderDate' => $layDeputyshipDto->getOrderDate(),
        ];
    }
}
