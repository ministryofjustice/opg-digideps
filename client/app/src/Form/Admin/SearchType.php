<?php

namespace App\Form\Admin;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('q', FormTypes\TextType::class)
            ->add('role_name', FormTypes\ChoiceType::class, [
                'choices' => array_flip([
                    '' => 'ALL ROLES',
                    User::ROLE_SUPER_ADMIN => 'Super Admin',
                    User::ROLE_ADMIN_MANAGER => 'Admin Manager',
                    User::ROLE_ADMIN => 'Admin',
                    User::ROLE_LAY_DEPUTY => 'Lay Deputy',
                    User::ROLE_AD => 'Assisted Digital',
                    'ROLE_PA%' => 'All Public Authority Deputies',
                    User::ROLE_PA_NAMED => 'Public Authority Deputies (Named) ',
                    'ROLE_PROF%' => 'All Professional Deputies',
                    User::ROLE_PROF_NAMED => 'Professional Deputies (Named)',
                ]),
            ])
            ->add('ndr_enabled', FormTypes\CheckboxType::class)
            ->add('include_clients', FormTypes\CheckboxType::class)
            ->add('search', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'validation_groups' => ['admin_add_user'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin';
    }
}
