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
            ->add('deputyEmail', RepeatedType::class, [
                'type' => EmailType::class,
                'required' => true,
                'first_options'  => ['label' => 'opg.register.deputyEmail.first.label'],
                'second_options' => ['label' => 'opg.register.deputyEmail.second.label'],
                'invalid_message' => 'opg.register.deputyEmail.doesNotMatch',
                'constraints' => [
                    new NotBlank(['message' => 'opg.register.deputyEmail.notBlank'])
                ]
            ])
            ->add('deputyPostCode', TextType::class, [
                'label' => 'opg.register.deputyPostCode.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.register.deputyPostCode.notBlank'])
                ]
            ])
            ->add('clientLastName', TextType::class, [
                'label' => 'opg.register.clientLastName.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.register.clientLastName.notBlank'])
                ]
            ])
            ->add('caseNumber', TextType::class, [
                'label' => 'opg.register.caseNumber.label',
                'required' => true,
                'help' => 'opg.register.caseNumber.help',
                'constraints' => [
                    new NotBlank(['message' => 'opg.register.caseNumber.notBlank'])
                ]
            ])

            // TO BE REMOVED WHEN WE HAVE THE REAL REGISTRATION SERVICE IN THE API
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
