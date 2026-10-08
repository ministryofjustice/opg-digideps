<?php

namespace OPG\Digideps\Frontend\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('deputyFirstName', TextType::class, [
                'label' => 'opg.register.deputyFirstName.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.register.deputyFirstName.notBlank'])
                ]
            ])
            ->add('deputyLastName', TextType::class, [
                'label' => 'opg.register.deputyLastName.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.register.deputyLastName.notBlank'])
                ]
            ])
            ->add('deputyPostCode', TextType::class)
            ->add('deputyEmail', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'user.email.doesNotMatch',
            ])
            ->add('clientLastName', TextType::class)
            ->add('caseNumber', TextType::class)

            // TO BE REMOVED WHEN WE HAVE THE REAL REGISTRATION SERVICE
            ->add('mockDetails', ChoiceType::class, [
                'choices' => [
                    'verified' => 'Valid details entered',
                    'invalid' => 'Details not provided on form',
                    'nomatch' => 'Details not matched',
                    'alreadyregistered' => 'Email is already registered'
                ]
            ])

            ->add('signUp', SubmitType::class, [
                'label' => 'opg.register.signUp.label'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'twig-components'
        ]);
    }
}
