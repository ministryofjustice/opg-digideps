<?php declare(strict_types=1);

namespace AppBundle\Factory;

use AppBundle\Entity\Organisation;

class OrganisationFactory
{
    const DEFAULT_ORG_NAME = 'Your Organisation';

    /** @var array */
    private $sharedDomains = [];

    /** @param array $sharedDomains */
    public function __construct(array $sharedDomains)
    {
        $this->sharedDomains = $sharedDomains;
    }

    /**
     * @param string $email
     * @param bool $isActivated
     * @return Organisation
     */
    public function createFromFullEmail(string $email, bool $isActivated = false): Organisation
    {
        $email = strtolower($email);

        $domainArray = explode('@', $email);

        if (count($domainArray) === 1 && !empty($domainArray[0])) {
            return $this->createFromEmailIdentifier($domainArray[0], $isActivated);
        }

        if (count($domainArray) === 2 && !empty($domainArray[1])) {
            $domain = $domainArray[1];
            $emailIdentifier = in_array($domain, $this->sharedDomains) ? $email : $domain;
            return $this->create($emailIdentifier, $isActivated);
        }

        throw new \InvalidArgumentException(sprintf(
            "Unable to create organisation from 'emailIdentifier': '%s'", $email
        ));
    }

    /**
     * @param string $emailIdentifier
     * @param bool $isActivated
     * @return Organisation
     */
    public function createFromEmailIdentifier(string $emailIdentifier, bool $isActivated = false): Organisation
    {
        $domainArray = explode('@', $emailIdentifier);
        if ((count($domainArray) == 1 && !empty($domainArray[0])) ||
            (count($domainArray) == 2 && !empty($domainArray[1]))
        )   {
            return $this->create(strtolower($emailIdentifier), $isActivated);
        }
        throw new \InvalidArgumentException(sprintf(
            "Unable to create organisation from 'emailIdentifier': '%s'", $emailIdentifier
        ));

    }

    /**
     * @param string $emailIdentifier
     * @param bool $isActivated
     * @return Organisation
     */
    private function create(string $emailIdentifier, bool $isActivated): Organisation
    {
        if (empty($emailIdentifier)) {
            throw new \InvalidArgumentException(sprintf(
                "Unable to create organisation from 'emailIdentifier': '%s'", $emailIdentifier
            ));
        }

        return (new Organisation())
            ->setName(self::DEFAULT_ORG_NAME)
            ->setEmailIdentifier($emailIdentifier)
            ->setIsActivated($isActivated);
    }
}
