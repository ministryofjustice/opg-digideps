<?php

namespace OPG\Digideps\Frontend\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'opg.register.firstName.label',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => new TranslatableMessage('opg.register.firstName.errorMessage', [], 'twig-components')
                    ])
                ]
            ])
            ->add('lastname', TextType::class)
            ->add('postcode', TextType::class)
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'user.email.doesNotMatch',
            ])
            ->add('clientLastname', TextType::class)
            ->add('caseNumber', TextType::class)
            ->add('mockDetails', ChoiceType::class, [
                'choices' => [
                    'verified' => 'Valid details entered',
                    'invalid' => 'Details not provided on form',
                    'nomatch' => 'Details not matched',
                    'alreadyregistered' => 'Email is already registered'
                ]
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'twig-components',
        ]);
    }
}
