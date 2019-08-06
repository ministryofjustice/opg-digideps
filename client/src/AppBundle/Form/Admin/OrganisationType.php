<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class OrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('name', FormTypes\TextType::class)
            ->add('emailIdentifierType', FormTypes\ChoiceType::class, [
                'choices' => [
                    'They own an email domain' => 'domain',
                    'They have an email address on a shared domain' => 'address',
                ],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'organisation.emailIdentifier.notBlank'])
                ],
            ])
            ->add('emailAddress', FormTypes\TextType::class)
            ->add('emailDomain', FormTypes\TextType::class)
            ->add('isActivated', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => true, 'No' => false],
                'expanded' => true,
                'data' => false,
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-organisations',
        ]);
    }
}
