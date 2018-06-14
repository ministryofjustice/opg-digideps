<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
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

        $builder->add('email', FormTypes\TextType::class)
            ->add('firstname', FormTypes\TextType::class)
            ->add('lastname', FormTypes\TextType::class)
            ->add('addressPostcode', FormTypes\TextType::class)
            ->add('roleName', FormTypes\ChoiceType::class, $roleNameOptions)
            ->add('ndrEnabled', FormTypes\CheckboxType::class)
            ->add('save', FormTypes\SubmitType::class);
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
