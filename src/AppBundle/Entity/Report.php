<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reports
 *
 * @ORM\Table(name="report")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReportRepository")
 */
class Report
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="report_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", inversedBy="reports")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Contact", mappedBy="report", cascade={"persist"})
     */
    private $contacts;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Account", mappedBy="report")
     */
    private $accounts;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\DecisionInvolvement", mappedBy="report", cascade={"persist"})
     */
    private $decisionInvolvements;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Decision", mappedBy="report", cascade={"persist"})
     */
    private $decisions;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Asset", mappedBy="report", cascade={"persist"})
     */
    private $assets;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PdfToken", mappedBy="report", cascade={"persist"})
     */
    private $pdfTokens;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CourtOrderType", inversedBy="reports")
     * @ORM\JoinColumn( name="court_order_type_id", referencedColumnName="id" )
     */
    private $courtOrderType;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=true)
     */
    private $title;

    /**
     * @var \Date
     *
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \Date
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="submit_date", type="datetime", nullable=true)
     */
    private $submitDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_edit", type="datetime", nullable=true)
     */	 
    private $lastedit;

    /**
     * @var string
     *
     * @ORM\Column(name="further_information", type="text", nullable=true)
     */
    private $furtherInformation;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="no_asset_to_add", type="boolean", options={ "default": false})
     */
    private $noAssetToAdd;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="no_contact_to_add", type="boolean", options={ "default": false})
     */
    private $noContactToAdd;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="no_decision_to_add", type="boolean", options={ "default": false})
     */
    private $noDecisionToAdd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="submitted", type="boolean", nullable=true)
     */
    private $submitted;
    

     /**
     * Constructor
     */
    public function __construct()
    {
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->accounts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->decisionInvolvements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->decisions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->assets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pdfTokens = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function setStartDate($startDate)
    {
        //$startDate->setTime(0, 0, 0);
        $this->startDate = $startDate;

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
    public function setEndDate($endDate)
    {
        //$endDate->setTime(23, 59, 59);
        $this->endDate = $endDate;

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
     * @param \DateTime $submitDate
     * @return Report
     */
    public function setSubmitDate($submitDate)
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

    /**
     * Add decisionInvolvements
     *
     * @param \AppBundle\Entity\DecisionInvolvement $decisionInvolvements
     * @return Report
     */
    public function addDecisionInvolvement(\AppBundle\Entity\DecisionInvolvement $decisionInvolvements)
    {
        $this->decisionInvolvements[] = $decisionInvolvements;

        return $this;
    }

    /**
     * Remove decisionInvolvements
     *
     * @param \AppBundle\Entity\DecisionInvolvement $decisionInvolvements
     */
    public function removeDecisionInvolvement(\AppBundle\Entity\DecisionInvolvement $decisionInvolvements)
    {
        $this->decisionInvolvements->removeElement($decisionInvolvements);
    }

    /**
     * Get decisionInvolvements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDecisionInvolvements()
    {
        return $this->decisionInvolvements;
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

    /**
     * Add pdfTokens
     *
     * @param \AppBundle\Entity\PdfToken $pdfTokens
     * @return Report
     */
    public function addPdfToken(\AppBundle\Entity\PdfToken $pdfTokens)
    {
        $this->pdfTokens[] = $pdfTokens;

        return $this;
    }

    /**
     * Remove pdfTokens
     *
     * @param \AppBundle\Entity\PdfToken $pdfTokens
     */
    public function removePdfToken(\AppBundle\Entity\PdfToken $pdfTokens)
    {
        $this->pdfTokens->removeElement($pdfTokens);
    }

    /**
     * Get pdfTokens
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPdfTokens()
    {
        return $this->pdfTokens;
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
     * Set noContactToAdd
     *
     * @param boolean $noContactToAdd
     * @return Report
     */
    public function setNoContactToAdd($noContactToAdd)
    {
        $this->noContactToAdd = $noContactToAdd;

        return $this;
    }

    /**
     * Get noContactToAdd
     *
     * @return boolean 
     */
    public function getNoContactToAdd()
    {
        return $this->noContactToAdd;
    }

    /**
     * Set noDecisionToAdd
     *
     * @param boolean $noDecisionToAdd
     * @return Report
     */
    public function setNoDecisionToAdd($noDecisionToAdd)
    {
        $this->noDecisionToAdd = $noDecisionToAdd;

        return $this;
    }

    /**
     * Get noDecisionToAdd
     *
     * @return boolean 
     */
    public function getNoDecisionToAdd()
    {
        return $this->noDecisionToAdd;
    }
    
    /**
     * Check if this report has any payments associated to it
     * 
     * @return boolean
     */
    public function hasPayments()
    {
        //check if this report is associated to any accounts
        if($this->accounts->count() < 1){
            return false;
        }
        
        foreach($this->accounts as $account){
            //check if this account has any income payments associated to it
            $incomes = $account->getIncomes();
            
            foreach($incomes as $income){
                if($income->getIncomePayments()->count() > 0){
                    return true;
                }
            }
            
            //check if this account has any benefits payment associated to it
            $benefits = $account->getBenefits();
            
            foreach($benefits as $benefit){
                if($benefit->getBenefitPayments()->count() > 0){
                    return true;
                }
            }
            
            //check if this account has any expenditure payment associated to it
            $expenditures = $account->getExpenditures();
            
            foreach($expenditures as $expenditure){
                if($expenditure->getExpenditurePayments()->count() > 0){
                    return true;
                }
            }
        }
        return false;
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
}
