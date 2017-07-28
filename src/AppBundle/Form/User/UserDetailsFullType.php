<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserDetailsFullType extends UserDetailsBasicType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('address1', 'text')
        ->add('address2', 'text')
        ->add('address3', 'text')
        ->add('addressPostcode', 'text')
        ->add('addressCountry', 'country', [
            'preferred_choices' => ['', 'GB'],
            'empty_value' => 'Please select ...',
        ])
        ->add('phoneMain', 'text')
        ->add('phoneAlternative', 'text')
        ->add('email', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'settings',
            'validation_groups' => 'user_details_full',
        ]);
    }
}
