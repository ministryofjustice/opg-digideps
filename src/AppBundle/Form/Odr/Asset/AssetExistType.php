<?php

namespace AppBundle\Form\Odr\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AssetExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('noAssetToAdd', 'choice', [
                'choices' => [0 => 'Yes', 1 => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'odr.asset.exist.notBlank', 'groups' => ['exist']])],
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-assets',
            'validation_groups' => ['exist'],
        ]);
    }

    public function getName()
    {
        return 'odr_asset_exist';
    }
}
