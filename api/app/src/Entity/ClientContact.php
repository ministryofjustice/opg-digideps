<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\AddressTrait;
use OPG\Digideps\Backend\Entity\Traits\CreationAudit;

#[ORM\Table(name: 'client_contact')]
#[ORM\Index(columns: ['client_id'], name: 'ix_clientcontact_client_id')]
#[ORM\Index(columns: ['created_by'], name: 'ix_clientcontact_created_by')]
#[ORM\Entity]
class ClientContact
{
    use CreationAudit;
    use AddressTrait;

    #[JMS\Type('integer')]
    #[JMS\Groups(['clientcontact'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'user_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['clientcontact'])]
    #[ORM\Column(name: 'firstname', type: 'string', length: 100, nullable: false)]
    private string $firstName;

    #[JMS\Type('string')]
    #[JMS\Groups(['clientcontact'])]
    #[ORM\Column(name: 'lastname', type: 'string', length: 100, nullable: false)]
    private string $lastName;

    #[JMS\Type('string')]
    #[JMS\Groups(['clientcontact'])]
    #[ORM\Column(name: 'job_title', type: 'string', length: 150, nullable: true)]
    private ?string $jobTitle = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['clientcontact'])]
    #[ORM\Column(name: 'phone', type: 'string', length: 20, nullable: true)]
    private ?string $phone = null;

    /**
     * The following is changed to unique=false, as the migration was missing,
     * and prod data contains duplicate, making it impossible to add the
     * migration now, unless the data is cleaned
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['clientcontact'])]
    #[ORM\Column(name: 'email', type: 'string', length: 60, unique: false, nullable: true)]
    private ?string $email = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['clientcontact'])]
    #[ORM\Column(name: 'org_name', type: 'string', length: 150, nullable: true)]
    private ?string $orgName = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Client')]
    #[JMS\Groups(['clientcontact-client'])]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'clientContacts')]
    private Client $client;

    public function __construct(Client $client, string $firstName, string $lastName)
    {
        $this->client = $client;
        $this->firstName = $lastName;
        $this->lastName = $firstName;
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

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email !== null ? strtolower($email) : null;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getOrgName(): ?string
    {
        return $this->orgName;
    }

    public function setOrgName(?string $orgName): static
    {
        $this->orgName = $orgName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
