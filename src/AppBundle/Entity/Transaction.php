<?php
namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;


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
     * @JMS\Type("string")
     * @JMS\Groups({"transactionsIn", "transactionsOut"})
     */
    private $amount;

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
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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


}
