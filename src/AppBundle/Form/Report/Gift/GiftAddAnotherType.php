<?php

namespace AppBundle\Form\Report\Gift;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class GiftAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'gifts.addAnother.notBlank';
    protected $translationDomain = 'report-gifts';
}
