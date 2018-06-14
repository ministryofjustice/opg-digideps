<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoDeputyVerificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname', FormTypes\TextType::class)
            ->add('lastname', FormTypes\TextType::class)
            ->add('address1', FormTypes\TextType::class)
            ->add('address2', FormTypes\TextType::class)
            ->add('address3', FormTypes\TextType::class)
            ->add('addressPostcode', FormTypes\TextType::class)
            ->add('addressCountry', FormTypes\CountryType::class, [
                'preferred_choices' => ['', 'GB'],
                'empty_value' => 'Please select ...',
            ])
            ->add('phoneMain', FormTypes\TextType::class)
            ->add('phoneAlternative', FormTypes\TextType::class)
            ->add('email', FormTypes\TextType::class)
            ->add('clientLastname', FormTypes\TextType::class, ['mapped' => false])
            ->add('clientCaseNumber', FormTypes\TextType::class, ['mapped' => false])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'co-deputy',
            'validation_groups' => ['verify-codeputy'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'co_deputy';
    }
}
