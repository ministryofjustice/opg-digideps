<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roleNameOptions = [
            'choices'     => $options['options']['roleChoices'],
            'empty_value' => $options['options']['roleNameEmptyValue'],
        ];

        if (!empty($options['options']['roleNameSetTo'])) {
            $roleNameOptions['data'] = $options['options']['roleNameSetTo'];
            $roleNameOptions['disabled'] = 'disabled';
        }

        $builder->add('email', 'text')
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('addressPostcode', 'text')
            ->add('roleName', 'choice', $roleNameOptions)
            ->add('ndrEnabled', 'checkbox')
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'validation_groups' => ['admin_add_user'],
        ])
        ->setRequired(['options']);
    }

    public function getBlockPrefix()
    {
        return 'admin';
    }
}
