<?php

namespace App\Entity;

use App\Repository\OrganisationRepository;
use App\Entity\Traits\IsSoftDeleteableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
#[ORM\Table(name: 'organisation')]
#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
class Organisation implements OrganisationInterface
{
    use IsSoftDeleteableEntity;

    /**
     * @var int
     */
    #[JMS\Groups(['organisation', 'user-organisations', 'client-organisations', 'org-created-event'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'organisation_id_seq', allocationSize: 1, initialValue: 1)]
    private $id;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[JMS\Groups(['organisation', 'user-organisations', 'client-organisations', 'org-created-event'])]
    #[ORM\Column(name: 'name', type: 'string', length: 256, nullable: false)]
    private $name;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[JMS\Type('string')]
    #[JMS\Groups(['user-organisations', 'organisation', 'org-created-event'])]
    #[JMS\SerializedName('email_identifier')]
    #[ORM\Column(name: 'email_identifier', type: 'string', length: 256, nullable: false, unique: true)]
    private $emailIdentifier;

    /**
     * @var bool
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['organisation', 'user-organisations', 'client-organisations', 'org-created-event'])]
    #[JMS\SerializedName('is_activated')]
    #[ORM\Column(name: 'is_activated', type: 'boolean', options: ['default' => false], nullable: false)]
    private $isActivated;

    /**
     * @var ArrayCollection
     */
    #[JMS\Type('ArrayCollection<App\Entity\User>')]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'organisations')]
    private $users;

    /**
     * @var ArrayCollection
     */
    #[JMS\Type('ArrayCollection<App\Entity\Clients>')]
    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'organisation')]
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
