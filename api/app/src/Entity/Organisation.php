<?php

namespace App\Entity;

use App\Entity\Traits\IsSoftDeleteableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="organisation")
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationRepository")
 */
class Organisation implements OrganisationInterface
{
    use IsSoftDeleteableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="organisation_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Groups(['organisation', 'user-organisations', 'client-organisations', 'org-created-event'])]
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    #[Assert\NotBlank]
    #[JMS\Groups(['organisation', 'user-organisations', 'client-organisations', 'org-created-event'])]
    private $name;

    /**
     * @var string
     *
     *
     *
     *
     * @ORM\Column(name="email_identifier", type="string", length=256, nullable=false, unique=true)
     */
    #[Assert\NotBlank]
    #[JMS\Groups(['user-organisations', 'organisation', 'org-created-event'])]
    #[JMS\Type('string')]
    #[JMS\SerializedName('email_identifier')]
    private $emailIdentifier;

    /**
     * @var bool
     *
     *
     *
     *
     * @ORM\Column(name="is_activated", type="boolean", options={ "default": false}, nullable=false)
     */
    #[JMS\Groups(['organisation', 'user-organisations', 'client-organisations', 'org-created-event'])]
    #[JMS\Type('boolean')]
    #[JMS\SerializedName('is_activated')]
    private $isActivated;

    /**
     * @var ArrayCollection
     *
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="organisations")
     */
    #[JMS\Type('ArrayCollection<App\Entity\User>')]
    private $users;

    /**
     * @var ArrayCollection
     *
     *
     * @ORM\OneToMany(targetEntity="Client", mappedBy="organisation")
     */
    #[JMS\Type('ArrayCollection<App\Entity\Clients>')]
    private $clients;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Organisation
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Organisation
    {
        $this->name = $name;

        return $this;
    }

    public function getEmailIdentifier(): string
    {
        return $this->emailIdentifier;
    }

    public function setEmailIdentifier(string $emailIdentifier): Organisation
    {
        $this->emailIdentifier = $emailIdentifier;

        return $this;
    }

    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(bool $isActivated): Organisation
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): Organisation
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): Organisation
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): Organisation
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }

    public function removeClient(Client $client): Organisation
    {
        $this->clients->removeElement($client);

        return $this;
    }

    /**
     * @return bool
     */
    public function containsUser(User $user)
    {
        return $this->users->contains($user);
    }

    /**
     *
     *
     *
     *
     * @return int
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('total-user-count')]
    #[JMS\Groups(['total-user-count'])]
    public function getTotalUserCount()
    {
        return count($this->getUsers());
    }

    /**
     *
     *
     *
     *
     * @return int
     */
    #[JMS\VirtualProperty]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('total-client-count')]
    #[JMS\Groups(['total-client-count'])]
    public function getTotalClientCount()
    {
        return count($this->getClients());
    }
}
