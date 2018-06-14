<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelfRegisterDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('postcode', 'text')
            ->add('email', 'repeated', [
                'type' => 'email',
                'invalid_message' => 'user.email.doesNotMatch',
            ])
            ->add('clientFirstname', 'text')
            ->add('clientLastname', 'text')
            ->add('caseNumber', 'text')
            ->add('save', 'submit');
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
