<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class SetPasswordType extends AbstractType
{
    const VALIDATION_GROUP = 'user_set_password';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'password',
            FormTypes\RepeatedType::class,
            [
                'type' => FormTypes\PasswordType::class,
                'invalid_message' => $options['passwordMismatchMessage'],
            ]
        );
        if (!empty($options['showTermsAndConditions'])) {
            $builder->add('showTermsAndConditions', FormTypes\CheckboxType::class, [
                'mapped' => false,
                'constraints' => [new Constraints\NotBlank(['message' => 'user.agreeTermsUse.notBlank', 'groups' => [self::VALIDATION_GROUP]])],
            ]);
        }
        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'user-activate',
              'validation_groups' => [self::VALIDATION_GROUP],
              'showTermsAndConditions' => false,
        ])
        ->setRequired(['passwordMismatchMessage']);
    }

    public function getBlockPrefix()
    {
        return 'set_password';
    }
}
