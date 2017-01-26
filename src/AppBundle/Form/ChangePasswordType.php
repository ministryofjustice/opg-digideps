<?php

namespace AppBundle\Form;

use AppBundle\Validator\Constraints\DUserPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordType extends AbstractType
{
    const VALIDATION_GROUP = 'change_password';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', 'password', [
                    'mapped' => false,
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Please enter your correct current password', 'groups' => [self::VALIDATION_GROUP]]),
                        new DUserPassword(['message' => 'Please enter your correct current password', 'groups' => [self::VALIDATION_GROUP]]),
                    ],
                ])
                ->add('plain_password', 'repeated', [
                    'mapped' => false,
                    'type' => 'password',
                    'invalid_message' => 'Password does not match',
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Please enter your new password', 'groups' => [self::VALIDATION_GROUP]]),
                        new Assert\Length(['min' => 8, 'max' => 50, 'minMessage' => 'user.password.minLength', 'maxMessage' => 'user.password.maxLength', 'groups' => [self::VALIDATION_GROUP]]),
                        new Assert\Regex(['pattern' => '/[a-z]/', 'message' => 'user.password.noLowerCaseChars', 'groups' => self::VALIDATION_GROUP]),
                        new Assert\Regex(['pattern' => '/[A-Z]/', 'message' => 'user.password.noUpperCaseChars', 'groups' => self::VALIDATION_GROUP]),
                        new Assert\Regex(['pattern' => '/[0-9]/', 'message' => 'user.password.noNumber', 'groups' => self::VALIDATION_GROUP]),
                    ],
                ])
                ->add('id', 'hidden')
                ->add('save', 'submit');
    }

    public function getParent()
    {
        return 'form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'user-details',
            'validation_groups' => [self::VALIDATION_GROUP],
        ]);
    }

    public function getName()
    {
        return 'change_password';
    }
}
