<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('content', FormTypes\TextareaType::class)
            ->add('enabled', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => true, 'No' => false],
                'expanded' => true,
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    /**
     * Set default form options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['setting'],
                'translation_domain' => 'admin-settings',
            ]
        );
    }
}
