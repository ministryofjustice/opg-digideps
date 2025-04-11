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
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="deputy_id_seq", allocationSize=1, initialValue=1)
     *
     * @JMS\Groups({"report-submitted-by", "deputy"})
     */
    private $id;

    /**
     * Holds the deputy the client belongs to
     * Loaded from the CSV upload.
     *
     * @JMS\Exclude
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Client", mappedBy="deputy")
     *
     * @ORM\JoinColumn(name="id", referencedColumnName="deputy_id")
     */
    private $clients;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="deputy_uid", type="string", length=20, nullable=false, unique=true)
     */
    private $deputyUid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=false)
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report-submitted-by", "deputy"})
     */
    private $lastname;

    /**
     * @var string
     *
     * @JMS\Groups({"report-submitted-by", "deputy"})
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email1", type="string", length=60, nullable=false, unique=false)
     */
    private $email1;

    /**
     * @var string
     *
     * @JMS\Groups({"report-submitted-by", "deputy"})
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email2", type="string", length=60, nullable=true, unique=false)
     */
    private $email2;

    /**
     * @var string
     *
     * @JMS\Groups({"report-submitted-by", "deputy"})
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email3", type="string", length=60, nullable=true, unique=false)
     */
    private $email3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({ "report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="address1", type="string", length=200, nullable=true)
     */
    private $address1;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({ "report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({ "report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="address3", type="string", length=200, nullable=true)
     */
    private $address3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({ "report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="address4", type="string", length=200, nullable=true)
     */
    private $address4;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({ "report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="address5", type="string", length=200, nullable=true)
     */
    private $address5;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({ "report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="address_postcode", type="string", length=10, nullable=true)
     */
    private $addressPostcode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user", "team", "report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="address_country", type="string", length=10, nullable=true)
     */
    private $addressCountry;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="phone_main", type="string", length=20, nullable=true)
     */
    private $phoneMain;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report-submitted-by", "deputy"})
     *
     * @ORM\Column(name="phone_alternative", type="string", length=20, nullable=true)
     */
    private $phoneAlternative;

    /**
     * @JMS\Type("App\Entity\User")
     *
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="deputy", cascade={"remove"})
     *
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")
     */
    private ?User $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CourtOrderDeputy", mappedBy="deputy", cascade={"persist"})
     */
    private Collection $courtOrderDeputyRelationships;

    public function __construct()
    {
        $this->courtOrderDeputyRelationships = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @return string
     */
    public function getDeputyUid()
    {
        return $this->deputyUid;
    }

    /**
     * @param string $deputyUid
     *
     * @return $this
     */
    public function setDeputyUid($deputyUid)
    {
        $this->deputyUid = $deputyUid;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = trim($firstname);

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = trim($lastname);

        return $this;
    }

    /**
     * @return string
     * @return $this
     */
    public function getEmail1()
    {
        return $this->email1;
    }

    /**
     * @param string $email1
     *
     * @return $this
     */
    public function setEmail1($email1)
    {
        $this->email1 = trim($email1);

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * @param string $email2
     *
     * @return $this
     */
    public function setEmail2($email2)
    {
        if (!is_null($email2)) {
            $this->email2 = trim($email2);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail3()
    {
        return $this->email3;
    }

    /**
     * @param string $email3
     *
     * @return $this
     */
    public function setEmail3($email3)
    {
        if (!is_null($email3)) {
            $this->email3 = trim($email3);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     *
     * @return $this
     */
    public function setAddress1($address1)
    {
        $this->address1 = trim($address1);

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     *
     * @return $this
     */
    public function setAddress2($address2)
    {
        $this->address2 = trim($address2);

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     *
     * @return $this
     */
    public function setAddress3($address3)
    {
        $this->address3 = trim($address3);

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress4()
    {
        return $this->address4;
    }

    /**
     * @param string $address4
     *
     * @return $this
     */
    public function setAddress4($address4)
    {
        $this->address4 = trim($address4);

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress5()
    {
        return $this->address5;
    }

    /**
     * @param string $address5
     *
     * @return $this
     */
    public function setAddress5($address5)
    {
        $this->address5 = trim($address5);

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }

    /**
     * @param string $addressPostcode
     *
     * @return $this
     */
    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = trim($addressPostcode);

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    /**
     * @return $this
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = trim($addressCountry);

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneMain()
    {
        return $this->phoneMain;
    }

    /**
     * @param string $phoneMain
     *
     * @return $this
     */
    public function setPhoneMain($phoneMain)
    {
        if (!is_null($phoneMain)) {
            $this->phoneMain = trim($phoneMain);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneAlternative()
    {
        return $this->phoneAlternative;
    }

    /**
     * @param string $phoneAlternative
     *
     * @return $this
     */
    public function setPhoneAlternative($phoneAlternative)
    {
        if (!is_null($phoneAlternative)) {
            $this->phoneAlternative = trim($phoneAlternative);
        }

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

    public function addressHasChanged(OrgDeputyshipDto $dto)
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

    public function associateWithCourtOrder(CourtOrder $courtOrder, bool $discharged = false): Deputy
    {
        $courtOrderDeputy = new CourtOrderDeputy();
        $courtOrderDeputy->setCourtOrder($courtOrder);
        $courtOrderDeputy->setDeputy($this);
        $courtOrderDeputy->setDischarged($discharged);

        $this->courtOrderDeputyRelationships[] = $courtOrderDeputy;

        return $this;
    }

    public function getCourtOrdersWithStatus(): array
    {
        $result = [];

        foreach ($this->courtOrderDeputyRelationships as $element) {
            $result[] = [
                'courtOrder' => $element->getCourtOrder(),
                'discharged' => $element->isDischarged(),
            ];
        }

        return $result;
    }
}
