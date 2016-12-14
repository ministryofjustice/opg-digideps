<?php

namespace AppBundle\Form\Odr\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AssetAddAnotherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addAnother', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [new NotBlank(['message' => 'odr.asset.addAnother.notBlank'])],
            ))
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-assets',
        ]);
    }

    public function getName()
    {
        return 'odr_assets_add_another';
    }
}
