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
     * @var string
     * 
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="client_case_number", type="string", length=20, nullable=false)
     */
    private $caseNumber;
    
    /**
     * @var string
     * 
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="client_lastname", type="string", length=50, nullable=false)
     */
    private $clientLastname;
    
    /**
     * @var string
     * 
     * @JMS\Type("string")
     * 
     * @Assert\NotBlank()
     * 
     * @ORM\Column(name="deputy_no", type="string", length=100, nullable=false)
     */
    private $deputyNo;
    
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * 
     * @ORM\Column(name="deputy_lastname", type="string", length=100, nullable=true)
     * 
     * @JMS\Type("string")
     */
    private $deputySurname;
    
    /**
     * @var string
     *
     * @JMS\Type("string")
     * 
     * @ORM\Column(name="deputy_postcode", type="string", length=10, nullable=true)
     * 
     * @Assert\Length(min=2, max=10, minMessage="user.addressPostcode.minLength", maxMessage="user.addressPostcode.maxLength" )
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
        $this->caseNumber = self::normaliseCaseNumber($caseNumber);
        $this->clientLastname = self::normaliseSurname($clientLastname);
        $this->deputyNo = self::normaliseDeputyNo($deputyNo);
        $this->deputySurname = self::normaliseSurname($deputySurname);
        $this->deputyPostCode = self::normaliseSurname($deputyPostCode);
    }
    
    
    public static function normaliseSurname($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        // remove MBE suffix
        $value = preg_replace('/ (mbe|m b e)$/i', '', $value);
        // remove characters that are not a-z or 0-9 or spaces
        $value = preg_replace('/([^a-z0-9])/i', '', $value);
        
        return $value;
    }
    
    public static function normaliseCaseNumber($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = preg_replace('#^([a-z0-9]+/)#i', '', $value);
        
        return $value;
    }
    
    public static function normaliseDeputyNo($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        
        return $value;
    }
    
    public static function normalisePostCode($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        // remove MBE suffix
        $value = preg_replace('/ (mbe|m b e)$/i', '', $value);
        // remove characters that are not a-z or 0-9 or spaces
        $value = preg_replace('/([^a-z0-9])/i', '', $value);
        
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
