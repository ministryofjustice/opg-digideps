<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Organisation;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\Service\OrgService;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
use Doctrine\ORM\EntityManagerInterface;

class OrgDeputyshipUploader
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var OrganisationFactory
     */
    private $orgFactory;

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
        $added = ['clients' => [], 'discharged_clients' => [], 'named_deputies' => [], 'reports' => []];

        foreach ($deputyshipDtos as $deputyshipDto) {
            if (!$deputyshipDto->isValid()) {
                $uploadResults['errors']++;
                continue;
            }

            $foundNamedDeputy = ($this->em->getRepository(NamedDeputy::class))->findOneBy(['email1' => $deputyshipDto->getEmail()]);

            if (is_null($foundNamedDeputy)) {
                $namedDeputy = (new NamedDeputy())
                    ->setEmail1($deputyshipDto->getEmail())
                    ->setDeputyNo($deputyshipDto->getDeputyNumber())
                    ->setFirstname($deputyshipDto->getFirstname())
                    ->setLastname($deputyshipDto->getLastname());

                $this->em->persist($namedDeputy);
                $this->em->flush();

                $added['named_deputies'][] = $namedDeputy->getId();
            }

            $orgDomainIdentifier = explode('@', $deputyshipDto->getEmail())[1];
            $foundOrganisation = ($this->em->getRepository(Organisation::class))->findOneBy(['emailIdentifier' => $orgDomainIdentifier]);

            if (is_null($foundOrganisation)) {
                $organisation = $this->orgFactory->createFromFullEmail(OrgService::DEFAULT_ORG_NAME, $deputyshipDto->getEmail());
                $this->em->persist($organisation);
                $this->em->flush();

                $added['organisations'][] = $organisation->getId();
            }

            $added['clients'][] = random_int(10000000, 99999999);
        }

        $uploadResults['added'] = $added;
        return $uploadResults;
    }
}
