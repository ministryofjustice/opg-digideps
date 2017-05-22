<?php

namespace AppBundle\Form\Pa;

use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TeamMemberAccount
 **
 * @package AppBundle\Form\Pa
 */
class TeamMemberAccount extends AbstractType
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

    /**
     * @param $showRoleName
     */
    public function __construct(Team $team, User $loggedInUser, User $targetUser = null)
    {
        $this->team = $team;
        $this->loggedInUser = $loggedInUser;
        $this->targetUser = $targetUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                'required' => true,
            ]);
        }

        $builder->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'pa-team',
            'validation_groups'  => $this->determineValidationGroups(),
            'data_class'         => User::class,
        ]);
    }

    /**
     * Determine the validation groups for the form. All validate against firstname, lastname and email.
     * Edit users adds phone and job title. If role name is displayed, then also validate.
     *
     * @return array
     */
    private function determineValidationGroups()
    {
        $validationGroups = [];
        if (!empty($this->targetUser)) {
            array_push($validationGroups, 'user_details_pa');
        } else {
            array_push($validationGroups, 'pa_team_add');
        }

        if ($this->team->canAddAdmin()) {
            array_push($validationGroups, 'pa_team_role_name');
        }

        return $validationGroups;
    }

    public function getName()
    {
        return 'team_member_account';
    }
}
