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
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
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
                    new NotBlank(['message' => 'opg.register.deputyFirstName.notBlank']),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: 'opg.register.deputyFirstName.tooShort',
                        maxMessage: 'opg.register.deputyFirstName.tooLong'
                    )
                ]
            ])
            ->add('deputyLastName', TextType::class, [
                'label' => 'opg.register.deputyLastName.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.register.deputyLastName.notBlank']),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: 'opg.register.deputyLastName.tooShort',
                        maxMessage: 'opg.register.deputyLastName.tooLong'
                    )
                ]
            ])
            ->add('deputyEmail', RepeatedType::class, [
                'type' => EmailType::class,
                'required' => true,
                'first_options'  => ['label' => 'opg.register.deputyEmail.first.label'],
                'second_options' => ['label' => 'opg.register.deputyEmail.second.label'],
                'invalid_message' => 'opg.register.deputyEmail.doesNotMatch',
                'constraints' => [
                    new NotBlank(message: 'opg.register.deputyEmail.notBlank'),
                    new Length(max: 60, maxMessage: 'opg.register.deputyEmail.tooLong'),
                    new Email(message: 'opg.register.deputyEmail.invalidFormat')
                ]
            ])
            ->add('deputyPostCode', TextType::class, [
                'label' => 'opg.register.deputyPostCode.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.register.deputyPostCode.notBlank']),
                    new Length(max: 60, maxMessage: 'opg.register.deputyPostCode.tooLong')
                ]
            ])
            ->add('clientLastName', TextType::class, [
                'label' => 'opg.register.clientLastName.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.register.clientLastName.notBlank']),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: 'opg.register.clientLastName.tooShort',
                        maxMessage: 'opg.register.clientLastName.tooLong'
                    )
                ]
            ])
            ->add('caseNumber', TextType::class, [
                'label' => 'opg.register.caseNumber.label',
                'required' => true,
                'help' => 'opg.register.caseNumber.help',
                'constraints' => [
                    new AtLeastOneOf([
                        new Length(8), new Length(10)
                    ], message: 'opg.register.caseNumber.wrongLength', includeInternalMessages: false)
                ]
            ])

            // TO BE REMOVED WHEN WE HAVE THE REAL ACCOUNT SERVICE
            ->add('mockDetails', ChoiceType::class, [
                'choices' => [
                    'Valid details entered' => 'verified',
                    'Details not matched' => 'nomatch',
                    'Email is already registered' => 'alreadyregistered',
                    'Notify failed to send "set password" email' => 'setpasswordemailfail',
                    'Error contacting API' => 'networkerror'
                ]
            ])

            ->add('signUp', SubmitType::class, [
                'label' => 'opg.register.signUp.label'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // label and help translations are automatically done using this translation file;
        // note that validation messages are translated using the validators.en.yml file instead
        $resolver->setDefaults([
            'translation_domain' => 'twig-components'
        ]);
    }
}
