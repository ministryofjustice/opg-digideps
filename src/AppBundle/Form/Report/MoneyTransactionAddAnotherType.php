<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class MoneyTransactionAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'moneyTransaction.addAnother.notBlank';
    protected $translationDomain = 'report-money-transaction';
}
