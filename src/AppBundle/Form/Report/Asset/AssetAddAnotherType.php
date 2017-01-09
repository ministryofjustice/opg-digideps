<?php

namespace AppBundle\Form\Report\Asset;

use AppBundle\Form\Report\AbstractAddAnotherType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AssetAddAnotherType extends AbstractAddAnotherType
{
    protected $missingMessage = 'asset.addAnother.notBlank';
    protected $translationDomain = 'report-assets';
}
