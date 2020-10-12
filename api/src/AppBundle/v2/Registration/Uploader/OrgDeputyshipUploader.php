<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Report\Report;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\Service\OrgService;
use AppBundle\v2\Assembler\ClientAssembler;
use AppBundle\v2\Assembler\NamedDeputyAssembler;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class OrgDeputyshipUploader
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var OrganisationFactory */
    private $orgFactory;

    /** @var Organisation|null */
    private $currentOrganisation;

    /** @var ClientAssembler */
    private $clientAssembler;

    /** @var NamedDeputyAssembler */
    private $namedDeputyAssembler;

    /** @var array[] */
    private $added;

    /** @var NamedDeputy|null */
    private $namedDeputy;

    /** @var Client|null */
    private $client;

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

        $this->added = ['clients' => [], 'named_deputies' => [], 'reports' => [], 'organisations' => []];
        $this->namedDeputy = null;
        $this->client = null;
    }

    /**
     * @param OrgDeputyshipDto[] $deputyshipDtos
     * @return array
     * @throws \Exception
     */
    public function upload(array $deputyshipDtos)
    {
        $this->resetAdded();

        $uploadResults = ['errors' => []];

        foreach ($deputyshipDtos as $deputyshipDto) {
            try {
                $this->handleNamedDeputy($deputyshipDto);
                $this->handleOrganisation($deputyshipDto);
                $this->handleClient($deputyshipDto);
                $this->handleReport($deputyshipDto);
            } catch (\Throwable $e) {
                $message = sprintf('Error for case "%s": %s', $deputyshipDto->getCaseNumber(), $e->getMessage());
                $uploadResults['errors'][] = $message;
                continue;
            }
        }

        $uploadResults['added'] = $this->added;
        return $uploadResults;
    }

    private function handleNamedDeputy(OrgDeputyshipDto $dto)
    {
        if (empty($dto->getDeputyEmail())) {
            throw new RuntimeException('deputy email missing');
        }

        if (empty($dto->getDeputyFirstname())) {
            throw new RuntimeException('deputy first name missing');
        }

        $namedDeputy = ($this->em->getRepository(NamedDeputy::class))->findOneBy(
            [
                'email1' => $dto->getDeputyEmail(),
                'deputyNo' => $dto->getDeputyNumber(),
                'firstname' => $dto->getDeputyFirstname(),
                'lastname' => $dto->getDeputyLastname(),
                'address1' => $dto->getDeputyAddress1(),
                'addressPostcode' => $dto->getDeputyPostCode()
            ]
        );

        if (is_null($namedDeputy)) {
            $namedDeputy = $this->namedDeputyAssembler->assembleFromOrgDeputyshipDto($dto);

            $this->em->persist($namedDeputy);
            $this->em->flush();

            $this->added['named_deputies'][] = $namedDeputy->getId();
        }

        $this->namedDeputy = $namedDeputy;
    }

    private function handleOrganisation(OrgDeputyshipDto $dto)
    {
        $orgDomainIdentifier = explode('@', $dto->getDeputyEmail())[1];
        $this->currentOrganisation = $foundOrganisation = ($this->em->getRepository(Organisation::class))->findOneBy(['emailIdentifier' => $orgDomainIdentifier]);

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
            $client = $this->clientAssembler->assembleFromOrgDeputyshipDto($dto);
            $client->setNamedDeputy($this->namedDeputy);

            if (!is_null($this->currentOrganisation)) {
                $this->currentOrganisation->addClient($client);
                $client->setOrganisation($this->currentOrganisation);
            }

            $this->added['clients'][] = $dto->getCaseNumber();
        } else {
            $client->setCourtDate($dto->getCourtDate());

            if (!$this->clientHasSwitchedOrganisation($client) && $this->clientHasNewNamedDeputy($client, $this->namedDeputy)) {
                $client->setNamedDeputy($this->namedDeputy);
            }
        }

        $this->em->persist($client);
        $this->em->flush();

        $this->client = $client;
    }

    /**
     * Returns true if clients organisation has changed
     *
     * @param Client $client
     * @return bool
     */
    private function clientHasSwitchedOrganisation(Client $client)
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

    /**
     * @param Client $client
     * @param NamedDeputy $namedDeputy
     * @return bool
     */
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
        } else {
            $report = new Report(
                $this->client,
                $dto->getReportType(),
                $dto->getReportStartDate(),
                $dto->getReportEndDate()
            );

            $this->client->addReport($report);

            $this->added['reports'][] = $this->client->getCaseNumber() . '-' . $dto->getReportEndDate()->format('Y-m-d');
        }

        $this->em->persist($report);
        $this->em->flush();
    }

    private function resetAdded()
    {
        $this->added = ['clients' => [], 'named_deputies' => [], 'reports' => [], 'organisations' => []];
    }
}
