<?php

namespace AppBundle\Form\Odr\Asset;

use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetTypeTitle extends \AppBundle\Form\Report\Asset\AssetTypeTitle
{
    public function configureOptions(OptionsResolver $resolver)
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
