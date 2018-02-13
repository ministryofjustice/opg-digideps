<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                    'ROLE_PA_%'   => 'All Public Authority deputies',
                    User::ROLE_PA_NAMED   => 'Public Authorities (named) ',
                    'ROLE_PROF_%' => 'All Professional Deputies',
                    User::ROLE_PROF_NAMED => 'Professional Deputies (named)',
                ],
            ])
            ->add('ndr_enabled', 'checkbox')
            ->add('search', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
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
