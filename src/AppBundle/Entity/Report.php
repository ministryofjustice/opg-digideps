<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use AppBundle\Filter\UserFilterInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Reports
 * @JMS\XmlRoot("report")
 * @JMS\ExclusionPolicy("NONE")
 * @ORM\Table(name="report")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReportRepository")
 */
class Report implements UserFilterInterface
{
    /**
     * @var integer
     *
     * @JMS\Groups({"transactions","basic"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="report_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     * 
     * @JMS\Groups({"transactions","basic"})
     * @JMS\Accessor(getter="getClientId")
     * @JMS\Type("integer")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", inversedBy="reports")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;
    
    /**
     * @JMS\Groups({"transactions","basic"})
     * @JMS\Accessor(getter="getContactIds")
     * @JMS\Type("array")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Contact", mappedBy="report", cascade={"persist"})
     */
    private $contacts;
    
    /**
     * @JMS\Groups({"basic"})
     * @JMS\Accessor(getter="getAccountIds")
     * @JMS\Type("array")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Account", mappedBy="report")
     */
    private $accounts;
    
    /**
     * @JMS\Groups({"transactions"})
     * @JMS\Accessor(getter="getAccounts", setter="addAccount")
     * @JMS\Type("array<AppBundle\Entity\Account>")
     */
    private $accountObjs;
    
    /**
     * @JMS\Groups({"transactions", "basic"})
     * @JMS\Accessor(getter="getDecisionIds")
     * @JMS\Type("array")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Decision", mappedBy="report", cascade={"persist"})
     */
    private $decisions;
    
    /**
     * @JMS\Groups({"transactions","basic"})
     * @JMS\Accessor(getter="getAssetIds")
     * @JMS\Type("array")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Asset", mappedBy="report", cascade={"persist"})
     */
    private $assets;
    
    /**
     * @JMS\Groups({"transactions", "basic"})
     * @JMS\Accessor(getter="getCourtOrderTypeId")
     * @JMS\Type("integer")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CourtOrderType", inversedBy="reports")
     * @ORM\JoinColumn( name="court_order_type_id", referencedColumnName="id" )
     */
    private $courtOrderType;

    /**
     * @var string
     *
     * @JMS\Groups({"transactions", "basic"})
     * @JMS\Type("string")
     * @ORM\Column(name="title", type="string", length=150, nullable=true)
     */
    private $title;

    /**
     * @var \Date
     *
     * @JMS\Groups({"transactions", "basic"})
     * @JMS\Accessor(getter="getStartDate")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     * 
     * @JMS\Groups({"transactions", "basic"})
     * @JMS\Accessor(getter="getEndDate")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @var \DateTime
     * 
     * @JMS\Groups({"transactions", "basic"})
     * @JMS\Accessor(getter="getSubmitDate")
     * @JMS\Type("DateTime")
     * @ORM\Column(name="submit_date", type="datetime", nullable=true)
     */
    private $submitDate;

    /**
     * @var \DateTime
     * @JMS\Accessor(getter="getLastedit")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */	 
    private $lastedit;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @ORM\Column(name="further_information", type="text", nullable=true)
     */
    private $furtherInformation;
    
    /**
     * @var boolean
     * @JMS\Groups({"transactions","basic"})
     * @JMS\Type("boolean")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="no_asset_to_add", type="boolean", options={ "default": false})
     */
    private $noAssetToAdd;
    
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="reason_for_no_contacts", type="text", nullable=true)
     */
    private $reasonForNoContacts;
    
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transactions","basic"})
     * @ORM\Column(name="reason_for_no_decisions", type="text", nullable=true)
     **/
    private $reasonForNoDecisions;

    /**
     * @var boolean
     *
     * @JMS\Groups({"transactions", "basic"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="submitted", type="boolean", nullable=true)
     */
    private $submitted;
    
    /**
     * @var boolean
     * @JMS\Groups({"basic"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="reviewed", type="boolean", nullable=true)
     */
    private $reviewed;
    
     /**
     * Constructor
     */
    public function __construct()
    {
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->accounts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->decisions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->assets = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Report
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Report
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = new \DateTime($startDate->format('Y-m-d'));
        
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {   
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Report
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = new \DateTime($endDate->format('Y-m-d'));

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {   
        return $this->endDate;
    }

    /**
     * Set submitDate
     *
     * @param string $submitDate
     * @return Report
     */
    public function setSubmitDate(\DateTime $submitDate = null)
    {
        $this->submitDate = $submitDate;

        return $this;
    }

    /**
     * Get submitDate
     *
     * @return \DateTime 
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * Set lastedit
     *
     * @param \DateTime $lastedit
     * @return Report
     */
    public function setLastedit(\DateTime $lastedit)
    {
        $this->lastedit = new \DateTime($lastedit->format('Y-m-d'));

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
     * Set furtherInformation
     *
     * @param string $furtherInformation
     * @return Report
     */
    public function setFurtherInformation($furtherInformation)
    {
        $this->furtherInformation = $furtherInformation;

        return $this;
    }

    /**
     * Get furtherInformation
     *
     * @return string 
     */
    public function getFurtherInformation()
    {
        return $this->furtherInformation;
    }

    /**
     * Set submitted
     *
     * @param boolean $submitted
     * @return Report
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

        return $this;
    }

    /**
     * Get submitted
     *
     * @return boolean 
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }
    
    /**
     * @return boolean
     */
    public function getReviewed()
    {
        return $this->reviewed;
    }

    /**
     * @param boolean $reviewed
     */
    public function setReviewed($reviewed)
    {
        $this->reviewed = $reviewed;
        return $this;
    }

    /**
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     * @return Report
     */
    public function setClient(\AppBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \AppBundle\Entity\Client 
     */
    public function getClient()
    {
        return $this->client;
    }
    
    public function getClientId()
    {
        return $this->client->getId();
    }

    /**
     * Add contacts
     *
     * @param \AppBundle\Entity\Contact $contacts
     * @return Report
     */
    public function addContact(\AppBundle\Entity\Contact $contacts)
    {
        $this->contacts[] = $contacts;

        return $this;
    }
    
    /**
     * Remove contacts
     *
     * @param \AppBundle\Entity\Contact $contacts
     */
    public function removeContact(\AppBundle\Entity\Contact $contacts)
    {
        $this->contacts->removeElement($contacts);
    }

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContacts()
    {
        return $this->contacts;
    }
    
    public function getContactIds()
    {   
        $contacts = [];
        if(!empty($this->contacts)){
            foreach($this->contacts as $contact){
                $contacts[] = $contact->getId();
            }
        }
        return $contacts;
    }

    /**
     * Add accounts
     *
     * @param \AppBundle\Entity\Account $accounts
     * @return Report
     */
    public function addAccount(\AppBundle\Entity\Account $accounts)
    {
        $this->accounts[] = $accounts;

        return $this;
    }

    /**
     * Remove accounts
     *
     * @param \AppBundle\Entity\Account $accounts
     */
    public function removeAccount(\AppBundle\Entity\Account $accounts)
    {
        $this->accounts->removeElement($accounts);
    }

    /**
     * Get accounts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccounts()
    {
        return $this->accounts;
    }
    
    public function getAccountIds()
    {
        $accounts = [];
        if(!empty($this->accounts)){
            foreach($this->accounts as $account){
                $accounts[] = $account->getId();
            }
        }
        return $accounts;
    }

    /**
     * Add decisions
     *
     * @param \AppBundle\Entity\Decision $decision
     * @return Report
     */
    public function addDecision(\AppBundle\Entity\Decision $decision)
    {
        $this->decisions[] = $decision;

        return $this;
    }

    /**
     * Remove decisions
     *
     * @param \AppBundle\Entity\Decision $decision
     */
    public function removeDecision(\AppBundle\Entity\Decision $decision)
    {
        $this->decisions->removeElement($decision);
    }

    /**
     * Get decisions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDecisions()
    {
        return $this->decisions;
    }

    
    public function getDecisionIds()
    {
        $decisions = [];
        if(!empty($this->decisions)){
            foreach($this->decisions as $decision){
                $decisions[] = $decision->getId();
            }
        }
        return $decisions;
    }
    
    /**
     * Add assets
     *
     * @param \AppBundle\Entity\Asset $assets
     * @return Report
     */
    public function addAsset(\AppBundle\Entity\Asset $assets)
    {
        $this->assets[] = $assets;

        return $this;
    }

    /**
     * Remove assets
     *
     * @param \AppBundle\Entity\Asset $assets
     */
    public function removeAsset(\AppBundle\Entity\Asset $assets)
    {
        $this->assets->removeElement($assets);
    }

    /**
     * Get assets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAssets()
    {
        return $this->assets;
    }

    public function getAssetIds()
    {
        $assets = [];
        if(!empty($this->assets)){
            foreach($this->assets as $asset){
                $assets[] = $asset->getId();
            }
        }
        return $assets;
    }
    
    /**
     * Set noAssetToAdd
     *
     * @param boolean $noAssetToAdd
     * @return Report
     */
    public function setNoAssetToAdd($noAssetToAdd)
    {
        $this->noAssetToAdd = $noAssetToAdd;

        return $this;
    }

    /**
     * Get noAssetToAdd
     *
     * @return boolean 
     */
    public function getNoAssetToAdd()
    {
        return $this->noAssetToAdd;
    }

    /**
     * Set reasonForNoContact
     *
     * @param string $reasonForNoContacts
     * @return Report
     */
    public function setReasonForNoContacts($reasonForNoContacts)
    {
        $this->reasonForNoContacts = $reasonForNoContacts;

        return $this;
    }

    /**
     * Get reasonForNoContacts
     *
     * @return string 
     */
    public function getReasonForNoContacts()
    {
        return $this->reasonForNoContacts;
    }

    /**
     * Set reasonForNoDecisions
     *
     * @param string $reasonForNoDecisions
     * @return Report
     **/
    public function setReasonForNoDecisions($reasonForNoDecisions)
    {
        $this->reasonForNoDecisions = $reasonForNoDecisions;

        return $this;
    }

    /**
     * Get ReasonForNoDecisions
     *
     * @return string
     */
    public function getReasonForNoDecisions()
    {
        return $this->reasonForNoDecisions;
    }
    

    /**
     * Set courtOrderType
     *
     * @param \AppBundle\Entity\CourtOrderType $courtOrderType
     * @return Report
     */
    public function setCourtOrderType(\AppBundle\Entity\CourtOrderType $courtOrderType = null)
    {
        $this->courtOrderType = $courtOrderType;

        return $this;
    }

    /**
     * Get courtOrderType
     *
     * @return \AppBundle\Entity\CourtOrderType 
     */
    public function getCourtOrderType()
    {
        return $this->courtOrderType;
    }
    
    public function getCourtOrderTypeId()
    {
        return $this->courtOrderType->getId();
    }
    
    /**
     * Filter every query run on report entity by user
     * 
     * @param QueryBuilder $qb
     * @param integer $userId
     * @return QueryBuilder
     */
    public static function applyUserFilter(QueryBuilder $qb,$userId)
    {
        $alias = $qb->getRootAliases()[0];
        $qb->join($alias.'.client ', 'c');
        $qb->join('c.users','u')->andWhere('u.id = :user_id')->setParameter('user_id', $userId);
        
        return $qb;
    }
}
