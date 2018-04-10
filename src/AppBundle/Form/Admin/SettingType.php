<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('content', 'textarea')
            ->add('enabled', 'choice', [
                'choices' => [true => 'Yes', false => 'No'],
                'expanded' => true,
            ])
            ->add('save', 'submit');
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
