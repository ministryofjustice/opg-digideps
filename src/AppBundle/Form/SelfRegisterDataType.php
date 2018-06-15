<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelfRegisterDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', FormTypes\TextType::class)
            ->add('lastname', FormTypes\TextType::class)
            ->add('postcode', FormTypes\TextType::class)
            ->add('email', FormTypes\RepeatedType::class, [
                'type' => 'email',
                'invalid_message' => 'user.email.doesNotMatch',
            ])
            ->add('clientFirstname', FormTypes\TextType::class)
            ->add('clientLastname', FormTypes\TextType::class)
            ->add('caseNumber', FormTypes\TextType::class)
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'register',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'self_registration';
    }
}
