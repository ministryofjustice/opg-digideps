<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;

class UserDetailsPaType extends UserDetailsBasicType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('jobTitle', FormTypes\TextType::class)
            ->add('phoneMain', FormTypes\TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'settings',
            'validation_groups' => 'user_details_org',
        ]);
    }
}
