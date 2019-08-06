<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="organisation")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\OrganisationRepository")
 */
class Organisation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="organisation_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="organisation_name", type="string", length=256, nullable=false)
     */
    private $organisationName;

    /**
     * @var string
     *
     * @ORM\Column(name="email_identifier", type="string", length=256, nullable=false)
     */
    private $emailIdentifier;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Organisation
     */
    public function setId(int $id): Organisation
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganisationName(): string
    {
        return $this->organisationName;
    }

    /**
     * @param string $organisationName
     * @return Organisation
     */
    public function setOrganisationName(string $organisationName): Organisation
    {
        $this->organisationName = $organisationName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailIdentifier(): string
    {
        return $this->emailIdentifier;
    }

    /**
     * @param string $emailIdentifier
     * @return Organisation
     */
    public function setEmailIdentifier(string $emailIdentifier): Organisation
    {
        $this->emailIdentifier = $emailIdentifier;

        return $this;
    }
}
