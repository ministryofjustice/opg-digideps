<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('password', 'repeated', [
                    'type' => 'password',
                    'invalid_message' => $options['passwordMismatchMessage']
                ])
                ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'password-reset',
             'validation_groups' => ['user_set_password'],
        ])
        ->setRequired(['passwordMismatchMessage']);
    }

    public function getName()
    {
        return 'reset_password';
    }
}
