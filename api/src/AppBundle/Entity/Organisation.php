<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="email_identifier", type="string", length=256, nullable=false, unique=true)
     */
    private $emailIdentifier;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_activated", type="boolean", options={ "default": false}, nullable=false)
     */
    private $isActivated;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Organisation
     */
    public function setName(string $name): Organisation
    {
        $this->name = $name;

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

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    /**
     * @param bool $isActivated
     * @return Organisation
     */
    public function setIsActivated(bool $isActivated): Organisation
    {
        $this->isActivated = $isActivated;

        return $this;
    }
}
