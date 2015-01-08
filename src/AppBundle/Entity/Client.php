<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 *
 * @ORM\Table(name="client")
 * @ORM\Entity
 */
class Client
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="client_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="clients")
     * @ORM\JoinTable(name="deputy_case",
     *         joinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")},
     *         inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}     
     *     )
     */
    private $users;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report", mappedBy="client", cascade={"persist"})
     */
    private $reports;

    /**
     * @var string
     *
     * @ORM\Column(name="case_number", type="string", length=20, nullable=true)
     */
    private $caseNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=200, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=10, nullable=true)
     */
    private $postcode;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Title", inversedBy="cases")
     * @ORM\JoinColumn(name="title_id", referencedColumnName="id")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=50, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=50, nullable=true)
     */
    private $lastname;

    /**
     * @var CourtOrderType
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CourtOrderType", inversedBy="clients")
     * @ORM\JoinColumn(name="court_order_type_id", referencedColumnName="id")
     */
    private $courtOrderType;

    /**
     * @var \Date
     *
     * @ORM\Column(name="court_date", type="date", nullable=true)
     */
    private $courtDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */
    private $lastedit;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reports = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set caseNumber
     *
     * @param string $caseNumber
     * @return Client
     */
    public function setCaseNumber($caseNumber)
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * Get caseNumber
     *
     * @return string 
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Client
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Client
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Client
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return Client
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get postcode
     *
     * @return string 
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Client
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return Client
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set courtDate
     *
     * @param \DateTime $courtDate
     * @return Client
     */
    public function setCourtDate($courtDate)
    {
        $this->courtDate = $courtDate;

        return $this;
    }

    /**
     * Get courtDate
     *
     * @return \DateTime 
     */
    public function getCourtDate()
    {
        return $this->courtDate;
    }

    /**
     * Set lastedit
     *
     * @param \DateTime $lastedit
     * @return Client
     */
    public function setLastedit($lastedit)
    {
        $this->lastedit = $lastedit;

        return $this;
    }

    /**
     * Get lastedit
     *
     * @return \DateTime 
     */
    public function getLastedit()
    {
        return $this->lastedit;
    }

    /**
     * Add users
     *
     * @param \AppBundle\Entity\User $users
     * @return Client
     */
    public function addUser(\AppBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \AppBundle\Entity\User $users
     */
    public function removeUser(\AppBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add reports
     *
     * @param \AppBundle\Entity\Report $reports
     * @return Client
     */
    public function addReport(\AppBundle\Entity\Report $reports)
    {
        $this->reports[] = $reports;

        return $this;
    }

    /**
     * Remove reports
     *
     * @param \AppBundle\Entity\Report $reports
     */
    public function removeReport(\AppBundle\Entity\Report $reports)
    {
        $this->reports->removeElement($reports);
    }

    /**
     * Get reports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Set title
     *
     * @param \AppBundle\Entity\Title $title
     * @return Client
     */
    public function setTitle(\AppBundle\Entity\Title $title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return \AppBundle\Entity\Title 
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @return CourtOrderType
     */
    public function getCourtOrderType()
    {
        return $this->courtOrderType;
    }

    public function setCourtOrderType(CourtOrderType $courtOrderType)
    {
        $this->courtOrderType = $courtOrderType;
    }

    /**
     * Return full name, e.g. Mr John Smith
     */
    public function getFullName($space =  "&nbsp;")
    {
        $hasValidTitle = $this->getTitle() && $this->getTitle()->getTitle();
        
        return ($hasValidTitle ?  $this->getTitle()->getTitle() . $space : '')
                . $this->getFirstname() . $space
                . $this->getLastname();
    }
    
    /**
     * @return boolean
     */
    public function isPersonalWelfare()
    {
        return $this->getCourtOrderType()->getId() == CourtOrderType::PERSONAL_WELFARE;
    }
    
    /**
     * @return boolean
     */
    public function isPropertyAndAffairs()
    {
        $courtOrderTypes = [ CourtOrderType::PROPERTY_AND_AFFAIRS, CourtOrderType::PROPERTY_AND_AFFAIRS_AND_PERSONAL_WELFARE];
        return in_array($this->getCourtOrderType()->getId(), $courtOrderTypes);
    }
    
    /**
     * @return boolean
     */
    public function hasAccounts()
    {
        return $this->isPropertyAndAffairs();
    }
    
    /**
     * @return boolean
     */
    public function hasAssets()
    {
        return $this->isPropertyAndAffairs();
    }
    
}
