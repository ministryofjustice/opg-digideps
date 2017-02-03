<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="casrec")
 * @ORM\Entity
 */
class CasRec
{
    /**
     * @var int
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
     * @var string OPG102|OPG103|empty string
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="type_of_report", type="string", length=10, nullable=true)
     */
    private $typeOfReport;


    /**
     * @var array
     */
    private static $normalizeChars = [
        'Š' => 'S', 'š' => 's', 'Ð' => 'Dj', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
        'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U',
        'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
        'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u',
        'ú' => 'u', 'ü' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'ƒ' => 'f',
        'ă' => 'a', 'î' => 'i', 'â' => 'a', 'ș' => 's', 'ț' => 't', 'Ă' => 'A', 'Î' => 'I', 'Â' => 'A', 'Ș' => 'S', 'Ț' => 'T',
    ];

    /**
     * @param string $caseNumber
     * @param string $clientLastname
     * @param string $deputyNo
     * @param string $deputySurname
     * @param string $deputyPostCode
     * @param string $typeOfReport
     */
    public function __construct($caseNumber, $clientLastname, $deputyNo, $deputySurname, $deputyPostCode, $typeOfReport)
    {
        $this->caseNumber = self::normaliseCaseNumber($caseNumber);
        $this->clientLastname = self::normaliseSurname($clientLastname);
        $this->deputyNo = self::normaliseDeputyNo($deputyNo);
        $this->deputySurname = self::normaliseSurname($deputySurname);
        $this->deputyPostCode = self::normaliseSurname($deputyPostCode);
        $this->typeOfReport = $typeOfReport;
    }

    public static function normaliseSurname($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = strtr($value, self::$normalizeChars);
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

    /**
     * @return string
     */
    public function getTypeOfReport()
    {
        return $this->typeOfReport;
    }

}
