<?php

namespace AppBundle\Form\Ad;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('roleName', 'choice', [
                    'empty_value' => null,
                    'choices'     => $options['roleChoices'],
                    'data'        => $options['roleNameSetTo'],
                ]
            )
            ->add('ndrEnabled', 'checkbox', [
                'data'     => true,
            ])
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ad',
            'validation_groups'  => ['ad_add_user'],
        ])
            ->setRequired(['roleChoices', 'roleNameSetTo']);
    }

    public function getName()
    {
        return 'ad';
    }
}
