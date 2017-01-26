<?php

namespace AppBundle\Form\Odr;

use Symfony\Component\Validator\Constraints\NotBlank;

class BankAccountAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'odr.account.addAnother.notBlank';
    protected $translationDomain = 'odr-bank-accounts';
}
