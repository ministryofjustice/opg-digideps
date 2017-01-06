<?php

namespace AppBundle\Form\Odr\Asset;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Asset form.
 *
 * note: title is hidden (filled from the controller based on AssetTypeTitle form)
 */
class AssetTypeOther extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', 'number', [
                'grouping' => true,
                'precision' => 2,
                'invalid_message' => 'asset.value.type',
            ])
            ->add('description', 'textarea')
            ->add('valuationDate', 'date', ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Enter a valid date',
            ]);


        $builder
            ->add('title', 'hidden')
            ->add('id', 'hidden')
            ->add('save', 'submit');
    }

    public function getName()
    {
        return 'odr_asset';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-assets',
            'validation_groups' => $this->getValidationGroups(),
        ]);
    }

    protected function getValidationGroups()
    {
        return [];
    }
}
