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
     * @param array $options keys: array roleChoices, array roleIdEmptyValue
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roleIdOptions = [
            'choices' => $this->options['roleChoices'],
            'empty_value' => $this->options['roleIdEmptyValue'],
        ];

        if (!empty($this->options['roleIdSetTo'])) {
            $roleIdOptions['data'] = $this->options['roleIdSetTo'];
            $roleIdOptions['disabled'] = 'disabled';
        }

        $builder->add('email', 'text')
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('roleId', 'choice', $roleIdOptions)
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
