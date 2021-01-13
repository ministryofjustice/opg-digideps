<?php

namespace App\Form\Org;

use App\Entity\User;
use App\Validator\Constraints\EmailSameDomain;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class OrganisationMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $targetUser   = $options['targetUser'];

        $builder
            ->add('firstname', FormTypes\TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'user.firstname.notBlankOtherUser']),
                ]
            ])
            ->add('lastname', FormTypes\TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'user.lastname.notBlankOtherUser']),
                ]
            ])
            ->add('jobTitle', FormTypes\TextType::class, ['required' => !empty($targetUser)])
            ->add('phoneMain', FormTypes\TextType::class, ['required' => !empty($targetUser)])
            ->add('roleName', FormTypes\ChoiceType::class, [
                'choices' => [
                    'yes' => $options['role_admin'],
                    'no' => $options['role_member']
                ],
                'choice_translation_domain' => 'common',
                'expanded' => true,
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'user.role.notBlankPa']),
                ]
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if (!$user || null === $user->getId() || !$user->getActive()) {
                $form->add('email', FormTypes\TextType::class, [
                    'required' => true,
                    'constraints' => [
                        new Email(),
                    ]
                ]);
            }
        });

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'org-organisation',
            'data_class'         => User::class,
            'validation_groups'  => ['org_team_add', 'org_team_role_name'],
            'targetUser'         => null
        ])
        ->setRequired(['role_admin', 'role_member']);
    }

    public function getBlockPrefix()
    {
        return 'organisation_member';
    }
}
