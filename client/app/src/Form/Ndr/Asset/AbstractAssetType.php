<?php

namespace App\Form\Ndr\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Asset form.
 *
 * note: title is hidden (filled from the controller based on AssetTypeTitle form)
 */
abstract class AbstractAssetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addFields($builder, $options);

        $builder
                ->add('title', FormTypes\HiddenType::class)
                ->add('id', FormTypes\HiddenType::class)
                ->add('save', FormTypes\SubmitType::class);
    }

    abstract protected function addFields($builder, $options);

    public function getBlockPrefix()
    {
        return 'oasset';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ndr-assets',
            'validation_groups' => $this->getValidationGroups(),
        ]);
    }

    protected function getValidationGroups()
    {
        return [];
    }
}
