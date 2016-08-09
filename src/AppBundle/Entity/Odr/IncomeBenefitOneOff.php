<?php

namespace AppBundle\Entity\Odr;

use AppBundle\Entity\Traits\OdrIncomeBenefitSingleTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="odr_income_one_off")
 */
class IncomeBenefitOneOff
{
    use OdrIncomeBenefitSingleTrait;

    public static $oneOffKeys = [
        'bequest_or_inheritance' => false,
        'cash_gift_received' => false,
        'refunds' => false,
        'sale_of_an_asset' => false,
        'sale_of_investment' => false,
        'sale_of_property' => false,
    ];

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"odr-income-state-benefits"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_oneoff_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Odr
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Odr\Odr")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id")
     */
    private $odr;

}
