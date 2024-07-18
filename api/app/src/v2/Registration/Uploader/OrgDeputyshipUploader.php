<?php

declare(strict_types=1);

namespace App\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Exception\ClientIsArchivedException;
use App\Factory\OrganisationFactory;
use App\Service\OrgService;
use App\v2\Assembler\ClientAssembler;
use App\v2\Assembler\DeputyAssembler;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class OrgDeputyshipUploader
{
    private array $added = ['clients' => [], 'deputies' => [], 'reports' => [], 'organisations' => []];
    private array $updated = ['clients' => [], 'deputies' => [], 'reports' => [], 'organisations' => []];
    private array $changeOrg = [];

    private ?Organisation $currentOrganisation = null;
    private ?Deputy $deputy = null;
    private ?Client $client = null;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrganisationFactory $orgFactory,
        private readonly ClientAssembler $clientAssembler,
        private readonly DeputyAssembler $deputyAssembler,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param OrgDeputyshipDto[] $deputyshipDtos
     *
     * @return array
     *
     * @throws \Exception
     */
    public function upload(array $deputyshipDtos)
    {
        $this->resetDeputyshipUploaderObjects();
        $this->em->clear();

        $uploadResults = [
            'added' => [],
            'updated' => [],
            'changeOrg' => [],
            'skipped' => 0,
            'errors' => [
                'count' => 0,
                'messages' => [],
            ],
        ];

        foreach ($deputyshipDtos as $deputyshipDto) {
            try {
                $this->handleDtoErrors($deputyshipDto);

                $this->client = $this->em->getRepository(Client::class)->findByCaseNumber($deputyshipDto->getCaseNumber());

                $this->skipArchivedClients();
                $this->handleDeputy($deputyshipDto);
                $this->handleOrganisation($deputyshipDto);
                $this->handleClient($deputyshipDto);
                $this->handleReport($deputyshipDto);
            } catch (ClientIsArchivedException $e) {
                ++$uploadResults['skipped'];
                continue;
            } catch (\Throwable $e) {
                $message = str_replace(PHP_EOL, '', $e->getMessage());
                $message = sprintf('Error for case %s: %s', $deputyshipDto->getCaseNumber(), $message);

                $this->logger->notice($message);
                $uploadResults['errors']['messages'][] = $message;

                ++$uploadResults['errors']['count'];
                continue;
            }
        }

        $this->removeDuplicateIds();

        $uploadResults['added'] = $this->added;
        $uploadResults['updated'] = $this->updated;
        $uploadResults['changeOrg'] = $this->changeOrg;

        return $uploadResults;
    }

    private function handleDeputy(OrgDeputyshipDto $dto)
    {
        /** @var Deputy $deputy */
        $deputy = $this->em->getRepository(Deputy::class)->findOneBy(
            [
                'deputyUid' => $dto->getDeputyUid(),
            ]
        );

        if (is_null($deputy)) {
            $deputy = $this->deputyAssembler->assembleFromOrgDeputyshipDto($dto);

            $this->em->persist($deputy);
            $this->em->flush();

            $this->added['deputies'][] = $deputy->getId();
        } elseif ($deputy->getDeputyUid() === $dto->getDeputyUid()) {
            $updated = false;

            if ($deputy->addressHasChanged($dto)) {
                $deputy
                    ->setAddress1($dto->getDeputyAddress1())
                    ->setAddress2($dto->getDeputyAddress2())
                    ->setAddress3($dto->getDeputyAddress3())
                    ->setAddress4($dto->getDeputyAddress4())
                    ->setAddress5($dto->getDeputyAddress5())
                    ->setAddressPostcode($dto->getDeputyPostcode());

                $updated = true;
            }

            if ($deputy->nameHasChanged($dto)) {
                if ($dto->deputyIsAnOrganisation()) {
                    $deputy->setFirstname($dto->getOrganisationName());
                    $deputy->setLastname('');
                } else {
                    $deputy->setFirstname($dto->getDeputyFirstname());
                    $deputy->setLastname($dto->getDeputyLastname());
                }

                $updated = true;
            }

            if ($deputy->emailHasChanged($dto)) {
                $deputy->setEmail1($dto->getDeputyEmail());

                $updated = true;
            }

            if ($updated) {
                $this->em->persist($deputy);
                $this->em->flush();

                $this->updated['deputies'][] = $deputy->getId();
            }
        }

        $this->deputy = $deputy;
    }

    private function handleOrganisation(OrgDeputyshipDto $dto)
    {
        $this->currentOrganisation = $foundOrganisation = $this->em->getRepository(Organisation::class)->findByEmailIdentifier($dto->getDeputyEmail());

        if (is_null($foundOrganisation)) {
            $orgName = empty($dto->getOrganisationName()) ? OrgService::DEFAULT_ORG_NAME : $dto->getOrganisationName();
            $organisation = $this->orgFactory->createFromFullEmail($orgName, $dto->getDeputyEmail());
            $this->em->persist($organisation);
            $this->em->flush();

            $this->currentOrganisation = $organisation;

            $this->added['organisations'][] = $organisation;
        }
    }

    private function handleClient(OrgDeputyshipDto $dto)
    {
        if ($this->client instanceof Client && $this->client->hasLayDeputy()) {
            throw new \RuntimeException('case number already used');
        }

        if (is_null($this->client)) {
            $this->client = $this->buildClientAndAssociateWithDeputyAndOrg($dto);

            $this->added['clients'][] = $dto->getCaseNumber();
        } else {
            if (is_null($this->client->getCourtDate())) {
                $this->client->setCourtDate($dto->getCourtDate());
                $this->updated['clients'][] = $this->client->getId();
            }

            // TODO - Implement fix for https://opgtransform.atlassian.net/browse/DDPB-4350
            // TODO - Remove temporary fixes/workarounds after the above issue is fixed
            //            //Temp disabling until we can rely on Sirius data
            //            if ($this->clientHasNewCourtOrder($this->client, $dto)) {
            //                // Discharge clients with a new court order
            //                // Look at adding audit logging for discharge to API side of app
            //                $this->client->setDeletedAt(new DateTime());
            //                $this->em->persist($this->client);
            //                $this->em->flush();
            //
            //                $this->client = $this->buildClientAndAssociateWithDeputyAndOrg($dto);
            //                $this->added['clients'][] = $dto->getCaseNumber();
            //            }
            //
            //            if ($this->clientHasSwitchedOrganisation($this->client)) {
            //                $this->currentOrganisation->addClient($this->client);
            //                $this->client->setOrganisation($this->currentOrganisation);
            //
            //                $this->updated['clients'][] = $this->client->getId();
            //            }
            //
            //            if ($this->clientHasNewDeputy($this->client, $this->deputy)) {
            //                $this->client->setDeputy($this->deputy);
            //
            //                $this->updated['clients'][] = $this->client->getId();
            //            }

            // Temp fix for deputies that have switched organisation and taken the client with them
            if (!$this->clientHasNewCourtOrder($this->client, $dto)) {
                if ($this->clientHasSwitchedOrganisation($this->client)) {
                    if (!$this->clientHasNewDeputy($this->client, $this->deputy)) {
                        // Track clients original organisation for audit logging before it is updated
                        $tempArray = ['old_organisation' => $this->client->getOrganisation()->getId()];

                        $this->currentOrganisation->addClient($this->client);
                        $this->client->setOrganisation($this->currentOrganisation);

                        $this->updated['clients'][] = $this->client->getId();

                        // Track clients for audit logging purposes
                        $tempArray[] = ['client_id' => $this->client->getId()];
                        $tempArray[] = ['deputy_id' => $this->client->getDeputy()->getId()];
                        $tempArray[] = ['new_organisation' => $this->client->getOrganisation()->getId()];

                        $changeOrg[] = $tempArray;
                    }
                }
            }

            // Temp fix for clients who have new deputy in same organisation
            if (!$this->clientHasSwitchedOrganisation($this->client)) {
                if ($this->clientHasNewDeputy($this->client, $this->deputy) && OrgDeputyshipDto::DUAL_TYPE != $dto->getHybrid()) {
                    $this->client->setDeputy($this->deputy);

                    $this->updated['clients'][] = $this->client->getId();
                }
            }
        }

        $this->em->persist($this->client);
        $this->em->flush();
    }

    private function buildClientAndAssociateWithDeputyAndOrg(OrgDeputyshipDto $dto): Client
    {
        $client = $this->clientAssembler->assembleFromOrgDeputyshipDto($dto);

        $client->setDeputy($this->deputy);

        if (!is_null($this->currentOrganisation)) {
            $this->currentOrganisation->addClient($client);
            $client->setOrganisation($this->currentOrganisation);
        }

        return $client;
    }

    private function clientHasNewCourtOrder(Client $client, OrgDeputyshipDto $dto): bool
    {
        return $client->getCourtDate()
            && $client->getCaseNumber() === $dto->getCaseNumber()
            && $client->getCourtDate()->format('Ymd') !== $dto->getCourtDate()->format('Ymd');
    }

    private function clientHasNewOrgAndDeputy(Client $client, Deputy $deputy): bool
    {
        return $this->clientHasSwitchedOrganisation($client) && $this->clientHasNewDeputy($client, $deputy);
    }

    /**
     * Returns true if clients organisation has changed.
     */
    private function clientHasSwitchedOrganisation(Client $client): bool
    {
        if (
            $client->getOrganisation() instanceof Organisation
            && $this->currentOrganisation instanceof Organisation
            && $client->getOrganisation()->getId() !== $this->currentOrganisation->getId()
        ) {
            return true;
        }

        return false;
    }

    private function clientHasNewDeputy(Client $client, Deputy $deputy): bool
    {
        return
            null === $client->getDeputy()
            || $client->getDeputy()->getDeputyUid() !== $deputy->getDeputyUid();
    }

    private function handleReport(OrgDeputyshipDto $dto)
    {
        $report = $this->client->getCurrentReport();

        if ($report) {
            if (!$report->getSubmitted() && empty($report->getUnSubmitDate())) {
                if (OrgDeputyshipDto::DUAL_TYPE == $dto->getHybrid()) {
                    if ($this->client->getDeputy()->getDeputyUid() == $dto->getDeputyUid()) {
                        if ($report->getType() !== $dto->getReportType()) {
                            $report->setType($dto->getReportType());

                            $this->updated['reports'][] = $report->getId();
                        }
                    }
                } else {
                    if ($report->getType() !== $dto->getReportType()) {
                        // Add audit logging for report type changing
                        $report->setType($dto->getReportType());

                        $this->updated['reports'][] = $report->getId();
                    }
                }
            }

        //            if ($this->clientHasNewOrgAndDeputy($this->client, $this->deputy)) {
        //                $report = new Report(
        //                    $this->client,
        //                    $dto->getReportType(),
        //                    $dto->getReportStartDate(),
        //                    $dto->getReportEndDate()
        //                );
        //
        //                $this->client->addReport($report);
        //
        //                $this->added['reports'][] = $this->client->getCaseNumber().'-'.$dto->getReportEndDate()->format('Y-m-d');
        //            }
        } else {
            $report = new Report(
                $this->client,
                $dto->getReportType(),
                $dto->getReportStartDate(),
                $dto->getReportEndDate()
            );

            $this->client->addReport($report);

            $this->added['reports'][] = $this->client->getCaseNumber().'-'.$dto->getReportEndDate()->format('Y-m-d');
        }

        $this->em->persist($report);
        $this->em->flush();
    }

    private function resetDeputyshipUploaderObjects()
    {
        $this->added = ['clients' => [], 'deputies' => [], 'reports' => [], 'organisations' => []];
        $this->updated = ['clients' => [], 'deputies' => [], 'reports' => [], 'organisations' => []];
        $this->changeOrg = [];
        $this->currentOrganisation = null;
        $this->deputy = null;
        $this->client = null;
    }

    private function handleDtoErrors(OrgDeputyshipDto $dto)
    {
        $missingData = [];

        if (empty($dto->getReportEndDate())) {
            $missingData[] = 'LastReportDay';
        }

        if (empty($dto->getCourtDate())) {
            $missingData[] = 'MadeDate';
        }

        if (empty($dto->getDeputyEmail())) {
            $missingData[] = 'DeputyEmail';
        }

        if (!empty($missingData)) {
            $errorMessage = sprintf('Missing data to upload row: %s', implode(', ', $missingData));
            throw new \RuntimeException($errorMessage);
        }
    }

    private function removeDuplicateIds()
    {
        $this->added['deputies'] = array_unique($this->added['deputies']);
        $this->added['organisations'] = array_unique($this->added['organisations'], SORT_REGULAR);
        $this->added['clients'] = array_unique($this->added['clients']);
        $this->added['reports'] = array_unique($this->added['reports']);

        $this->updated['deputies'] = array_unique($this->updated['deputies']);
        $this->updated['organisations'] = array_unique($this->updated['organisations']);
        $this->updated['clients'] = array_unique($this->updated['clients']);
        $this->updated['reports'] = array_unique($this->updated['reports']);
    }

    private function skipArchivedClients()
    {
        if (!is_null($this->client) && !is_null($this->client->getArchivedAt())) {
            $message = sprintf(
                'Client with case number "%s" is archived. Skipping CSV row.',
                $this->client->getCaseNumber()
            );

            throw new ClientIsArchivedException($message);
        }
    }
}
