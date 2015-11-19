<?php
namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;


class Transaction
{
    /**
     * @JMS\Type("string")
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
     */
    private $amount;
}
