<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="court_order_deputy")
 * @ORM\Entity()
 */
class CourtOrderDeputy
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="court_order_deputy_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $deputyNumber;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $firstname;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $surname;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * @var DateTime|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $dob;

    /**
     * @var CourtOrder
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CourtOrder", inversedBy="deputies", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="court_order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $courtOrder;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $user;

    /**
     * @var Organisation|null
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $organisation;

    /**
     * @var Collection<int, CourtOrderAddress>
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CourtOrderAddress", mappedBy="deputy", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $addresses;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDeputyNumber(): string
    {
        return $this->deputyNumber;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getDob(): ?DateTime
    {
        return $this->dob;
    }

    public function getCourtOrder(): CourtOrder
    {
        return $this->courtOrder;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    /**
     * @return Collection<int, CourtOrderAddress>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function setDeputyNumber(string $deputyNumber): CourtOrderDeputy
    {
        $this->deputyNumber = $deputyNumber;

        return $this;
    }

    public function setFirstname(string $firstname): CourtOrderDeputy
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function setSurname(string $surname): CourtOrderDeputy
    {
        $this->surname = $surname;

        return $this;
    }

    public function setEmail(string $email): CourtOrderDeputy
    {
        $this->email = $email;

        return $this;
    }

    public function setDob(DateTime $dob): CourtOrderDeputy
    {
        $this->dob = $dob;

        return $this;
    }

    public function setCourtOrder(CourtOrder $courtOrder): CourtOrderDeputy
    {
        $this->courtOrder = $courtOrder;

        return $this;
    }

    public function setUser(User $user): CourtOrderDeputy
    {
        $this->user = $user;

        return $this;
    }

    public function setOrganisation(Organisation $organisation): CourtOrderDeputy
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function addAddress(CourtOrderAddress $address): CourtOrderDeputy
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setDeputy($this);
        }

        return $this;
    }
}
