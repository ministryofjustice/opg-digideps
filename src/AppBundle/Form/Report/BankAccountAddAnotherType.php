<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Validator\Constraints\NotBlank;

class BankAccountAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'account.addAnother.notBlank';
    protected $translationDomain = 'report-bank-accounts';
}
