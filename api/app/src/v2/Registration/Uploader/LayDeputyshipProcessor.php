<?php

declare(strict_types=1);

namespace App\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\v2\Assembler\ClientAssembler;
use App\v2\Registration\DTO\LayDeputyshipDto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;

/**
 * Processor for an individual row in the lay CSV file, represented as a lay deputyship DTO object.
 */
class LayDeputyshipProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClientAssembler $clientAssembler,
        private readonly LayClientMatcher $clientMatcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return array
     *               [
     *               'entityDetails' => [] || [<details about entities added>],
     *               'message' => '<explanation of what happened with the row>,
     *               'error' => null || '<error which occurred while processing the row>'
     *               ]
     */
    public function processLayDeputyship(LayDeputyshipDto $layDeputyshipDto, bool $multiclientApplyDbChanges = true): array
    {
        $entityDetails = [];
        $message = null;
        $errorMessage = null;

        try {
            $this->em->beginTransaction();

            $client = $this->handleNewClient($layDeputyshipDto);

            if (is_null($client)) {
                return [
                    'entityDetails' => $entityDetails,
                    'message' => 'Found a potential co-deputy or dual; will not create new multi-client entities',
                    'error' => $errorMessage,
                ];
            }

            $user = $this->findUser($layDeputyshipDto->getDeputyUid());
            $client->addUser($user);

            $report = $this->handleNewReport($layDeputyshipDto, $client);
            $report->setClient($client);

            $this->em->persist($report);
            $this->em->persist($client);

            if ($multiclientApplyDbChanges) {
                $this->em->flush();
                $this->em->commit();
            } else {
                $this->em->rollback();
            }

            $this->em->clear();

            // log db changes
            $entityDetails = $this->getEntityDetails(
                $client,
                $report,
                $layDeputyshipDto
            );

            $message = 'Added new client and report to multi-client deputy';
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
            'message' => $message,
            'error' => $errorMessage,
        ];
    }

    /*
     * Assumption is that we can just associate all clients to the existing user in the dd_user table.
     * If there is no user in this table, we won't get any potential new clients as we don't have anything to attach
     * them to.
     */
    private function findUser(string $deputyUid): User
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

    // returns null if a new client is not created
    private function handleNewClient(LayDeputyshipDto $dto): ?Client
    {
        $clientMatch = $this->clientMatcher->matchDto($dto);

        // Only create a new client if this is a multi-client and there is not an existing active client for this case
        // number.
        //
        // * If there is a compatible report (i.e. we found a candidate client), creating a client here would create
        //   an unnecessary duplicate client when a co-deputy would be more appropriate
        // * If there is no compatible report (i.e. no candidate client), but there is an active client,
        //   creating another client here would turn this deputy into a dual on the case, which we don't want to do
        //   automatically
        $client = null;
        if (!$clientMatch->activeClientExistsForCase) {
            $client = $this->clientAssembler->assembleFromLayDeputyshipDto($dto);
        }

        return $client;
    }

    // we only call this if we created a new client; otherwise we are reusing an existing client and report
    private function handleNewReport(LayDeputyshipDto $dto, Client $newClient): Report
    {
        $determinedReportType = PreRegistration::getReportTypeByOrderType($dto->getTypeOfReport(), $dto->getOrderType(), PreRegistration::REALM_LAY);

        $reportStartDate = clone $dto->getOrderDate();
        $reportEndDate = clone $reportStartDate;
        $reportEndDate->add(new \DateInterval('P364D'));

        return new Report(
            $newClient,
            $determinedReportType,
            $reportStartDate,
            $reportEndDate,
            false
        );
    }

    // note that the reportId and clientId will be null if this is a dry run (database changes are not applied)
    private function getEntityDetails(
        Client $client,
        Report $report,
        LayDeputyshipDto $layDeputyshipDto,
    ): array {
        $deputyUids = array_map(
            function ($user) { return $user->getDeputyUid(); },
            iterator_to_array($client->getUsers())
        );

        return [
            // ID of the client used for this row
            'clientId' => $client->getId(),

            // case number of the client
            'clientCaseNumber' => $client->getCaseNumber(),

            // UIDs of deputies associated with the client (including the deputy from the row)
            'clientDeputyUids' => $deputyUids,

            // ID of the report used for this row
            'reportId' => $report->getId(),

            // type of the report used for this row; '102', '103', '104', '102-4', '103-4'
            'reportType' => $report->getType(),

            // data from the CSV parsed into the DTO
            'dto.caseNumber' => $layDeputyshipDto->getCaseNumber(),
            'dto.deputyUid' => $layDeputyshipDto->getDeputyUid(),
            'dto.orderType' => $layDeputyshipDto->getOrderType(),
            'dto.typeOfReport' => $layDeputyshipDto->getTypeOfReport(),
            'dto.orderDate' => $layDeputyshipDto->getOrderDate(),
        ];
    }
}
