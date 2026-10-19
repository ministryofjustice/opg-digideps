<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use OPG\Digideps\Backend\Entity\Organisation;

class OrganisationFactory
{
    public function __construct(private readonly array $sharedDomains)
    {
    }

    public function createFromFullEmail(string $name, string $email, bool $isActivated = false): Organisation
    {
        $email = strtolower($email);

        $domainArray = explode('@', $email);

        if (count($domainArray) === 1 && !empty($domainArray[0])) {
            return $this->createFromEmailIdentifier($name, $domainArray[0], $isActivated);
        }

        if (count($domainArray) === 2 && !empty($domainArray[1])) {
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
            (count($domainArray) == 1 && !empty($domainArray[0]))
            || (count($domainArray) == 2 && !empty($domainArray[1]))
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

        return new Organisation($name, $emailIdentifier, $isActivated);
    }
}
