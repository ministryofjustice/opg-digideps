<?php

namespace App\Form;

use App\Validator\Constraints\DUserPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordType extends AbstractType
{
    public const VALIDATION_GROUP = 'user_change_password';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', FormTypes\PasswordType::class, [
            'mapped' => false,
            'constraints' => [
                new Assert\NotBlank(['message' => 'user.password.existing.notBlank', 'groups' => [self::VALIDATION_GROUP]]),
                new DUserPassword(['message' => 'user.password.existing.notCorrect', 'groups' => [self::VALIDATION_GROUP]]),
            ],
        ])
            ->add('password', FormTypes\RepeatedType::class, [
                'mapped' => true,
                'type' => FormTypes\PasswordType::class,
                'invalid_message' => 'user.password.new.doesntMatch',
                'first_options' => ['attr' => ['autocomplete' => 'off']],
                'second_options' => ['attr' => ['autocomplete' => 'off']],
            ])
            ->add('id', FormTypes\HiddenType::class)
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'settings',
            'validation_groups' => [self::VALIDATION_GROUP],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'change_password';
    }
}
