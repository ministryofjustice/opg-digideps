<?php

namespace AppBundle\Form\Odr\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AssetTypeTitle extends \AppBundle\Form\Report\Asset\AssetTypeTitle
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-assets',
            'validation_groups' => 'title_only',
        ]);
    }

    public function getName()
    {
        return 'odr_asset_title';
    }
}
