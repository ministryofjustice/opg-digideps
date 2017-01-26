<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Validator\Constraints\NotBlank;

class MoneyTransferAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'transfer.addAnother.notBlank';
    protected $translationDomain = 'report-money-transfer';
}
