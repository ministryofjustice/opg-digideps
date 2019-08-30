<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\AddressTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Users.
 *
 * @ORM\Table(name="named_deputy", indexes={@ORM\Index(name="named_deputy_no_idx", columns={"deputy_no"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\NamedDeputyRepository")
 */
class NamedDeputy
{
    use AddressTrait;

    /**
     * @var int
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="named_deputy_id_seq", allocationSize=1, initialValue=1)
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @ORM\Column(name="deputy_no", type="string", length=15, nullable=false)
     */
    private $deputyNo;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=false)
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     */
    private $lastname;

    /**
     * @var string
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email1", type="string", length=60, nullable=false, unique=false)
     */
    private $email1;

    /**
     * @var string
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email2", type="string", length=60, nullable=true, unique=false)
     */
    private $email2;

    /**
     * @var string
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email3", type="string", length=60, nullable=true, unique=false)
     */
    private $email3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @ORM\Column(name="dep_addr_no", type="integer", length=100, nullable=true)
     */
    private $depAddrNo;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({ "report-submitted-by", "named-deputy"})
     * @ORM\Column(name="address4", type="string", length=200, nullable=true)
     */
    private $address4;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({ "report-submitted-by", "named-deputy"})
     * @ORM\Column(name="address5", type="string", length=200, nullable=true)
     */
    private $address5;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @ORM\Column(name="phone_main", type="string", length=20, nullable=true)
     */
    private $phoneMain;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @ORM\Column(name="phone_alternative", type="string", length=20, nullable=true)
     */
    private $phoneAlternative;

    /**
     * NamedDeputy constructor.
     * @param array $csvRow
     */
    public function __construct(array $csvRow)
    {
        $this->setDeputyNo($csvRow['Deputy No']);
        $this->setFirstname($csvRow['Dep Forename']);
        $this->setLastname($csvRow['Dep Surname']);
        $this->setEmail1($csvRow['Email']);
        $this->setEmail2($csvRow['Email2']);
        $this->setEmail3($csvRow['Email3']);
        $this->setDepAddrNo($csvRow['DepAddr No']);

        $this->setAddress1($csvRow['Dep Adrs1']);
        $this->setAddress2($csvRow['Dep Adrs2']);
        $this->setAddress3($csvRow['Dep Adrs3']);
        $this->setAddress4($csvRow['Dep Adrs4']);
        $this->setAddress5($csvRow['Dep Adrs5']);
        $this->setAddressPostcode($csvRow['Dep Postcode']);

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
     * @return string
     */
    public function getDeputyNo()
    {
        return $this->deputyNo;
    }

    /**
     * @param string $deputyNo
     *
     * @return $this
     */
    public function setDeputyNo($deputyNo)
    {
        $this->deputyNo = User::padDeputyNumber($deputyNo);
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
     *
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
        $this->email2 = trim($email2);
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
        $this->email3 = trim($email3);
        return $this;
    }

    /**
     * @return string
     */
    public function getDepAddrNo()
    {
        return $this->depAddrNo;
    }

    /**
     * @param string $depAddrNo
     *
     * @return $this
     */
    public function setDepAddrNo($depAddrNo)
    {
        $this->depAddrNo = User::padDeputyNumber($depAddrNo);
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
        $this->phoneMain = trim($phoneMain);
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
        $this->phoneAlternative = trim($phoneAlternative);
        return $this;
    }
}
