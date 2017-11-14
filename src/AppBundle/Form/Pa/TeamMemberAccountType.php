<?php

namespace AppBundle\Form\Pa;

use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TeamMemberAccountType
 **
 * @package AppBundle\Form\Pa
 */
class TeamMemberAccountType extends AbstractType
{
    /**
     * @var Team
     */
    private $team;

    /**
     * @var User
     */
    private $loggedInUser = null;

    /**
     * @var User|null
     */
    private $targetUser = null;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->team         = $options['team'];
        $this->loggedInUser = $options['loggedInUser'];
        $this->targetUser   = $options['targetUser'];

        $builder
            ->add('firstname', 'text', ['required' => true])
            ->add('lastname', 'text', ['required' => true])
            ->add('email', 'text', [
                'required' => true
            ])
            ->add('jobTitle', 'text', ['required' => !empty($this->targetUser)])
            ->add('phoneMain', 'text', ['required' => !empty($this->targetUser)]);

        if (!$this->loggedInUser->isTeamMember() && $this->team->canAddAdmin($this->targetUser)) {
            $builder->add('roleName', 'choice', [
                'choices'  => [User::ROLE_PA_ADMIN => 'Yes', User::ROLE_PA_TEAM_MEMBER => 'No'],
                'expanded' => true,
                'required' => true
            ]);
        }

        $builder->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'pa-team',
            'data_class'         => User::class,
            'targetUser'         => null
        ])
        ->setRequired(['team','loggedInUser','validation_groups'])
        ->setAllowedTypes('team'        , Team::class)
        ->setAllowedTypes('loggedInUser', User::class)
        ->setAllowedTypes('validation_groups'  , 'array')
        ;
    }

    public function getName()
    {
        return 'team_member_account';
    }
}
