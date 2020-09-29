<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Repository\NamedDeputyRepository;
use AppBundle\v2\Registration\Assembler\CasRecToOrgDeputyshipDtoAssembler;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
use Doctrine\ORM\EntityManagerInterface;

class OrgDeputyshipUploader
{
    /**
     * @param OrgDeputyshipDto[] $deputyshipDtos
     * @param EntityManagerInterface $em
     * @return int[]
     * @throws \Exception
     */
    public function upload(array $deputyshipDtos, EntityManagerInterface $em)
    {
        $uploadResults = ['errors' => 0];
        $added = ['clients' => [], 'discharged_clients' => [], 'named_deputies' => [], 'reports' => []];

        foreach ($deputyshipDtos as $deputyshipDto) {
            if (!$deputyshipDto->isValid()) {
                $uploadResults['errors']++;
                continue;
            }

            $foundNamedDeputy = ($em->getRepository(NamedDeputy::class))->findOneBy(['email1' => $deputyshipDto->getEmail()]);

            if (is_null($foundNamedDeputy)) {
                $namedDeputy = (new NamedDeputy())
                    ->setEmail1($deputyshipDto->getEmail())
                    ->setDeputyNo($deputyshipDto->getDeputyNumber())
                    ->setFirstname($deputyshipDto->getFirstname())
                    ->setLastname($deputyshipDto->getLastname());

                $em->persist($namedDeputy);
                $em->flush();

                $added['named_deputies'][] = $namedDeputy->getId();
            }

            $added['clients'][] = random_int(10000000, 99999999);
        }

        $uploadResults['added'] = $added;
        return $uploadResults;
    }
}
