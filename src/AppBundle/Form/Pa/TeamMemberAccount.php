<?php

namespace AppBundle\Form\Pa;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TeamMemberAccount extends AbstractType
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'text')
            ->add('firstname', 'text', [
                'required' => true,
            ])
            ->add('lastname', 'text', [
                'required' => true,
            ])
            ->add('email', 'text', [
                'required' => true,
            ])
            ->add('grantAdminAccess', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'required' => true
            ])
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'pa-team',
            'validation_groups' => ['user_details_pa'],
        ]);
    }

    public function getName()
    {
        return 'team_member_account';
    }
}
