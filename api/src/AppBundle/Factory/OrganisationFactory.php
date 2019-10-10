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

        if (!$this->isValidEmailAddress($email)) {
            throw new \InvalidArgumentException(sprintf(
                "Unable to create organisation from 'name': '%s', 'email': '%s'",
                $name, $email
            ));
        }

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

        $domain = $this->extractDomain($emailIdentifier);

        $isPublicDomain = in_array($domain, $this->sharedDomains);

        return (new Organisation())
            ->setName($name)
            ->setEmailIdentifier($emailIdentifier)
            ->setIsActivated($isActivated)
            ->setIsPublicDomain($isPublicDomain);
    }

    /**
     * @param $emailIdentifier
     * @return mixed
     */
    private function extractDomain($emailIdentifier)
    {
        $atSymbolArray = explode('@', $emailIdentifier);

        if (count($atSymbolArray) == 2) {
            return $atSymbolArray[1];
        }

        return $emailIdentifier;
    }

    /**
     * @param $email
     * @return bool
     */
    private function isValidEmailAddress($email)
    {
        $valid = false;
        $atSymbolArray = explode('@', $email);

        if (!empty($email) && count($atSymbolArray) == 2) {
            $valid = true;
        }

        return $valid;
    }
}
