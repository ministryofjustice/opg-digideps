<?php

namespace AppBundle\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(methods={"moreDetailsValidate"}, groups={"transactions"})
 */
class Transaction
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"transactionsIn", "transactionsOut"})
     */
    private $id;

    /**
     * @JMS\Type("string")
     */
    private $category;

    /**
     * @JMS\Type("string")
     */
    private $type;

    /**
     * @var array
     * 
     * @JMS\Type("array<string>")
     * @JMS\Groups({"transactionsIn", "transactionsOut"})
     */
    private $amounts;

    /**
     * @JMS\Type("string")
     *
     * @var float
     */
    private $amountsTotal;

    /**
     * @var string
     * @JMS\Groups({"transactionsIn", "transactionsOut"})
     * @JMS\Type("boolean")
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"transactionsIn", "transactionsOut"})
     * @JMS\Type("string")
     */
    private $moreDetails;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param string $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }

    /**
     * @return string
     */
    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    /**
     * @param string $moreDetails
     */
    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;
    }

    public function getAmounts()
    {
        return $this->amounts ? $this->amounts : [null];
    }

    /**
     * get amounts not null or empty string
     * 0.0 values are considered valid
     * non-numeric values are considered valid too,.
     * 
     * @return array
     */
    public function getNotNullAmounts()
    {
        return array_filter($this->getAmounts(), function ($a) {
            return $a !== null && trim($a) !== '';
        });
    }

    public function setAmounts($amounts)
    {
        $this->amounts = $amounts;

        return $this;
    }

    /**
     * flag moreDetails invalid if amount is given and moreDetails is empty
     * flag amount invalid if moreDetails is given and amount is empty.
     * 
     * @param ExecutionContextInterface $context
     */
    public function moreDetailsValidate(ExecutionContextInterface $context)
    {
        // if the transaction required no moreDetails, no validation is needed
        if (!$this->getHasMoreDetails()) {
            return;
        }

        $hasAtLeastOneAmount = count($this->getNotNullAmounts()) > 0;
        $hasMoreDetails = trim($this->getMoreDetails(), " \n") ? true : false;

        if ($hasAtLeastOneAmount && !$hasMoreDetails) {
            $context->addViolationAt('moreDetails', 'account.moneyInOut.moreDetails.empty');
        }

        if ($hasMoreDetails && !$hasAtLeastOneAmount) {
            $context->addViolationAt('amount', 'account.moneyInOut.amount.missingWhenDetailsFilled');
        }
    }

    public function getAmountsTotal()
    {
        return $this->amountsTotal;
    }

    public function setAmountsTotal($amountsTotal)
    {
        $this->amountsTotal = $amountsTotal;

        return $this;
    }
}
