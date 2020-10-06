<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\NamedDeputyRepository;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\Service\OrgService;
use AppBundle\Service\ReportUtils;
use AppBundle\v2\Assembler\ClientAssembler;
use AppBundle\v2\Assembler\NamedDeputyAssembler;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
use Doctrine\ORM\EntityManagerInterface;

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
     * @return array
     * @throws \Exception
     */
    public function upload(array $deputyshipDtos)
    {
        $uploadResults = ['errors' => 0];
        $added = ['clients' => [], 'discharged_clients' => [], 'named_deputies' => [], 'reports' => [], 'organisations' => []];

        foreach ($deputyshipDtos as $deputyshipDto) {
            if (!$deputyshipDto->isValid()) {
                $uploadResults['errors']++;
                continue;
            }

            $namedDeputy = ($this->em->getRepository(NamedDeputy::class))->findOneBy(
                [
                    'email1' => $deputyshipDto->getDeputyEmail(),
                    'deputyNo' => $deputyshipDto->getDeputyNumber(),
                    'firstname' => $deputyshipDto->getDeputyFirstname(),
                    'lastname' => $deputyshipDto->getDeputyLastname(),
                    'address1' => $deputyshipDto->getDeputyAddress1(),
                    'addressPostcode' => $deputyshipDto->getDeputyPostCode()
                ]
            );

            if (is_null($namedDeputy)) {
                $namedDeputy = $this->namedDeputyAssembler->assembleFromOrgDeputyshipDto($deputyshipDto);

                $this->em->persist($namedDeputy);
                $this->em->flush();

                $added['named_deputies'][] = $namedDeputy->getId();
            }

            $orgDomainIdentifier = explode('@', $deputyshipDto->getDeputyEmail())[1];
            $this->currentOrganisation = $foundOrganisation = ($this->em->getRepository(Organisation::class))->findOneBy(['emailIdentifier' => $orgDomainIdentifier]);

            if (is_null($foundOrganisation)) {
                $organisation = $this->orgFactory->createFromFullEmail(OrgService::DEFAULT_ORG_NAME, $deputyshipDto->getDeputyEmail());
                $this->em->persist($organisation);
                $this->em->flush();

                $this->currentOrganisation = $organisation;

                $added['organisations'][] = $organisation->getId();
            }

            $client = ($this->em->getRepository(Client::class))->findOneBy(['caseNumber' => $deputyshipDto->getCaseNumber()]);

            if (is_null($client)) {
                $client = $this->clientAssembler->assembleFromOrgDeputyshipDto($deputyshipDto);
                $client->setNamedDeputy($namedDeputy);

                if (!is_null($this->currentOrganisation)) {
                    $this->currentOrganisation->addClient($client);
                    $client->setOrganisation($this->currentOrganisation);
                }

                $added['clients'][] = $deputyshipDto->getCaseNumber();
            } else {
                $client->setCourtDate($deputyshipDto->getCourtDate());

                if ($client->getOrganisation() === $this->currentOrganisation) {
                    $client->setNamedDeputy($namedDeputy);
                }
            }

            $this->em->persist($client);
            $this->em->flush();

            $report = $client->getCurrentReport();

            if ($report) {
                if ($report->getType() != $deputyshipDto->getReportType() && !$report->getSubmitted() && empty($report->getUnSubmitDate())) {
                    // Add audit logging for report type changing
                    $report->setType($deputyshipDto->getReportType());
                }
            } else {
                $report = new Report(
                    $client,
                    $deputyshipDto->getReportType(),
                    $deputyshipDto->getReportStartDate(),
                    $deputyshipDto->getReportEndDate()
                );

                $client->addReport($report);
            }

            $this->em->persist($report);
            $this->em->flush();

            $added['reports'][] = $client->getCaseNumber() . '-' . $deputyshipDto->getReportEndDate()->format('Y-m-d');
        }

        $uploadResults['added'] = $added;
        return $uploadResults;
    }
}
