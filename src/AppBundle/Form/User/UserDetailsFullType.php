<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDetailsFullType extends UserDetailsBasicType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('address1', FormTypes\TextType::class)
        ->add('address2', FormTypes\TextType::class)
        ->add('address3', FormTypes\TextType::class)
        ->add('addressPostcode', FormTypes\TextType::class)
        ->add('addressCountry', FormTypes\CountryType::class, [
            'preferred_choices' => ['', 'GB'],
            'empty_value' => 'Please select ...',
        ])
        ->add('phoneMain', FormTypes\TextType::class)
        ->add('phoneAlternative', FormTypes\TextType::class)
        ->add('email', FormTypes\TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'settings',
            'validation_groups' => 'user_details_full',
        ]);
    }
}
