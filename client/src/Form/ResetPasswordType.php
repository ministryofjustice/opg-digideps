<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends AbstractType
{
    const VALIDATION_GROUP = 'user_set_password';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('password', FormTypes\RepeatedType::class, [
                    'type' => FormTypes\PasswordType::class,
                    'invalid_message' => $options['passwordMismatchMessage'],
                ])
                ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'password-reset',
            'validation_groups' => [self::VALIDATION_GROUP],
        ])
        ->setRequired(['passwordMismatchMessage']);
    }

    public function getBlockPrefix()
    {
        return 'reset_password';
    }
}
