<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Odr\Odr;
use AppBundle\Form\Report\OtherInfoType as OtherInfoTypeReport;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OtherInfoType extends OtherInfoTypeReport
{
    protected $translationDomain = 'odr-more-info';
}
