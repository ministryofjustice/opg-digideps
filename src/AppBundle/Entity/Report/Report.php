<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Traits as ReportTraits;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Reports.
 *
 * @ORM\Table(name="report")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Report\ReportRepository")
 */
class Report
{
    use ReportTraits\MoreInfoTrait;
    use ReportTraits\ExpensesTrait;
    use ReportTraits\GiftsTrait;
    use ReportTraits\ReportMoneyShortTrait;

    const HEALTH_WELFARE = 1;
    const PROPERTY_AND_AFFAIRS = 2;

    const TYPE_102 = '102';
    const TYPE_103 = '103';
    const TYPE_104 = '104';

    // to enable 103 permanently, search for this constant and enable code
    const ENABLE_103 = false;

    /**
     * Reports with total amount of assets
     * Threshold under which reports should be 103, and not 102
     */
    const ASSETS_TOTAL_VALUE_103_THRESHOLD = 21000;

    /**
     * @var int
     *
     * @JMS\Groups({"report"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="report_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *             see TYPE_ class constants
     *
     * @JMS\Groups({"report"})
     * @JMS\Type("string")
     * @ORM\Column(name="type", type="string", length=3, nullable=false)
     */
    private $type;

    /**
     * @var int
     *
     * @JMS\Groups({"client"})
     * @JMS\Type("AppBundle\Entity\Client")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", inversedBy="reports")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @JMS\Groups({"contact"})
     * @JMS\Type("array<AppBundle\Entity\Report\Contact>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Contact", mappedBy="report", cascade={"persist"})
     */
    private $contacts;

    /**
     * @JMS\Groups({"account"})
     * @JMS\Type("array<AppBundle\Entity\Report\BankAccount>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\BankAccount", mappedBy="report", cascade={"persist"})
     */
    private $bankAccounts;

    /**
     * @JMS\Groups({"money-transfer"})
     * @JMS\Type("array<AppBundle\Entity\Report\MoneyTransfer>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\MoneyTransfer", mappedBy="report", cascade={"persist"})
     */
    private $moneyTransfers;

    /**
     * @var MoneyTransaction[]
     *
     * @JMS\Groups({"transaction"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\MoneyTransaction", mappedBy="report", cascade={"persist"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $moneyTransactions;

    /**
     * @var Debt[]
     *
     * @JMS\Groups({"debt"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Debt", mappedBy="report", cascade={"persist"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $debts;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"debt"})
     *
     * @ORM\Column(name="has_debts", type="string", length=5, nullable=true)
     *
     * @var string
     */
    private $hasDebts;

    /**
     * @var Decision[]
     *
     * @JMS\Groups({"decision"})
     * @JMS\Type("array<AppBundle\Entity\Report\Decision>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Decision", mappedBy="report", cascade={"persist"})
     */
    private $decisions;

    /**
     * @var Asset[]
     *
     * @JMS\Groups({"asset"})
     * @JMS\Type("array<AppBundle\Entity\Report\Asset>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Asset", mappedBy="report", cascade={"persist"})
     */
    private $assets;

    /**
     * @var VisitsCare
     *
     * @JMS\Groups({"visits-care"})
     * @JMS\Type("AppBundle\Entity\Report\VisitsCare")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\VisitsCare",  mappedBy="report", cascade={"persist"})
     **/
    private $visitsCare;

    /**
     * @var Action
     *
     * @JMS\Groups({"action"})
     * @JMS\Type("AppBundle\Entity\Report\Action")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\Action",  mappedBy="report", cascade={"persist"})
     **/
    private $action;

    /**
     * @var MentalCapacity
     *
     * @JMS\Groups({ "mental-capacity"})
     * @JMS\Type("AppBundle\Entity\Report\MentalCapacity")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\MentalCapacity",  mappedBy="report", cascade={"persist"})
     **/
    private $mentalCapacity;

    /**
     * @var \Date
     *
     * @JMS\Groups({"report"})
     * @JMS\Accessor(getter="getStartDate")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report"})
     * @JMS\Accessor(getter="getEndDate")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"report"})
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
     * @JMS\Groups({"report"})
     * @ORM\Column(name="further_information", type="text", nullable=true)
     */
    private $furtherInformation;

    /**
     * @var bool deputy declaration saying there are no assets. Required (true/false) if no decisions are added
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"report"})
     * @ORM\Column(name="no_asset_to_add", type="boolean", options={ "default": false}, nullable=true)
     */
    private $noAssetToAdd;

    /**
     * @var bool deputy declaration saying there are no transfers. Required (true/false) if no transfers are added
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"report", "money-transfer"})
     * @ORM\Column(name="no_transfers_to_add", type="boolean", options={ "default": false}, nullable=true)
     */
    private $noTransfersToAdd;

    /**
     * @var string deputy reason for not having contacts. Required if no contacts are added
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report"})
     * @ORM\Column(name="reason_for_no_contacts", type="text", nullable=true)
     */
    private $reasonForNoContacts;

    /**
     * @var string deputy reason for not having decision. Required if no decisions are added
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report","decision"})
     * @ORM\Column(name="reason_for_no_decisions", type="text", nullable=true)
     **/
    private $reasonForNoDecisions;

    /**
     * @var bool whether the report is submitted or not
     *
     * @JMS\Groups({"report"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="submitted", type="boolean", nullable=true)
     */
    private $submitted;


    /**
     * @deprecated client shouldn't need this anymore
     *
     * @var bool
     * @JMS\Groups({"report"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="report_seen", type="boolean", options={"default": true})
     */
    private $reportSeen;

    /**
     * @var string reason required if balance calculation mismatches
     *
     * @JMS\Groups({"balance"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="balance_mismatch_explanation", type="text", nullable=true)
     */
    private $balanceMismatchExplanation;

    /**
     * @var string only_deputy|more_deputies_behalf|more_deputies_not_behalf
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report"})
     * @ORM\Column(name="agreed_behalf_deputy", type="string", length=50, nullable=true)
     */
    private $agreedBehalfDeputy;

    /**
     * @var string required if agreedBehalfDeputy == more_deputies_not_behalf
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report"})
     * @ORM\Column(name="agreed_behalf_deputy_explanation", type="text", nullable=true)
     */
    private $agreedBehalfDeputyExplanation;

    /**
     * @deprecated data needed for previous data migration
     *
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report"})
     * @ORM\Column(name="metadata", type="text", nullable=true)
     */
    private $metadata;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->bankAccounts = new ArrayCollection();
        $this->moneyTransfers = new ArrayCollection();
        $this->moneyTransactions = new ArrayCollection();
        $this->debts = new ArrayCollection();
        $this->decisions = new ArrayCollection();
        $this->assets = new ArrayCollection();
        $this->noAssetToAdd = null;
        $this->noTransfersToAdd = null;
        $this->reportSeen = true;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime $startDate
     *
     * @return Report
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = new \DateTime($startDate->format('Y-m-d'));

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime $endDate
     *
     * @return Report
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = new \DateTime($endDate->format('Y-m-d'));

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set submitDate.
     *
     * @param string $submitDate
     *
     * @return Report
     */
    public function setSubmitDate(\DateTime $submitDate = null)
    {
        $this->submitDate = $submitDate;

        return $this;
    }

    /**
     * Get submitDate.
     *
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * Set lastedit.
     *
     * @param \DateTime $lastedit
     *
     * @return Report
     */
    public function setLastedit(\DateTime $lastedit)
    {
        $this->lastedit = new \DateTime($lastedit->format('Y-m-d'));

        return $this;
    }

    /**
     * Get lastedit.
     *
     * @return \DateTime
     */
    public function getLastedit()
    {
        return $this->lastedit;
    }

    /**
     * Set furtherInformation.
     *
     * @param string $furtherInformation
     *
     * @return Report
     */
    public function setFurtherInformation($furtherInformation)
    {
        $furtherInformation = trim($furtherInformation, " \n");

        $this->furtherInformation = $furtherInformation;

        return $this;
    }

    /**
     * Get furtherInformation.
     *
     * @return string
     */
    public function getFurtherInformation()
    {
        return $this->furtherInformation;
    }

    /**
     * Set submitted.
     *
     * @param bool $submitted
     *
     * @return Report
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

        return $this;
    }

    /**
     * Get submitted.
     *
     * @return bool
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * Set client.
     *
     * @param Client $client
     *
     * @return Report
     */
    public function setClient(Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return Client
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
     * Add contacts.
     *
     * @param Contact $contacts
     *
     * @return Report
     */
    public function addContact(Contact $contacts)
    {
        $this->contacts[] = $contacts;

        return $this;
    }

    /**
     * Remove contacts.
     *
     * @param Contact $contacts
     */
    public function removeContact(Contact $contacts)
    {
        $this->contacts->removeElement($contacts);
    }

    /**
     * Get contacts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add accounts.
     *
     * @param BankAccount $accounts
     *
     * @return Report
     */
    public function addAccount(BankAccount $accounts)
    {
        $this->bankAccounts[] = $accounts;

        return $this;
    }

    /**
     * Remove accounts.
     *
     * @param BankAccount $accounts
     */
    public function removeAccount(BankAccount $accounts)
    {
        $this->bankAccounts->removeElement($accounts);
    }

    /**
     * Get accounts.
     *
     * @return BankAccount[]
     */
    public function getBankAccounts()
    {
        return $this->bankAccounts;
    }

    /**
     * @return MoneyTransfer[]
     */
    public function getMoneyTransfers()
    {
        return $this->moneyTransfers;
    }

    /**
     * @param MoneyTransfer $moneyTransfer
     *
     * @return \Report
     */
    public function addMoneyTransfers(MoneyTransfer $moneyTransfer)
    {
        $this->moneyTransfers->add($moneyTransfer);

        return $this;
    }

    /**
     * Add decisions.
     *
     * @param Decision $decision
     *
     * @return Report
     */
    public function addDecision(Decision $decision)
    {
        $this->decisions[] = $decision;

        return $this;
    }

    /**
     * Remove decisions.
     *
     * @param Decision $decision
     */
    public function removeDecision(Decision $decision)
    {
        $this->decisions->removeElement($decision);
    }

    /**
     * Get decisions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDecisions()
    {
        return $this->decisions;
    }

    /**
     * Add assets.
     *
     * @param Asset $assets
     *
     * @return Report
     */
    public function addAsset(Asset $assets)
    {
        $this->assets[] = $assets;

        return $this;
    }

    /**
     * Remove assets.
     *
     * @param Asset $assets
     */
    public function removeAsset(Asset $assets)
    {
        $this->assets->removeElement($assets);
    }

    /**
     * Get assets.
     *
     * @return Asset[]
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Get assets total value.
     *
     * @JMS\VirtualProperty
     * @JMS\Type("double")
     * @JMS\SerializedName("assets_total_value")
     * @JMS\Groups({"asset"})
     *
     * @return float
     */
    public function getAssetsTotalValue()
    {
        $ret = 0;
        foreach ($this->getAssets() as $asset) {
            $ret += $asset->getValueTotal();
        }

        return $ret;
    }

    /**
     * Set noAssetToAdd.
     *
     * @param bool $noAssetToAdd
     *
     * @return Report
     */
    public function setNoAssetToAdd($noAssetToAdd)
    {
        $this->noAssetToAdd = $noAssetToAdd;

        return $this;
    }

    /**
     * Get noAssetToAdd.
     *
     * @return bool
     */
    public function getNoAssetToAdd()
    {
        return $this->noAssetToAdd;
    }

    /**
     * @return bool
     */
    public function getNoTransfersToAdd()
    {
        return $this->noTransfersToAdd;
    }

    /**
     * @param bool $noTransfersToAdd
     */
    public function setNoTransfersToAdd($noTransfersToAdd)
    {
        $this->noTransfersToAdd = $noTransfersToAdd;

        return $this;
    }

    /**
     * @return VisitsCare
     */
    public function getVisitsCare()
    {
        return $this->visitsCare;
    }

    /**
     * @param VisitsCare $visitsCare
     *
     * @return Report
     */
    public function setVisitsCare(VisitsCare $visitsCare = null)
    {
        $this->visitsCare = $visitsCare;

        return $this;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param \AppBundle\Entity\Report\Action $action
     *
     * @return \AppBundle\Entity\Report\Report
     */
    public function setAction(Action $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return MentalCapacity
     */
    public function getMentalCapacity()
    {
        return $this->mentalCapacity;
    }

    /**
     * @param MentalCapacity $mentalCapacity
     */
    public function setMentalCapacity(MentalCapacity $mentalCapacity)
    {
        $this->mentalCapacity = $mentalCapacity;

        return $this;
    }

    /**
     * Set reasonForNoContact.
     *
     * @param string $reasonForNoContacts
     *
     * @return Report
     */
    public function setReasonForNoContacts($reasonForNoContacts)
    {
        $this->reasonForNoContacts = $reasonForNoContacts;

        return $this;
    }

    /**
     * Get reasonForNoContacts.
     *
     * @return string
     */
    public function getReasonForNoContacts()
    {
        return $this->reasonForNoContacts;
    }

    /**
     * Set reasonForNoDecisions.
     *
     * @param string $reasonForNoDecisions
     *
     * @return Report
     **/
    public function setReasonForNoDecisions($reasonForNoDecisions)
    {
        $this->reasonForNoDecisions = trim($reasonForNoDecisions, " \n");

        return $this;
    }

    /**
     * Get ReasonForNoDecisions.
     *
     * @return string
     */
    public function getReasonForNoDecisions()
    {
        return $this->reasonForNoDecisions;
    }

    /**
     * Set reportSeen.
     *
     * @param bool $reportSeen
     *
     * @return Report
     */
    public function setReportSeen($reportSeen)
    {
        $this->reportSeen = $reportSeen;

        return $this;
    }

    /**
     * Get reportSeen.
     *
     * @return bool
     */
    public function getReportSeen()
    {
        return $this->reportSeen;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function belongsToUser(User $user)
    {
        return in_array($user->getId(), $this->getClient()->getUserIds());
    }

    public function getAgreedBehalfDeputy()
    {
        return $this->agreedBehalfDeputy;
    }

    public function setAgreedBehalfDeputy($agreeBehalfDeputy)
    {
        $acceptedValues = ['only_deputy', 'more_deputies_behalf', 'more_deputies_not_behalf'];
        if ($agreeBehalfDeputy && !in_array($agreeBehalfDeputy, $acceptedValues)) {
            throw new \InvalidArgumentException(__METHOD__ . " {$agreeBehalfDeputy} given. Expected value: " . implode(' or ', $acceptedValues));
        }

        $this->agreedBehalfDeputy = $agreeBehalfDeputy;

        return $this;
    }

    public function getAgreedBehalfDeputyExplanation()
    {
        return $this->agreedBehalfDeputyExplanation;
    }

    public function setAgreedBehalfDeputyExplanation($agreedBehalfDeputyExplanation)
    {
        $this->agreedBehalfDeputyExplanation = $agreedBehalfDeputyExplanation;

        return $this;
    }

    /**
     * @return string
     */
    public function getBalanceMismatchExplanation()
    {
        return $this->balanceMismatchExplanation;
    }

    /**
     * @param string $balanceMismatchExplanation
     */
    public function setBalanceMismatchExplanation($balanceMismatchExplanation)
    {
        $this->balanceMismatchExplanation = $balanceMismatchExplanation;
    }

    /**
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("money_transactions_in")
     * @JMS\Groups({"transactionsIn"})
     *
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactionsIn()
    {
        return $this->moneyTransactions->filter(function ($t) {
            return $t->getType() == 'in';
        });
    }

    /**
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("money_transactions_out")
     * @JMS\Groups({"transactionsOut"})
     *
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactionsOut()
    {
        return $this->moneyTransactions->filter(function ($t) {
            return $t->getType() == 'out';
        });
    }

    /**
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactions()
    {
        return $this->moneyTransactions;
    }

    /**
     * @param mixed $moneyTransactions
     */
    public function setMoneyTransactions($moneyTransactions)
    {
        $this->moneyTransactions = $moneyTransactions;
    }

    /**
     * @param mixed $moneyTransactions
     */
    public function addMoneyTransaction(MoneyTransaction $t)
    {
        if (!$this->moneyTransactions->contains($t)) {
            $this->moneyTransactions->add($t);
        }
    }

    /**
     * @param mixed $debts
     */
    public function setDebts($debts)
    {
        $this->debts = $debts;
    }

    /**
     * @return Debt[]
     */
    public function getDebts()
    {
        return $this->debts;
    }

    /**
     * @param string $typeId
     *
     * @return Debt
     */
    public function getDebtByTypeId($typeId)
    {
        return $this->getDebts()->filter(function (Debt $debt) use ($typeId) {
            return $debt->getDebtTypeId() == $typeId;
        })->first();
    }

    /**
     * @param Debt $debt
     */
    public function addDebt(Debt $debt)
    {
        if (!$this->debts->contains($debt)) {
            $this->debts->add($debt);
        }

        return $this;
    }

    /**
     * Get assets total value.
     *
     * @JMS\VirtualProperty
     * @JMS\Type("string")
     * @JMS\SerializedName("debts_total_amount")
     * @JMS\Groups({"debt"})
     *
     * @return float
     */
    public function getDebtsTotalAmount()
    {
        $ret = 0;
        foreach ($this->getDebts() as $debt) {
            $ret += $debt->getAmount();
        }

        return $ret;
    }

    /**
     * @return mixed
     */
    public function getHasDebts()
    {
        return $this->hasDebts;
    }

    /**
     * @param mixed $hasDebts
     */
    public function setHasDebts($hasDebts)
    {
        $this->hasDebts = $hasDebts;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"transactionsIn"})
     * @JMS\Type("double")
     * @JMS\SerializedName("money_in_total")
     */
    public function getMoneyInTotal()
    {
        $ret = 0;
        foreach ($this->getMoneyTransactions() as $t) {
            if ($t->getType() == 'in') {
                $ret += $t->getAmount();
            }
        }

        return $ret;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"transactionsOut"})
     * @JMS\Type("double")
     * @JMS\SerializedName("money_out_total")
     */
    public function getMoneyOutTotal()
    {
        $ret = 0;
        foreach ($this->getMoneyTransactions() as $t) {
            if ($t->getType() == 'out') {
                $ret += $t->getAmount();
            }
        }

        return $ret;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance", "account"})
     * @JMS\Type("double")
     * @JMS\SerializedName("accounts_opening_balance_total")
     */
    public function getAccountsOpeningBalanceTotal()
    {
        $ret = 0;
        foreach ($this->getBankAccounts() as $a) {
            if ($a->getOpeningBalance() === null) {
                return;
            }
            $ret += $a->getOpeningBalance();
        }

        return $ret;
    }

    /**
     * Return sum of closing balances (if all of them have a value, otherwise returns null).
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance", "account"})
     * @JMS\Type("double")
     * @JMS\SerializedName("accounts_closing_balance_total")
     *
     * @return float
     */
    public function getAccountsClosingBalanceTotal()
    {
        $ret = 0;
        foreach ($this->getBankAccounts() as $a) {
            if ($a->getClosingBalance() === null) {
                return;
            }
            $ret += $a->getClosingBalance();
        }

        return $ret;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance"})
     * @JMS\Type("double")
     * @JMS\SerializedName("calculated_balance")
     */
    public function getCalculatedBalance()
    {
        if ($this->getAccountsOpeningBalanceTotal() === null) {
            return null;
        }

        return $this->getAccountsOpeningBalanceTotal()
        + $this->getMoneyInTotal()
        - $this->getMoneyOutTotal();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance"})
     * @JMS\Type("double")
     * @JMS\SerializedName("totals_offset")
     */
    public function getTotalsOffset()
    {
        if ($this->getCalculatedBalance() === null || $this->getAccountsClosingBalanceTotal() === null) {
            return null;
        }

        return $this->getCalculatedBalance() - $this->getAccountsClosingBalanceTotal();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance"})
     * @JMS\Type("boolean")
     * @JMS\SerializedName("totals_match")
     */
    public function getTotalsMatch()
    {
        return $this->getTotalsOffset() !== null && abs($this->getTotalsOffset()) < 0.2;
    }

    /**
     * @param Transaction[] $transactions
     *
     * @return array array of [category=>[entries=>[[id=>,type=>]], amountTotal[]]]
     */
    public function groupByCategory($transactions)
    {
        $ret = [];

        foreach ($transactions as $id => $t) {
            $cat = $t->getCategoryString();
            if (!isset($ret[$cat])) {
                $ret[$cat] = ['entries' => [], 'amountTotal' => 0];
            }
            $ret[$cat]['entries'][$id] = $t; // needed to find the corresponding transaction in the form
            $ret[$cat]['amountTotal'] += $t->getAmountsTotal();
        }

        return $ret;
    }

    public function isDue()
    {
        if (!$this->getEndDate() instanceof \DateTime) {
            return false;
        }

        // reset time on dates
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $reportDueOn = clone $this->getEndDate();
        $reportDueOn->setTime(0, 0, 0);

        return $today >= $reportDueOn;
    }

    /**
     * @return string
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param string $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Function to get the due date for a report based on the logic that the due date is 8 weeks
     * after the end of the report period.
     *
     * @return bool|\DateTime
     */
    public function getDueDate()
    {
        if (!$this->getEndDate() instanceof \DateTime) {
            return false;
        }
        return $this->getEndDate()->add(new \DateInterval('P56D'));
    }
}
