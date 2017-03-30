<?php

namespace AppBundle\Form\Pa;

use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TeamMemberAccount extends AbstractType
{
    private $team;

    /**
     * @param $showRoleName
     */
    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', [
                'required' => true,
            ])
            ->add('lastname', 'text', [
                'required' => true,
            ])
            ->add('email', 'text', [
                'required' => true,
            ])
            ->add('jobTitle', 'text')
            ->add('phoneMain', 'text');

        if ($this->team->canAddAdmin()) {
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
            'validation_groups'  => $this->team->canAddAdmin() ? ['pa_team_add', 'pa_team_role_name'] : ['pa_team_add'],
            'data_class'         => User::class,
        ]);
    }

    public function getName()
    {
        return 'team_member_account';
    }
}
