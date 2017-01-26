<?php

namespace AppBundle\Form\Report\Gift;

use AppBundle\Form\Report\AbstractAddAnotherType;
use Symfony\Component\Validator\Constraints\NotBlank;

class GiftAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'gifts.addAnother.notBlank';
    protected $translationDomain = 'report-gifts';
}
