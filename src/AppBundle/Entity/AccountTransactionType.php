<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Account
 * @ORM\Entity
 * @ORM\Table(name="account_transaction_type")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"in" = "AccountTransactionTypeIn", "out" = "AccountTransactionTypeOut"})
 */
abstract class AccountTransactionType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="string", nullable=false)
     * @ORM\Id
     */
    private $id;
    
    private $type;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="has_more_details", type="boolean", nullable=false)
     */
    private $hasMoreDetails;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="display_order", type="integer", nullable=true)
     */
    private $displayOrder;
    
}
