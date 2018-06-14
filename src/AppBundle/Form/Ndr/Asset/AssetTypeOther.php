<?php

namespace AppBundle\Form\Ndr\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('value', FormTypes\NumberType::class, [
                'grouping' => true,
                'precision' => 2,
                'invalid_message' => 'asset.value.type',
            ])
            ->add('description', FormTypes\TextareaType::class)
            ->add('valuationDate', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Enter a valid date',
            ]);


        $builder
            ->add('title', FormTypes\HiddenType::class)
            ->add('id', FormTypes\HiddenType::class)
            ->add('save', FormTypes\SubmitType::class);
    }

    public function getBlockPrefix()
    {
        return 'ndr_asset';
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
