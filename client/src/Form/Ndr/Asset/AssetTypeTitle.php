<?php

namespace App\Form\Ndr\Asset;

use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetTypeTitle extends \App\Form\Report\Asset\AssetTypeTitle
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ndr-assets',
            'validation_groups' => 'title_only',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ndr_asset_title';
    }
}
