<?php

namespace AppBundle\Form\Report\Asset;

use AppBundle\Form\Report\AbstractAddAnotherType;
use Symfony\Component\Validator\Constraints\NotBlank;

class AssetAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'asset.addAnother.notBlank';
    protected $translationDomain = 'report-assets';
}
