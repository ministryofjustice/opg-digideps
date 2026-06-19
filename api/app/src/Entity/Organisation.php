<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\IsSoftDeleteableEntity;
use OPG\Digideps\Backend\Repository\OrganisationRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'organisation')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
class Organisation
{
    use IsSoftDeleteableEntity;

    #[JMS\Groups(['organisation', 'user-organisations', 'client-organisations', 'org-created-event'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'organisation_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[JMS\Groups(['organisation', 'user-organisations', 'client-organisations', 'org-created-event'])]
    #[ORM\Column(name: 'name', type: 'string', length: 256, nullable: false)]
    private string $name;

    #[Assert\NotBlank]
    #[JMS\Type('string')]
    #[JMS\Groups(['user-organisations', 'organisation', 'org-created-event'])]
    #[JMS\SerializedName('email_identifier')]
    #[ORM\Column(name: 'email_identifier', type: 'string', length: 256, unique: true, nullable: false)]
    private string $emailIdentifier;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['organisation', 'user-organisations', 'client-organisations', 'org-created-event'])]
    #[JMS\SerializedName('is_activated')]
    #[ORM\Column(name: 'is_activated', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isActivated;

    /**
     * @var Collection<int, User>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\User>')]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'organisations')]
    private Collection $users;

    /**
     * @var Collection<int, Client>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Clients>')]
    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: Client::class)]
    private Collection $clients;

    public function __construct(string $name, string $emailIdentifier, bool $isActivated = false)
    {
        $this->users = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->name = $name;
        $this->emailIdentifier = $emailIdentifier;
        $this->isActivated = $isActivated;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmailIdentifier(): string
    {
        return $this->emailIdentifier;
    }

    public function setEmailIdentifier(string $emailIdentifier): static
    {
        $this->emailIdentifier = $emailIdentifier;

        return $this;
    }

    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(bool $isActivated): static
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        $this->clients->removeElement($client);

        return $this;
    }

    public function containsUser(User $user): bool
    {
        return $this->users->contains($user);
    }

    #[JMS\VirtualProperty]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('total-user-count')]
    #[JMS\Groups(['total-user-count'])]
    public function getTotalUserCount(): int
    {
        return $this->getUsers()->count();
    }

    #[JMS\VirtualProperty]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('total-client-count')]
    #[JMS\Groups(['total-client-count'])]
    public function getTotalClientCount(): int
    {
        return $this->getClients()->count();
    }
}
