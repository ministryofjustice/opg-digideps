<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @JMS\ExclusionPolicy("none")
 */
class Concern
{
    use Traits\HasReportTrait;
    
    /**
     * @JMS\Type("integer")
     * @var integer
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="concern.doYouExpectFinancialDecisions.notBlank", groups={"concern"})
     * 
     */
    private $doYouExpectFinancialDecisions;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="concern.doYouHaveConcerns.notBlank", groups={"concern"})
     */
    private $doYouHaveConcerns;
    
    public function getId()
    {
        return $this->id;
    }

    public function getDoYouExpectFinancialDecisions()
    {
        return $this->doYouExpectFinancialDecisions;
    }

    public function getDoYouHaveConcerns()
    {
        return $this->doYouHaveConcerns;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setDoYouExpectFinancialDecisions($doYouExpectFinancialDecisions)
    {
        $this->doYouExpectFinancialDecisions = $doYouExpectFinancialDecisions;
        return $this;
    }

    public function setDoYouHaveConcerns($doYouHaveConcerns)
    {
        $this->doYouHaveConcerns = $doYouHaveConcerns;
        return $this;
    }


    
}
