<?php

namespace AppBundle\Entity\Odr\Traits;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

trait AgreeTrait
{
    /** @var bool
     * @JMS\Type("boolean")
     * @Assert\True(message="odr.agree", groups={"declare"} )
     */
    private $agree;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"submit"})
     * @Assert\NotBlank(message="odr.agreedBehalfDeputy.notBlank", groups={"declare"} )
     */
    private $agreedBehalfDeputy;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"submit"})
     * @Assert\NotBlank(message="odr.agreedBehalfDeputyExplanation.notBlank", groups={"declare-explanation"} )
     */
    private $agreedBehalfDeputyExplanation;

    /**
     * @return bool
     */
    public function isAgree()
    {
        return $this->agree;
    }

    /**
     * @param bool $agree
     */
    public function setAgree($agree)
    {
        $this->agree = $agree;
    }

    /**
     * @return string
     */
    public function getAgreedBehalfDeputy()
    {
        return $this->agreedBehalfDeputy;
    }

    /**
     * @param string $agreedBehalfDeputy
     */
    public function setAgreedBehalfDeputy($agreedBehalfDeputy)
    {
        $this->agreedBehalfDeputy = $agreedBehalfDeputy;
    }

    /**
     * @return string
     */
    public function getAgreedBehalfDeputyExplanation()
    {
        return $this->agreedBehalfDeputyExplanation;
    }

    /**
     * @param string $agreedBehalfDeputyExplanation
     */
    public function setAgreedBehalfDeputyExplanation($agreedBehalfDeputyExplanation)
    {
        $this->agreedBehalfDeputyExplanation = $agreedBehalfDeputyExplanation;
    }
}
