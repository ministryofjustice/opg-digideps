<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Validator\Constraints\NotBlank;

class MoneyTransactionAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'moneyTransaction.addAnother.notBlank';
    protected $translationDomain = 'report-money-transaction';
}
