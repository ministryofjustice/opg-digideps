<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Validator\Constraints\NotBlank;

class DecisionAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'decision.addAnother.notBlank';
    protected $translationDomain = 'report-decisions';
}
