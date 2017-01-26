<?php

namespace AppBundle\Form\Odr\Asset;

use AppBundle\Form\Odr\AbstractAddAnotherType;
use Symfony\Component\Validator\Constraints\NotBlank;

class AssetAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'odr.asset.addAnother.notBlank';
    protected $translationDomain = 'odr-assets';
}
