<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Validator\Constraints\NotBlank;

class ContactAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'contact.addAnother.notBlank';
    protected $translationDomain = 'report-contacts';
}
