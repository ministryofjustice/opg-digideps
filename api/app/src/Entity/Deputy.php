<?php

namespace App\Entity;

use App\Entity\Traits\CreateUpdateTimestamps;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Deputy.
 *
 * @ORM\Table(name="deputy", indexes={@ORM\Index(name="deputy_uid_idx", columns={"deputy_uid"})})
 *
 * @ORM\Entity(repositoryClass="App\Repository\DeputyRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Deputy
{
    use CreateUpdateTimestamps;

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="deputy_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private int $id;

    /**
     * Holds the deputy the client belongs to
     * Loaded from the CSV upload.
     *
     * @var ArrayCollection<int, Client>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Client", mappedBy="deputy")
     *
     * @ORM\JoinColumn(name="id", referencedColumnName="deputy_id", onDelete="CASCADE")
     */
    #[JMS\Exclude]
    private Collection $clients;

    /**
     * @ORM\Column(name="deputy_uid", type="string", length=20, nullable=false, unique=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private string $deputyUid;

    /**
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private string $firstname;

    /**
     * @ORM\Column(name="lastname", type="string", length=100, nullable=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private string $lastname;

    /**
     * @ORM\Column(name="email1", type="string", length=60, nullable=false, unique=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private string $email1;

    /**
     * @ORM\Column(name="email2", type="string", length=60, nullable=true, unique=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $email2 = null;

    /**
     * @ORM\Column(name="email3", type="string", length=60, nullable=true, unique=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $email3 = null;

    /**
     * @ORM\Column(name="address1", type="string", length=200, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $address1 = null;

    /**
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $address2 = null;

    /**
     * @ORM\Column(name="address3", type="string", length=200, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $address3 = null;

    /**
     * @ORM\Column(name="address4", type="string", length=200, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $address4 = null;

    /**
     * @ORM\Column(name="address5", type="string", length=200, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $address5 = null;

    /**
     * @ORM\Column(name="address_postcode", type="string", length=10, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $addressPostcode = null;

    /**
     * @ORM\Column(name="address_country", type="string", length=10, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['user', 'team', 'report-submitted-by', 'deputy'])]
    private ?string $addressCountry = null;

    /**
     * @ORM\Column(name="phone_main", type="string", length=20, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $phoneMain = null;

    /**
     * @ORM\Column(name="phone_alternative", type="string", length=20, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report-submitted-by', 'deputy'])]
    private ?string $phoneAlternative = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="deputy", cascade={"remove", "persist"})
     *
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")
     */
    #[JMS\Type('App\Entity\User')]
    private ?User $user = null;

    /**
     * @var ArrayCollection<int, CourtOrderDeputy>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CourtOrderDeputy", mappedBy="deputy", cascade={"persist", "remove"})
     */
    private Collection $courtOrderDeputyRelationships;

    public function __construct()
    {
        $this->courtOrderDeputyRelationships = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function getDeputyUid(): string
    {
        return $this->deputyUid;
    }

    public function setDeputyUid(string $deputyUid): self
    {
        $this->deputyUid = $deputyUid;

        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = trim($firstname);

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = trim($lastname);

        return $this;
    }

    public function getEmail1(): string
    {
        return $this->email1;
    }

    public function setEmail1(string $email1): self
    {
        $this->email1 = trim($email1);

        return $this;
    }

    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    public function setEmail2(?string $email2): self
    {
        if (!is_null($email2)) {
            $email2 = trim($email2);
        }

        $this->email2 = $email2;

        return $this;
    }

    public function getEmail3(): ?string
    {
        return $this->email3;
    }

    public function setEmail3(?string $email3): self
    {
        if (!is_null($email3)) {
            $email3 = trim($email3);
        }

        $this->email3 = $email3;

        return $this;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(?string $address1): self
    {
        if (!is_null($address1)) {
            $address1 = trim($address1);
        }

        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        if (!is_null($address2)) {
            $address2 = trim($address2);
        }

        $this->address2 = $address2;

        return $this;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    public function setAddress3(?string $address3): self
    {
        if (!is_null($address3)) {
            $address3 = trim($address3);
        }

        $this->address3 = $address3;

        return $this;
    }

    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    public function setAddress4(?string $address4): self
    {
        if (!is_null($address4)) {
            $address4 = trim($address4);
        }

        $this->address4 = $address4;

        return $this;
    }

    public function getAddress5(): ?string
    {
        return $this->address5;
    }

    public function setAddress5(?string $address5): self
    {
        if (!is_null($address5)) {
            $address5 = trim($address5);
        }

        $this->address5 = $address5;

        return $this;
    }

    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    public function setAddressPostcode(?string $addressPostcode): self
    {
        if (!is_null($addressPostcode)) {
            $addressPostcode = trim($addressPostcode);
        }

        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): self
    {
        if (!is_null($addressCountry)) {
            $addressCountry = trim($addressCountry);
        }

        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getPhoneMain(): ?string
    {
        return $this->phoneMain;
    }

    public function setPhoneMain(?string $phoneMain): self
    {
        if (!is_null($phoneMain)) {
            $phoneMain = trim($phoneMain);
        }

        $this->phoneMain = $phoneMain;

        return $this;
    }

    public function getPhoneAlternative(): ?string
    {
        return $this->phoneAlternative;
    }

    public function setPhoneAlternative(?string $phoneAlternative): self
    {
        if (!is_null($phoneAlternative)) {
            $phoneAlternative = trim($phoneAlternative);
        }

        $this->phoneAlternative = $phoneAlternative;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): Deputy
    {
        $this->user = $user;

        return $this;
    }

    public function addressHasChanged(OrgDeputyshipDto $dto): bool
    {
        return $this->getAddress1() !== $dto->getDeputyAddress1()
             || $this->getAddress2() !== $dto->getDeputyAddress2()
             || $this->getAddress3() !== $dto->getDeputyAddress3()
             || $this->getAddress4() !== $dto->getDeputyAddress4()
             || $this->getAddress5() !== $dto->getDeputyAddress5()
             || $this->getAddressPostcode() !== $dto->getDeputyPostcode();
    }

    public function nameHasChanged(OrgDeputyshipDto $dto): bool
    {
        if ($dto->deputyIsAnOrganisation()) {
            return $dto->getOrganisationName() !== $this->getFirstname();
        } else {
            return $dto->getDeputyFirstname() !== $this->getFirstname()
            || $dto->getDeputyLastname() !== $this->getLastname();
        }
    }

    public function emailHasChanged(OrgDeputyshipDto $dto): bool
    {
        return $this->email1 !== $dto->getDeputyEmail()
            && null !== $dto->getDeputyEmail();
    }

    public function associateWithCourtOrder(CourtOrder $courtOrder, bool $isActive = true): Deputy
    {
        $courtOrderDeputy = new CourtOrderDeputy();
        $courtOrderDeputy->setCourtOrder($courtOrder);
        $courtOrderDeputy->setDeputy($this);
        $courtOrderDeputy->setIsActive($isActive);

        $this->courtOrderDeputyRelationships[] = $courtOrderDeputy;

        return $this;
    }

    public function getCourtOrdersWithStatus(): array
    {
        $result = [];

        foreach ($this->courtOrderDeputyRelationships as $element) {
            $result[] = [
                'courtOrder' => $element->getCourtOrder(),
                'isActive' => $element->isActive(),
            ];
        }

        return $result;
    }
}
