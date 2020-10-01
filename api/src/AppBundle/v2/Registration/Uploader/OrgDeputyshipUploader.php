<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Organisation;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\Service\OrgService;
use AppBundle\Service\ReportUtils;
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

    public function __construct(EntityManagerInterface $em, OrganisationFactory $orgFactory)
    {
        $this->em = $em;
        $this->orgFactory = $orgFactory;
    }

    /**
     * @param OrgDeputyshipDto[] $deputyshipDtos
     * @param EntityManagerInterface $em
     * @return int[]
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
                    'firstname' => $deputyshipDto->getFirstname(),
                    'lastname' => $deputyshipDto->getLastname(),
                    'address1' => $deputyshipDto->getDeputyAddress1(),
                    'addressPostcode' => $deputyshipDto->getDeputyPostCode()
                ]
            );

            if (is_null($namedDeputy)) {
                $namedDeputy = (new NamedDeputy())
                    ->setEmail1($deputyshipDto->getDeputyEmail())
                    ->setDeputyNo($deputyshipDto->getDeputyNumber())
                    ->setFirstname($deputyshipDto->getFirstname())
                    ->setLastname($deputyshipDto->getLastname())
                    ->setAddress1($deputyshipDto->getDeputyAddress1())
                    ->setAddressPostcode($deputyshipDto->getDeputyPostcode());

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
                $client = new Client();

                $client->setCaseNumber($deputyshipDto->getCaseNumber());
                $client->setFirstname($deputyshipDto->getClientFirstname());
                $client->setLastname($deputyshipDto->getClientLastname());

                if (!empty($deputyshipDto->getClientAddress1())) {
                    $client->setAddress($deputyshipDto->getClientAddress1());
                }

                if (!empty($deputyshipDto->getClientAddress2())) {
                    $client->setAddress2($deputyshipDto->getClientAddress2());
                }

                if (!empty($deputyshipDto->getClientAddress3())) {
                    $client->setCounty($deputyshipDto->getClientAddress3());
                }

                if (!empty($deputyshipDto->getClientPostCode())) {
                    $client->setPostcode($deputyshipDto->getClientPostCode());
                    $client->setCountry('GB'); //postcode given means a UK address is given
                }

                if (!empty($deputyshipDto->getClientDateOfBirth())) {
                    $client->setDateOfBirth($deputyshipDto->getClientDateOfBirth());
                }

                $client->setNamedDeputy($namedDeputy);

                if (!is_null($this->currentOrganisation)) {
                    $this->currentOrganisation->addClient($client);
                    $client->setOrganisation($this->currentOrganisation);
                }

                $added['clients'][] = $deputyshipDto->getCaseNumber();

                $this->em->persist($client);
                $this->em->flush();
            } else {
                // Updating court date to account for updates in casrec
                $client->setCourtDate($deputyshipDto->getCourtDate());

                if ($client->getOrganisation() === $this->currentOrganisation) {
                    $client->setNamedDeputy($namedDeputy);
                }

                $this->em->persist($client);
                $this->em->flush();
            }
        }

        $uploadResults['added'] = $added;
        return $uploadResults;
    }
}
