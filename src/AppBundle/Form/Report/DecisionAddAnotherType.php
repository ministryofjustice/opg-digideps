<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DecisionAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'decision.addAnother.notBlank';
    protected $translationDomain = 'report-decisions';
}
