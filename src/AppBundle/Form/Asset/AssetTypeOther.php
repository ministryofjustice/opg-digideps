<?php

namespace AppBundle\Form\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Asset form
 * 
 * note: title is hidden (filled from the controller based on AssetTypeTitle form)
 */
class AssetTypeOther extends AbstractAssetType
{

    protected function addFields($builder, $options)
    {
        $builder
                ->add('value', 'number', [
                    'grouping' => true,
                    'precision' => 2,
                    'invalid_message' => 'asset.value.type'
                ])
                ->add('description', 'textarea')
                ->add('valuationDate', 'date', [ 'widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'dd-MM-yyyy',
                    'invalid_message' => 'Enter a valid date'
        ]);
    }

}
