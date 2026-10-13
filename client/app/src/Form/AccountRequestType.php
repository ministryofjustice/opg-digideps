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

class AccountRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('deputyFirstName', TextType::class, [
                'label' => 'opg.account.request.deputyFirstName.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.account.request.deputyFirstName.notBlank']),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: 'opg.account.request.deputyFirstName.tooShort',
                        maxMessage: 'opg.account.request.deputyFirstName.tooLong'
                    )
                ]
            ])
            ->add('deputyLastName', TextType::class, [
                'label' => 'opg.account.request.deputyLastName.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.account.request.deputyLastName.notBlank']),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: 'opg.account.request.deputyLastName.tooShort',
                        maxMessage: 'opg.account.request.deputyLastName.tooLong'
                    )
                ]
            ])
            ->add('deputyEmail', RepeatedType::class, [
                'type' => EmailType::class,
                'required' => true,
                'first_options'  => ['label' => 'opg.account.request.deputyEmail.first.label'],
                'second_options' => ['label' => 'opg.account.request.deputyEmail.second.label'],
                'invalid_message' => 'opg.account.request.deputyEmail.doesNotMatch',
                'constraints' => [
                    new NotBlank(message: 'opg.account.request.deputyEmail.notBlank'),
                    new Length(max: 60, maxMessage: 'opg.account.request.deputyEmail.tooLong'),
                    new Email(message: 'opg.account.request.deputyEmail.invalidFormat')
                ]
            ])
            ->add('deputyPostCode', TextType::class, [
                'label' => 'opg.account.request.deputyPostCode.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.account.request.deputyPostCode.notBlank']),
                    new Length(max: 60, maxMessage: 'opg.account.request.deputyPostCode.tooLong')
                ]
            ])
            ->add('clientLastName', TextType::class, [
                'label' => 'opg.account.request.clientLastName.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'opg.account.request.clientLastName.notBlank']),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: 'opg.account.request.clientLastName.tooShort',
                        maxMessage: 'opg.account.request.clientLastName.tooLong'
                    )
                ]
            ])
            ->add('caseNumber', TextType::class, [
                'label' => 'opg.account.request.caseNumber.label',
                'required' => true,
                'help' => 'opg.account.request.caseNumber.help',
                'constraints' => [
                    new NotBlank(['message' => 'opg.account.request.caseNumber.wrongLength']),
                    new AtLeastOneOf([
                        new Length(8), new Length(10)
                    ], message: 'opg.account.request.caseNumber.wrongLength', includeInternalMessages: false)
                ]
            ])

            // TODO REMOVE WHEN WE HAVE THE REAL ACCOUNT SERVICE
            ->add('mockResponseToReceive', ChoiceType::class, [
                'choices' => [
                    'Valid details entered' => 'verified',
                    'Details not matched' => 'nomatch',
                    'Email is already registered' => 'alreadyregistered',
                    'Notify failed to send "set password" email' => 'setpasswordemailfail',
                    'Error contacting API' => 'networkerror'
                ]
            ])

            ->add('signUp', SubmitType::class, [
                'label' => 'opg.account.request.signUp.label'
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
