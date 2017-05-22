<?php

namespace AppBundle\Form\Odr\Asset;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
