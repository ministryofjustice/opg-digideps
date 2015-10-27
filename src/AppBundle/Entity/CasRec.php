<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\Length(min=2, max=10, minMessage="user.addressPostcode.minLength", maxMessage="user.addressPostcode.maxLength", groups={"user_details_full"} )
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
        $this->caseNumber = self::normaliseValue($caseNumber);
        $this->clientLastname = self::normaliseValue($clientLastname);
        $this->deputyNo = self::normaliseValue($deputyNo);
        $this->deputySurname = self::normaliseValue($deputySurname);
        $this->deputyPostCode = self::normaliseValue($deputyPostCode);
    }
    
    public static function normaliseValue($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = preg_replace('/ */', '', $value);
        return $value;
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
