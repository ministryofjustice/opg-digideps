<?php

namespace AppBundle\Form\Ndr\Asset;

use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetTypeTitle extends \AppBundle\Form\Report\Asset\AssetTypeTitle
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
