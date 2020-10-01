<?php declare(strict_types=1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\NamedDeputy;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
use Doctrine\ORM\EntityRepository;

class NamedDeputyRepository extends EntityRepository
{
    public function findOrCreateByOrgDeputyshipDto(OrgDeputyshipDto $dto)
    {
        $found = $this->findOneBy(['email1' => $dto->getDeputyEmail()]);

        if ($found) {
            return $found;
        }



        return $namedDeputy;
    }
}
