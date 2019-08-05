<?php

namespace AppBundle\Form\Org;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

/**
 * PA - edit client
 *
 * Similar to edit client for Lay, but too many differences therefore easier to replicate then inherit
 * and share only a few fields
 */
class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', FormTypes\TextType::class)
            ->add('lastname', FormTypes\TextType::class)
            ->add('dateOfBirth', FormTypes\DateType::class, ['widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'dd-MM-yyyy',
                    'invalid_message' => 'Enter a valid date',
            ])
            ->add('email', FormTypes\EmailType::class)
            ->add('address', FormTypes\TextType::class)
            ->add('address2', FormTypes\TextType::class)
            ->add('postcode', FormTypes\TextType::class)

            ->add('county', FormTypes\TextType::class)
            ->add('phone', FormTypes\TextType::class)
            ->add('id', FormTypes\HiddenType::class)
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'org-client-edit',
            'validation_groups' => 'pa-client',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'org_client_edit';
    }
}
