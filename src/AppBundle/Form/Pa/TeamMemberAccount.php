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
     * @param array $options keys: array roleChoices, array roleNameEmptyValue
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'text')
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('email', 'text')
            ->add('grantAdminAccess', 'checkbox')
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'pa-team',
            'validation_groups' => ['pa_add_team_member_account'],
        ]);
    }

    public function getName()
    {
        return 'team_member_account';
    }
}
