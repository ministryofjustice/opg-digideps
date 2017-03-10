<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserDetailsPaType extends UserDetailsBasicType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('jobTitle', 'text')
        //->add('paTeamName', 'text')
        ->add('phoneMain', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'user-details',
            'validation_groups' => 'user_details_pa',
        ]);
    }
}
