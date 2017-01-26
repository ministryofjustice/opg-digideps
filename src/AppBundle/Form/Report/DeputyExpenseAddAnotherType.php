<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Validator\Constraints\NotBlank;

class DeputyExpenseAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'expenses.addAnother.notBlank';
    protected $translationDomain = 'deputy-expenses';
}
