<?php

namespace OPG\Digideps\Frontend\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'First name',
                'constraints' => [
                    new NotBlank(['message' => 'firstname must not be blank'])
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
}
