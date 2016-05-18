<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResetPasswordType extends AbstractType
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('password', 'repeated', [
                    'type' => 'password',
                    'invalid_message' => $this->options['passwordMismatchMessage'],
                ])
                ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'password-reset',
             'validation_groups' => ['user_set_password'],
        ]);
    }

    public function getName()
    {
        return 'reset_password';
    }
}
