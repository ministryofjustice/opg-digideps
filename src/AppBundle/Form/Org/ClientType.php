<?php

namespace AppBundle\Form\Org;

use Symfony\Component\Form\AbstractType;
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
                ->add('dateOfBirth', 'date', ['widget' => 'text',
                        'input' => 'datetime',
                        'format' => 'dd-MM-yyyy',
                        'invalid_message' => 'Enter a valid date',
                ])
                ->add('email', 'email')
                ->add('address', 'text')
                ->add('address2', 'text')
                ->add('postcode', 'text')

                ->add('county', 'text')
//                ->add('country', 'country', [
//                      'preferred_choices' => ['GB'],
//                      'empty_value' => 'country.defaultOption',
//                ])
                ->add('phone', 'text')
                ->add('id', 'hidden')
                ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'org-client-edit',
            'validation_groups' => 'org-client',
        ]);
    }

    public function getName()
    {
        return 'org_client_edit';
    }
}
