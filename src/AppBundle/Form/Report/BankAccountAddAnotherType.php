<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BankAccountAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'account.addAnother.notBlank';
    protected $translationDomain = 'report-bank-accounts';
}
