<?php

namespace AppBundle\Entity\Odr;

use AppBundle\Entity\Client;
use AppBundle\Entity\Odr\Traits\OdrIncomeBenefitTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Odr
{
    use OdrIncomeBenefitTrait;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"submit"})
     *
     * @var bool
     */
    private $submitted;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     * @JMS\Groups({"submit"})
     */
    private $submitDate;

    /**
     * @JMS\Type("AppBundle\Entity\Client")
     *
     * @var Client
     */
    private $client;

    /**
     * @JMS\Type("AppBundle\Entity\Odr\VisitsCare")
     *
     * @var VisitsCare
     */
    private $visitsCare;

    /**
     * @JMS\Type("array<AppBundle\Entity\Odr\BankAccount>")
     *
     * @var BankAccount
     */
    private $bankAccounts;


    /**
     * @JMS\Type("array<AppBundle\Entity\Odr\Debt>")
     * @JMS\Groups({"debts"})
     *
     * @var ArrayCollection
     */
    private $debts;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debts"})
     *
     * @Assert\NotBlank(message="odr.hasDebts.notBlank", groups={"debts"})
     *
     * @var string
     */
    private $hasDebts;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debts"})
     *
     * @var decimal
     */
    private $debtsTotalAmount;

    /**
     * @JMS\Type("array<AppBundle\Entity\Odr\Asset>")
     *
     * @var Asset[]
     */
    private $assets;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"noAssetsToAdd"})
     *
     * @var bool
     */
    private $noAssetToAdd;


    /**
     * @return decimal
     */
    public function getBankAccountsBalanceTotal()
    {
        $ret = 0;
        foreach ($this->getBankAccounts() as $bankAccount) {
            $ret += $bankAccount->getBalanceOnCourtOrderDate();
        }
        return $ret;
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
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * @param \DateTime $submitDate
     */
    public function setSubmitDate($submitDate)
    {
        $this->submitDate = $submitDate;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;

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
     */
    public function setVisitsCare($visitsCare)
    {
        $this->visitsCare = $visitsCare;

        return $this;
    }

    /**
     * @return BankAccount[]
     */
    public function getBankAccounts()
    {
        return $this->bankAccounts;
    }

    /**
     * @param BankAccount[] $bankAccount
     */
    public function setBankAccounts($bankAccounts)
    {
        $this->bankAccounts = $bankAccounts;
    }

    /**
     * @return bool
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * @param bool $submitted
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

        return $this;
    }

    public function isDue()
    {
        return false;
    }

    /**
     * Return the due date (calculated as court order date + 40 days).
     *
     * @return \DateTime $dueDate
     */
    public function getDueDate()
    {
        $client = $this->getClient();
        if (!$client instanceof Client) {
            return;
        }

        $cod = $client->getCourtDate();

        if (!$cod instanceof \DateTime) {
            return;
        }
        $dueDate = clone $cod;
        $dueDate->modify('+40 days');

        return $dueDate;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasBankAccountWithId($id)
    {
        foreach ($this->getBankAccounts() as $bankAccount) {
            if ($bankAccount->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return decimal
     */
    public function getBalanceOnCourtOrderDateTotal()
    {
        $ret = 0;
        foreach ($this->getBankAccounts() as $account) {
            $ret += $account->getBalanceOnCourtOrderDate();
        }
        return $ret;
    }

    /**
     * Get debts total value.
     *
     * @return float
     */
    public function getDebtsTotalValue()
    {
        $ret = 0;
        foreach ($this->getDebts() as $debt) {
            $ret += $debt->getAmount();
        }

        return $ret;
    }

    /**
     * @return Debt[]
     */
    public function getDebts()
    {
        return $this->debts;
    }

    /**
     * @param Debt[] $debts
     */
    public function setDebts($debts)
    {
        $this->debts = $debts;

        return $this;
    }

    /**
     * @return decimal
     */
    public function getDebtsTotalAmount()
    {
        return $this->debtsTotalAmount;
    }

    /**
     * @param decimal $debtsTotalAmount
     */
    public function setDebtsTotalAmount($debtsTotalAmount)
    {
        $this->debtsTotalAmount = $debtsTotalAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getHasDebts()
    {
        return $this->hasDebts;
    }

    /**
     * @param string $hasDebts
     */
    public function setHasDebts($hasDebts)
    {
        $this->hasDebts = $hasDebts;

        return $this;
    }

    public function hasAtLeastOneDebtsWithValidAmount()
    {
        foreach ($this->debts as $debt) {
            if ($debt->getAmount()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function debtsValid(ExecutionContextInterface $context)
    {
        if ($this->getHasDebts() == 'yes' && !$this->hasAtLeastOneDebtsWithValidAmount()) {
            $context->addViolation('odr.hasDebts.mustHaveAtLeastOneDebt');
        }
    }


    /**
     * @param array $assets
     *
     * @return self
     */
    public function setAssets($assets)
    {
        $this->assets = $assets;

        return $this;
    }

    /**
     * @return array $assets
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Used in the list view
     * AssetProperty is considered having title "Property"
     * Artwork, Antiques, Jewellery are grouped into "Artwork, antiques and jewellery".
     *
     * @return array $assets e.g. [Property => [asset1, asset2], Bonds=>[]...]
     */
    public function getAssetsGroupedByTitle()
    {
        // those needs to be grouped together
        $titleToGroupOverride = [
            'Artwork' => 'Artwork, antiques and jewellery',
            'Antiques' => 'Artwork, antiques and jewellery',
            'Jewellery' => 'Artwork, antiques and jewellery',
        ];

        $ret = [];
        foreach ($this->assets as $asset) {
            if ($asset instanceof AssetProperty) {
                $ret['Property'][$asset->getId()] = $asset;
            } elseif ($asset instanceof AssetOther) {
                $title = isset($titleToGroupOverride[$asset->getTitle()]) ?
                    $titleToGroupOverride[$asset->getTitle()] : $asset->getTitle();
                $ret[$title][$asset->getId()] = $asset;
            }
        }

        return $ret;

        // order categories
        ksort($ret);
        // order assets inside by key
        foreach ($ret as &$row) {
            ksort($row);
        }

        return $ret;
    }

    /**
     * @return boolean
     */
    public function getNoAssetToAdd()
    {
        return $this->noAssetToAdd;
    }

    /**
     * @param boolean $noAssetToAdd
     * @return Odr
     */
    public function setNoAssetToAdd($noAssetToAdd)
    {
        $this->noAssetToAdd = $noAssetToAdd;
        return $this;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasAssetWithId($id)
    {
        foreach ($this->getAssets() as $asset) {
            if ($asset->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get assets total value.
     *
     * @return float
     */
    public function getAssetsTotalValue()
    {
        $ret = 0;
        foreach ($this->getAssets() as $asset) {
            $ret += $asset->getValue();
        }

        return $ret;
    }

}
