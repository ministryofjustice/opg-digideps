<?php

namespace App\Form;

use App\Validator\Constraints\DUserPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeEmailType extends AbstractType
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public const VALIDATION_GROUP = 'user_change_email';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $loggedInUser = $this->tokenStorage->getToken()->getUser();

        $builder->add('current_email', FormTypes\TextType::class, [
                'mapped' => false,
                'disabled' => true,
                'data' => $loggedInUser->getEmail(),
        ])
        ->add('new_email', FormTypes\RepeatedType::class, [
            'mapped' => false,
            'type' => FormTypes\EmailType::class,
            'invalid_message' => 'user.email.new.doesntMatch',
        ])
        ->add('password', FormTypes\PasswordType::class, [
            'mapped' => true,
            'constraints' => [
                new Assert\NotBlank(['message' => 'user.password.existing.notBlank', 'groups' => [self::VALIDATION_GROUP]]),
                new DUserPassword(['message' => 'user.password.existing.notCorrect', 'groups' => [self::VALIDATION_GROUP]]),
            ],
        ])
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
        return 'change_email';
    }
}
