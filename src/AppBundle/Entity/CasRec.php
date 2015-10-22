<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="casrec")
 * @ORM\Entity
 */
class CasRec 
{
    /**
     * @var integer
     * 
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="casrec_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @var string
     *
     * @ORM\Column(name="client_case_number", type="string", length=20, nullable=false)
     */
    private $caseNumber;
    
    /**
     * @JMS\Type("string")
     * @var string
     *
     * @ORM\Column(name="client_lastname", type="string", length=50, nullable=false)
     */
    private $clientLastname;
    
    /**
     * @JMS\Type("string")
     * @var string
     *
     * @ORM\Column(name="deputy_no", type="string", length=100, nullable=false)
     */
    private $deputyNo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="deputy_lastname", type="string", length=100, nullable=true)
     * @JMS\Type("string")
     * @JMS\Groups({"basic", "audit_log"})
     */
    private $deputySurname;
    
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @ORM\Column(name="deputy_postcode", type="string", length=10, nullable=true)
     */
    private $deputyPostCode;
    
    /**
     * @param string $caseNumber
     * @param string $clientLastname
     * @param string $deputyNo
     * @param string $deputySurname
     * @param string $deputyPostCode
     */
    public function __construct($caseNumber, $clientLastname, $deputyNo, $deputySurname, $deputyPostCode)
    {
        $this->caseNumber = $caseNumber;
        $this->clientLastname = $clientLastname;
        $this->deputyNo = $deputyNo;
        $this->deputySurname = $deputySurname;
        $this->deputyPostCode = $deputyPostCode;
    }
    
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }


    public function getClientLastname()
    {
        return $this->clientLastname;
    }


    public function getDeputyNo()
    {
        return $this->deputyNo;
    }


    public function getDeputySurname()
    {
        return $this->deputySurname;
    }


    public function getDeputyPostCode()
    {
        return $this->deputyPostCode;
    }

}
