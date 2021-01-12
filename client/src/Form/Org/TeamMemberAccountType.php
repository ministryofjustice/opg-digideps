<?php

namespace App\Form\Org;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TeamMemberAccountType
 */
class TeamMemberAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $team         = $options['team'];
        /** @var User $loggedInUser */
        $loggedInUser = $options['loggedInUser'];
        $targetUser   = $options['targetUser'];

        $builder
            ->add('firstname', FormTypes\TextType::class, ['required' => true])
            ->add('lastname', FormTypes\TextType::class, ['required' => true])
            ->add('jobTitle', FormTypes\TextType::class, ['required' => !empty($targetUser)])
            ->add('phoneMain', FormTypes\TextType::class, ['required' => !empty($targetUser)]);

        if ($team->canAddAdmin($targetUser)) {
            if ($loggedInUser->isProfAdministrator() || $loggedInUser->isProfNamedDeputy()) {
                // PROF ROLES
                $builder->add('roleName', FormTypes\ChoiceType::class, [
                    'choices' => array_flip([User::ROLE_PROF_ADMIN => 'Yes', User::ROLE_PROF_TEAM_MEMBER => 'No']),
                    'expanded' => true,
                    'required' => true
                ]);
            } elseif ($loggedInUser->isPaAdministrator() || $loggedInUser->isPaNamedDeputy()) {
                // PA ROLES
                $builder->add('roleName', FormTypes\ChoiceType::class, [
                    'choices' => array_flip([User::ROLE_PA_ADMIN => 'Yes', User::ROLE_PA_TEAM_MEMBER => 'No']),
                    'expanded' => true,
                    'required' => true
                ]);
            }
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if (!$user || null === $user->getId()) {
                $form->add('email', FormTypes\TextType::class, [
                    'required' => true
                ]);
            }
        });

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'org-team',
            'data_class'         => User::class,
            'targetUser'         => null
        ])
        ->setRequired(['team','loggedInUser','validation_groups'])
        ->setAllowedTypes('team', Team::class)
        ->setAllowedTypes('loggedInUser', User::class)
        ->setAllowedTypes('validation_groups', 'array')
        ;
    }

    public function getBlockPrefix()
    {
        return 'team_member_account';
    }
}
