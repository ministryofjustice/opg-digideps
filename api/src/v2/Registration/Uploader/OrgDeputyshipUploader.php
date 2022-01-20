<?php

declare(strict_types=1);

namespace App\v2\Registration\Uploader;

use App\Entity\CasRec;
use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Factory\OrganisationFactory;
use App\Service\OrgService;
use App\v2\Assembler\ClientAssembler;
use App\v2\Assembler\NamedDeputyAssembler;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Throwable;

class OrgDeputyshipUploader
{
    private array $added = ['clients' => [], 'named_deputies' => [], 'reports' => [], 'organisations' => []];
    private array $updated = ['clients' => [], 'named_deputies' => [], 'reports' => [], 'organisations' => []];

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
     * @throws Exception
     */
    public function upload(array $deputyshipDtos)
    {
        $this->resetAdded();
        $this->resetUpdated();

        $uploadResults = ['errors' => []];

        foreach ($deputyshipDtos as $deputyshipDto) {
            try {
                $this->handleDtoErrors($deputyshipDto);
                $this->handleNamedDeputy($deputyshipDto);
                $this->handleOrganisation($deputyshipDto);
                $this->handleClient($deputyshipDto);
                $this->handleReport($deputyshipDto);
            } catch (Throwable $e) {
                $message = sprintf('Error for case %s: %s', $deputyshipDto->getCaseNumber(), $e->getMessage());
                $uploadResults['errors'][] = $message;
                continue;
            }
        }

        $this->removeDuplicateIds();

        $roleType = User::TYPE_PA;
        // DepAddr No column is missing from PA CSV uploads
        foreach ($deputyshipDtos as $deputyshipDto) {
            if (null != $deputyshipDto->getDeputyAddressNumber()) {
                $roleType = User::TYPE_PROF;
                break;
            }
        }

        $uploadResults['roleType'] = $roleType;
        $uploadResults['source'] = CasRec::CASREC_SOURCE;
        $uploadResults['added'] = $this->added;
        $uploadResults['updated'] = $this->updated;

        return $uploadResults;
    }

    private function handleNamedDeputy(OrgDeputyshipDto $dto)
    {
        $deputyNumber = $dto->getDeputyAddressNumber() ?
            sprintf('%s-%s', $dto->getDeputyNumber(), $dto->getDeputyAddressNumber()) :
            $dto->getDeputyNumber();

        /** @var NamedDeputy $namedDeputy */
        $namedDeputy = ($this->em->getRepository(NamedDeputy::class))->findOneBy(
            [
                'deputyNo' => $deputyNumber,
            ]
        );

        // Temporary fix to generate dep adr numbers for all deps - remove once CSVs run
        if (!is_null($namedDeputy) && $dto->getDeputyAddressNumber()) {
            $namedDeputy->setDepAddrNo($dto->getDeputyAddressNumber());
        }

        if (is_null($namedDeputy)) {
            $namedDeputy = $this->namedDeputyAssembler->assembleFromOrgDeputyshipDto($dto);

            $this->em->persist($namedDeputy);
            $this->em->flush();

            $this->added['named_deputies'][] = $namedDeputy->getId();
        } elseif ($namedDeputy->addressHasChanged($dto)) {
            $namedDeputy
                ->setAddress1($dto->getDeputyAddress1())
                ->setAddress2($dto->getDeputyAddress2())
                ->setAddress3($dto->getDeputyAddress3())
                ->setAddress4($dto->getDeputyAddress4())
                ->setAddress5($dto->getDeputyAddress5())
                ->setAddressPostcode($dto->getDeputyPostcode())
                ->setDepAddrNo($dto->getDeputyAddressNumber())
                ->setDeputyNo($deputyNumber);

            $this->em->persist($namedDeputy);
            $this->em->flush();

            $this->updated['named_deputies'][] = $namedDeputy->getId();
        }

        $this->namedDeputy = $namedDeputy;
    }

    private function handleOrganisation(OrgDeputyshipDto $dto)
    {
        $this->currentOrganisation = $foundOrganisation = ($this->em->getRepository(Organisation::class))->findByEmailIdentifier($dto->getDeputyEmail());

        if (is_null($foundOrganisation)) {
            $organisation = $this->orgFactory->createFromFullEmail(OrgService::DEFAULT_ORG_NAME, $dto->getDeputyEmail());
            $this->em->persist($organisation);
            $this->em->flush();

            $this->currentOrganisation = $organisation;

            $this->added['organisations'][] = $organisation->getId();
        }
    }

    private function handleClient(OrgDeputyshipDto $dto)
    {
        /** @var Client $client */
        $client = ($this->em->getRepository(Client::class))->findOneBy(['caseNumber' => $dto->getCaseNumber()]);

        if ($client instanceof Client && $client->hasLayDeputy()) {
            throw new RuntimeException('case number already used');
        }

        if (is_null($client)) {
            $client = $this->buildClientAndAssociateWithDeputyAndOrg($dto);

            $this->added['clients'][] = $dto->getCaseNumber();
        } else {
            if (is_null($client->getCourtDate())) {
                $client->setCourtDate($dto->getCourtDate());
                $this->updated['clients'][] = $client->getId();
            }

            if ($this->clientHasNewCourtOrder($client, $dto)) {
                // Discharge clients with a new court order
                // Look at adding audit logging for discharge to API side of app
                $client->setDeletedAt(new DateTime());
                $this->em->persist($client);
                $this->em->flush();

                $client = $this->buildClientAndAssociateWithDeputyAndOrg($dto);
                $this->added['clients'][] = $dto->getCaseNumber();
            }

            if ($this->clientHasSwitchedOrganisation($client)) {
                $this->currentOrganisation->addClient($client);
                $client->setOrganisation($this->currentOrganisation);

                $this->updated['clients'][] = $client->getId();
            }

            if ($this->clientHasNewNamedDeputy($client, $this->namedDeputy)) {
                $client->setNamedDeputy($this->namedDeputy);

                $this->updated['clients'][] = $client->getId();
            }
        }

        $this->em->persist($client);
        $this->em->flush();

        $this->client = $client;
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
            $client->getNamedDeputy()->getDeputyNo() !== $namedDeputy->getDeputyNo();
    }

    private function handleReport(OrgDeputyshipDto $dto)
    {
        $report = $this->client->getCurrentReport();

        if ($report) {
            if (!$report->getSubmitted() && empty($report->getUnSubmitDate())) {
                // Add audit logging for report type changing
                $report->setType($dto->getReportType());
            }

            if ($this->clientHasNewOrgAndNamedDeputy($this->client, $this->namedDeputy)) {
                $report = new Report(
                    $this->client,
                    $dto->getReportType(),
                    $dto->getReportStartDate(),
                    $dto->getReportEndDate()
                );

                $this->client->addReport($report);

                $this->added['reports'][] = $this->client->getCaseNumber().'-'.$dto->getReportEndDate()->format('Y-m-d');
            }
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
            throw new RuntimeException($errorMessage);
        }
    }

    private function removeDuplicateIds()
    {
        $this->added['named_deputies'] = array_unique($this->added['named_deputies']);
        $this->added['organisations'] = array_unique($this->added['organisations']);
        $this->added['clients'] = array_unique($this->added['clients']);
        $this->added['reports'] = array_unique($this->added['reports']);

        $this->updated['named_deputies'] = array_unique($this->updated['named_deputies']);
        $this->updated['organisations'] = array_unique($this->updated['organisations']);
        $this->updated['clients'] = array_unique($this->updated['clients']);
        $this->updated['reports'] = array_unique($this->updated['reports']);
    }
}
