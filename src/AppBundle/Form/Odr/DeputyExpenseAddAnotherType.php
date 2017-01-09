<?php

namespace AppBundle\Form\Odr;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeputyExpenseAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'odr.expenses.addAnother.notBlank';
    protected $translationDomain = 'odr-deputy-expenses';
}
