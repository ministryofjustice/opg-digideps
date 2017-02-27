<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('q', 'text')
            ->add('role_name', 'choice', [
                'choices' => [
                    ''                    => 'ALL ROLES',
                    User::ROLE_ADMIN      => 'OPG Admin',
                    User::ROLE_LAY_DEPUTY => 'Lay Deputy',
                    User::ROLE_AD         => 'Assisted Digital',
                    User::ROLE_PA         => 'Public Authority',
                ],
            ])
            ->add('odr_enabled', 'checkbox')
            ->add('search', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'validation_groups'  => ['admin_add_user'],
        ]);
    }

    public function getName()
    {
        return 'admin';
    }
}
