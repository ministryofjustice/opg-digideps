<?php

namespace AppBundle\Form\Odr;

use Symfony\Component\Validator\Constraints\NotBlank;

class DeputyExpenseAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'odr.expenses.addAnother.notBlank';
    protected $translationDomain = 'odr-deputy-expenses';
}
