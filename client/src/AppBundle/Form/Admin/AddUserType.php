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
        $deputyRoles = [User::ROLE_LAY_DEPUTY, User::ROLE_PA_NAMED, User::ROLE_PROF_NAMED];
        $staffRoles = [User::ROLE_ADMIN, User::ROLE_CASE_MANAGER];

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
                'choices' => $deputyRoles,
                'choice_label' => function($choice) {
                    return 'addUserForm.roleName.options.' . $choice;
                },
                'placeholder' => 'addUserForm.roleName.defaultOption',
                'mapped' => false,
            ])
            ->add('roleNameStaff', FormTypes\ChoiceType::class, [
                'choices' => $staffRoles,
                'choice_label' => function($choice) {
                    return 'addUserForm.roleName.options.' . $choice;
                },
                'placeholder' => 'addUserForm.roleName.defaultOption',
                'mapped' => false,
            ])
            ->add('ndrEnabled', FormTypes\CheckboxType::class)
            ->add('save', FormTypes\SubmitType::class);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($staffRoles) {
            $user = $event->getData();
            $form = $event->getForm();

            if (in_array($user->getRoleName(), $staffRoles) || $user->getRoleName() === 'ROLE_AD') {
                $form->get('roleType')->setData('staff');
                $form->get('roleNameStaff')->setData($user->getRoleName());
            } else {
                $form->get('roleType')->setData('deputy');
                $form->get('roleNameDeputy')->setData($user->getRoleName());
            }
        });

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
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin';
    }
}
