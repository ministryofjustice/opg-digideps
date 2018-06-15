<?php

namespace AppBundle\Form\Ad;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', FormTypes\TextType::class)
            ->add('lastname', FormTypes\TextType::class)
            ->add('roleName', FormTypes\ChoiceType::class, [
                    'placeholder' => null,
                    'choices'     => $options['roleChoices'],
                    'data'        => $options['roleNameSetTo'],
                ]
            )
            ->add('ndrEnabled', FormTypes\CheckboxType::class, [
                'data'     => true,
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ad',
            'validation_groups'  => ['ad_add_user'],
        ])
            ->setRequired(['roleChoices', 'roleNameSetTo']);
    }

    public function getBlockPrefix()
    {
        return 'ad';
    }
}
