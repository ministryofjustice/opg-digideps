<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', FormTypes\TextType::class)
            ->add('firstname', FormTypes\TextType::class)
            ->add('lastname', FormTypes\TextType::class)
            ->add('addressPostcode', FormTypes\TextType::class)
            ->add('roleType', FormTypes\ChoiceType::class, [
                'choices' => [
                    'addUserForm.roleType.deputy' => 'deputy',
                    'addUserForm.roleType.staff' => 'staff',
                ],
                'expanded' => true,
                'mapped' => false,
            ])
            ->add('roleName', FormTypes\HiddenType::class)
            ->add('roleNameDeputy', FormTypes\ChoiceType::class, [
                'choices' => [
                    'Lay Deputy' => User::ROLE_LAY_DEPUTY,
                    'Public Authority (named)' => User::ROLE_PA_NAMED,
                    'Professional Deputy (named)' => User::ROLE_PROF_NAMED,
                ],
                'placeholder' => 'Please select...',
                'mapped' => false,
            ])
            ->add('roleNameStaff', FormTypes\ChoiceType::class, [
                'choices' => [
                    'OPG Admin' => User::ROLE_ADMIN,
                    'Case manager' => User::ROLE_CASE_MANAGER,
                ],
                'placeholder' => 'Please select...',
                'mapped' => false,
            ])
            ->add('ndrEnabled', FormTypes\CheckboxType::class)
            ->add('save', FormTypes\SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (isset($data['roleType'])) {
                $field = 'roleName' . ucfirst($data['roleType']);
                $data['roleName'] = $data[$field];
            }

            $event->setData($data);
        });
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
