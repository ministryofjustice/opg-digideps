<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddUserType extends AbstractType
{
    /**
     * @var array
     */
    private $roleChoices = [];

    /**
     * @var string
     */
    private $roleNameEmptyValue;

    /**
     * @var bool
     */
    private $roleNameDisabled;

    /**
     * @param array $options keys: array roleChoices, array roleNameEmptyValue
     */
    public function __construct(array $options)
    {
        $this->roleChoices = $options['roleChoices'];
        $this->roleNameEmptyValue = $options['roleNameEmptyValue'];
        $this->roleNameDisabled = empty($options['roleNameDisabled']) ? false : true;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'text')
                 ->add('firstname', 'text')
                 ->add('lastname', 'text')
                 ->add('roleName', 'choice', [
                    'choices' => $this->roleChoices,
                    'empty_value' => $this->roleNameEmptyValue,
                    'disabled' => $this->roleNameDisabled,
                  ])
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
