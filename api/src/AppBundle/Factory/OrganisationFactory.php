<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Organisation;

class OrganisationFactory
{
    /** @var array */
    private $sharedDomains = [];

    /** @param array $sharedDomains */
    public function __construct(array $sharedDomains)
    {
        $this->sharedDomains = $sharedDomains;
    }

    /**
     * @param string $name
     * @param string $email
     * @param bool $isActivated
     * @return Organisation
     */
    public function createFromFullEmail(string $name, string $email, bool $isActivated = false): Organisation
    {
        $email = strtolower($email);

        if (false === ($atSymbolPosition = strpos($email, '@'))) {
            return $this->create($name, $email, $isActivated);
        }

        $domain = substr($email, $atSymbolPosition + 1);
        $emailIdentifier = in_array($domain, $this->sharedDomains) ? $email : $domain;

        return $this->create($name, $emailIdentifier, $isActivated);
    }

    /**
     * @param string $name
     * @param string $emailIdentifier
     * @param bool $isActivated
     * @return Organisation
     */
    public function createFromEmailIdentifier(string $name, string $emailIdentifier, bool $isActivated = false): Organisation
    {
        return $this->create($name, strtolower($emailIdentifier), $isActivated);
    }

    /**
     * @param string $name
     * @param string $emailIdentifier
     * @param bool $isActivated
     * @return Organisation
     */
    private function create(string $name, string $emailIdentifier, bool $isActivated): Organisation
    {
        if (empty($name) || empty($emailIdentifier)) {
            throw new \InvalidArgumentException(sprintf(
                "Unable to create organisation from 'name': '%s', 'emailIdentifier': '%s'",
                $name, $emailIdentifier
            ));
        }

        return (new Organisation())
            ->setName($name)
            ->setEmailIdentifier($emailIdentifier)
            ->setIsActivated($isActivated);
    }
}
