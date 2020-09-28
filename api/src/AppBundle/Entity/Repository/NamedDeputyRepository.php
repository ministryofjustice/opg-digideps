<?php declare(strict_types=1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\NamedDeputy;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
use Doctrine\ORM\EntityRepository;

class NamedDeputyRepository extends EntityRepository
{
    public function findOrCreateByOrgDeputyshipDto(OrgDeputyshipDto $dto)
    {
        $found = $this->findOneBy(['email1' => $dto->getEmail()]);

        if ($found) {
            return $found;
        }

        $namedDeputy = (new NamedDeputy())
            ->setEmail1($dto->getEmail())
            ->setDeputyNo($dto->getDeputyNumber())
            ->setFirstname($dto->getFirstname())
            ->setLastname($dto->getLastname());

        $this->_em->persist($namedDeputy);
        $this->_em->flush();

        return $namedDeputy;
    }
}
