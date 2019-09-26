<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
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
     * @JMS\Groups({"user-organisations", "client-organisations"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="organisation_id_seq", allocationSize=1, initialValue=1)
     * @JMS\Groups({"organisation"})
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Groups({"user-organisations", "client-organisations"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     * @JMS\Groups({"organisation"})
     */
    private $name;

    /**
     * @var string
     *
     * @JMS\Groups({"user-organisations"})
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="email_identifier", type="string", length=256, nullable=false, unique=true)
     */
    private $emailIdentifier;

    /**
     * @var bool
     *
     * @JMS\Groups({"user-organisations"})
     *
     * @ORM\Column(name="is_activated", type="boolean", options={ "default": false}, nullable=false)
     */
    private $isActivated;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="organisations")
     */
    private $users;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Client", mappedBy="organisation")
     */
    private $clients;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

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

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User $user
     * @return Organisation
     */
    public function addUser(User $user): Organisation
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    /**
     * @param User $user
     * @return Organisation
     */
    public function removeUser(User $user): Organisation
    {
        $this->users->removeElement($user);
        return $this;
    }

    /**
     * @return Collection
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    /**
     * @param Client $client
     * @return Organisation
     */
    public function addClient(Client $client): Organisation
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }

    /**
     * @param Client $client
     * @return Organisation
     */
    public function removeClient(Client $client): Organisation
    {
        $this->clients->removeElement($client);
        return $this;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function containsUser(User $user)
    {
        return $this->users->contains($user);
    }
}
