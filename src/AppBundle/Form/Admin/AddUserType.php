<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddUserType extends AbstractType
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
        $roleNameOptions = [
            'choices' => $this->options['roleChoices'],
            'empty_value' => $this->options['roleNameEmptyValue'],
        ];

        if (!empty($this->options['roleNameSetTo'])) {
            $roleNameOptions['data'] = $this->options['roleNameSetTo'];
            $roleNameOptions['disabled'] = 'disabled';
        }

        $builder->add('email', 'text')
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('roleName', 'choice', $roleNameOptions)
            ->add('odrEnabled', 'checkbox')
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'validation_groups' => ['admin_add_user'],
        ]);
    }

    public function getName()
    {
        return 'admin';
    }
}
