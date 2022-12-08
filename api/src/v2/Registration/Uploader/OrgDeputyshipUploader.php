<?php

declare(strict_types=1);

namespace App\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Exception\ClientIsArchivedException;
use App\Factory\OrganisationFactory;
use App\Service\OrgService;
use App\v2\Assembler\ClientAssembler;
use App\v2\Assembler\NamedDeputyAssembler;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class OrgDeputyshipUploader
{
    private array $added = ['clients' => [], 'named_deputies' => [], 'reports' => [], 'organisations' => []];
    private array $updated = ['clients' => [], 'named_deputies' => [], 'reports' => [], 'organisations' => []];
    private array $changeOrg = [];

    private ?Organisation $currentOrganisation = null;
    private ?NamedDeputy $namedDeputy = null;
    private ?Client $client = null;

    private EntityManagerInterface $em;
    private OrganisationFactory $orgFactory;
    private NamedDeputyAssembler $namedDeputyAssembler;
    private ClientAssembler $clientAssembler;

    public function __construct(
        EntityManagerInterface $em,
        OrganisationFactory $orgFactory,
        ClientAssembler $clientAssembler,
        NamedDeputyAssembler $namedDeputyAssembler
    ) {
        $this->em = $em;
        $this->orgFactory = $orgFactory;
        $this->clientAssembler = $clientAssembler;
        $this->namedDeputyAssembler = $namedDeputyAssembler;
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
        $this->resetAdded();
        $this->resetUpdated();

        $uploadResults = ['errors' => [], 'skipped' => 0];

        foreach ($deputyshipDtos as $deputyshipDto) {
            try {
                $this->handleDtoErrors($deputyshipDto);

                $this->client = $this->em->getRepository(Client::class)->findByCaseNumber($deputyshipDto->getCaseNumber());

                $this->skipArchivedClients();
                $this->handleNamedDeputy($deputyshipDto);
                $this->handleOrganisation($deputyshipDto);
                $this->handleClient($deputyshipDto);
                $this->handleReport($deputyshipDto);
            } catch (ClientIsArchivedException $e) {
                ++$uploadResults['skipped'];
                continue;
            } catch (\Throwable $e) {
                $message = sprintf('Error for case %s: %s', $deputyshipDto->getCaseNumber(), $e->getMessage());
                $uploadResults['errors'][] = $message;
                continue;
            }
        }

        $this->removeDuplicateIds();

        $uploadResults['added'] = $this->added;
        $uploadResults['updated'] = $this->updated;
        $uploadResults['changeOrg'] = $this->changeOrg;

        return $uploadResults;
    }

    private function handleNamedDeputy(OrgDeputyshipDto $dto)
    {
        /** @var NamedDeputy $namedDeputy */
        $namedDeputy = $this->em->getRepository(NamedDeputy::class)->findOneBy(
            [
                'deputyUid' => $dto->getDeputyUid(),
            ]
        );

        if (is_null($namedDeputy)) {
            $namedDeputy = $this->namedDeputyAssembler->assembleFromOrgDeputyshipDto($dto);

            $this->em->persist($namedDeputy);
            $this->em->flush();

            $this->added['named_deputies'][] = $namedDeputy->getId();
        }

        if ($namedDeputy->getDeputyUid() === $dto->getDeputyUid()) {
            if ($namedDeputy->addressHasChanged($dto)) {
                $namedDeputy
                    ->setAddress1($dto->getDeputyAddress1())
                    ->setAddress2($dto->getDeputyAddress2())
                    ->setAddress3($dto->getDeputyAddress3())
                    ->setAddress4($dto->getDeputyAddress4())
                    ->setAddress5($dto->getDeputyAddress5())
                    ->setAddressPostcode($dto->getDeputyPostcode());

                $this->em->persist($namedDeputy);
                $this->em->flush();

                $this->updated['named_deputies'][] = $namedDeputy->getId();
            }

            if ($namedDeputy->nameHasChanged($dto)) {
                if ($dto->deputyIsAnOrganisation()) {
                    $namedDeputy->setFirstname($dto->getOrganisationName());
                    $namedDeputy->setLastname('');
                } else {
                    $namedDeputy->setFirstname($dto->getDeputyFirstname());
                    $namedDeputy->setLastname($dto->getDeputyLastname());
                }

                $this->em->persist($namedDeputy);
                $this->em->flush();

                $this->updated['named_deputies'][] = $namedDeputy->getId();
            }

            if ($namedDeputy->emailHasChanged($dto)) {
                $namedDeputy->setEmail1($dto->getDeputyEmail());

                $this->em->persist($namedDeputy);
                $this->em->flush();

                $this->updated['named_deputies'][] = $namedDeputy->getId();
            }
        }

        $this->namedDeputy = $namedDeputy;
    }

    private function handleOrganisation(OrgDeputyshipDto $dto)
    {
        $this->currentOrganisation = $foundOrganisation = $this->em->getRepository(Organisation::class)->findByEmailIdentifier($dto->getDeputyEmail());

        if (is_null($foundOrganisation)) {
            $organisation = $this->orgFactory->createFromFullEmail(OrgService::DEFAULT_ORG_NAME, $dto->getDeputyEmail());
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
//            if ($this->clientHasNewNamedDeputy($this->client, $this->namedDeputy)) {
//                $this->client->setNamedDeputy($this->namedDeputy);
//
//                $this->updated['clients'][] = $this->client->getId();
//            }

            // Temp fix for deputies that have switched organisation and taken the client with them
            if (!$this->clientHasNewCourtOrder($this->client, $dto)) {
                if ($this->clientHasSwitchedOrganisation($this->client)) {
                    if (!$this->clientHasNewNamedDeputy($this->client, $this->namedDeputy)) {
                        // Track clients original organisation for audit logging before it is updated
                        $tempArray = ['old_organisation' => $this->client->getOrganisation()->getId()];

                        $this->currentOrganisation->addClient($this->client);
                        $this->client->setOrganisation($this->currentOrganisation);

                        $this->updated['clients'][] = $this->client->getId();

                        // Track clients for audit logging purposes
                        $tempArray[] = ['client_id' => $this->client->getId()];
                        $tempArray[] = ['deputy_id' => $this->client->getNamedDeputy()->getId()];
                        $tempArray[] = ['new_organisation' => $this->client->getOrganisation()->getId()];

                        $changeOrg[] = $tempArray;
                    }
                }
            }

            // Temp fix for clients who have new named deputy in same organisation
            if (!$this->clientHasSwitchedOrganisation($this->client)) {
                if ($this->clientHasNewNamedDeputy($this->client, $this->namedDeputy)) {
                    $this->client->setNamedDeputy($this->namedDeputy);

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

        $client->setNamedDeputy($this->namedDeputy);

        if (!is_null($this->currentOrganisation)) {
            $this->currentOrganisation->addClient($client);
            $client->setOrganisation($this->currentOrganisation);
        }

        return $client;
    }

    private function clientHasNewCourtOrder(Client $client, OrgDeputyshipDto $dto): bool
    {
        return $client->getCourtDate() &&
            $client->getCaseNumber() === $dto->getCaseNumber() &&
            $client->getCourtDate()->format('Ymd') !== $dto->getCourtDate()->format('Ymd');
    }

    private function clientHasNewOrgAndNamedDeputy(Client $client, NamedDeputy $namedDeputy): bool
    {
        return $this->clientHasSwitchedOrganisation($client) && $this->clientHasNewNamedDeputy($client, $namedDeputy);
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

    private function clientHasNewNamedDeputy(Client $client, NamedDeputy $namedDeputy): bool
    {
        return
            null === $client->getNamedDeputy() ||
            $client->getNamedDeputy()->getDeputyUid() !== $namedDeputy->getDeputyUid();
    }

    private function handleReport(OrgDeputyshipDto $dto)
    {
        $report = $this->client->getCurrentReport();

        if ($report) {
            if (!$report->getSubmitted() && empty($report->getUnSubmitDate())) {
                if (OrgDeputyshipDto::DUAL_TYPE == $dto->getHybrid()) {
                    if ($this->client->getNamedDeputy()->getDeputyUid() == $dto->getDeputyUid()) {
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

//            if ($this->clientHasNewOrgAndNamedDeputy($this->client, $this->namedDeputy)) {
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

    private function resetAdded()
    {
        $this->added = ['clients' => [], 'named_deputies' => [], 'reports' => [], 'organisations' => []];
    }

    private function resetUpdated()
    {
        $this->updated = ['clients' => [], 'named_deputies' => [], 'reports' => [], 'organisations' => []];
    }

    private function handleDtoErrors(OrgDeputyshipDto $dto)
    {
        $missingDataTypes = [];

        if (empty($dto->getReportStartDate())) {
            $missingDataTypes[] = 'Report Start Date';
        }

        if (empty($dto->getReportEndDate())) {
            $missingDataTypes[] = 'Report End Date';
        }

        if (empty($dto->getCourtDate())) {
            $missingDataTypes[] = 'Court Date';
        }

        if (empty($dto->getDeputyEmail())) {
            $missingDataTypes[] = 'Deputy Email';
        }

        if (!empty($missingDataTypes)) {
            $errorMessage = sprintf('Missing data to upload row: %s', implode(', ', $missingDataTypes));
            throw new \RuntimeException($errorMessage);
        }
    }

    private function removeDuplicateIds()
    {
        $this->added['named_deputies'] = array_unique($this->added['named_deputies']);
        $this->added['organisations'] = array_unique($this->added['organisations'], SORT_REGULAR);
        $this->added['clients'] = array_unique($this->added['clients']);
        $this->added['reports'] = array_unique($this->added['reports']);

        $this->updated['named_deputies'] = array_unique($this->updated['named_deputies']);
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
