<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Organisation;

class OrganisationFactory
{
    public function __construct(private readonly array $sharedDomains)
    {
    }

    public function createFromFullEmail(string $name, string $email, bool $isActivated = false): Organisation
    {
        $email = strtolower($email);

        $domainArray = explode('@', $email);

        if (1 === count($domainArray) && !empty($domainArray[0])) {
            return $this->createFromEmailIdentifier($name, $domainArray[0], $isActivated);
        }

        if (2 === count($domainArray) && !empty($domainArray[1])) {
            $domain = $domainArray[1];
            $emailIdentifier = in_array($domain, $this->sharedDomains) ? $email : $domain;

            return $this->create($name, $emailIdentifier, $isActivated);
        }

        throw new \InvalidArgumentException(sprintf("Unable to create organisation from 'emailIdentifier': '%s'", $email));
    }

    public function createFromEmailIdentifier(string $name, string $emailIdentifier, bool $isActivated = false): Organisation
    {
        $domainArray = explode('@', $emailIdentifier);
        if (
            (1 == count($domainArray) && !empty($domainArray[0]))
            || (2 == count($domainArray) && !empty($domainArray[1]))
        ) {
            return $this->create($name, strtolower($emailIdentifier), $isActivated);
        }
        throw new \InvalidArgumentException(sprintf("Unable to create organisation from 'emailIdentifier': '%s'", $emailIdentifier));
    }

    private function create(string $name, string $emailIdentifier, bool $isActivated): Organisation
    {
        if (empty($name) || empty($emailIdentifier)) {
            throw new \InvalidArgumentException(sprintf("Unable to create organisation with name '%s' from 'emailIdentifier': '%s'", $name, $emailIdentifier));
        }

        return (new Organisation())
            ->setName($name)
            ->setEmailIdentifier($emailIdentifier)
            ->setIsActivated($isActivated);
    }
}
