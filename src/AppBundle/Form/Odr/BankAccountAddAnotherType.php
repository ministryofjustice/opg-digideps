<?php

namespace AppBundle\Form\Odr;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BankAccountAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'odr.account.addAnother.notBlank';
    protected $translationDomain = 'odr-bank-accounts';
}
