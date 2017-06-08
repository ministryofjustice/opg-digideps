<?php

namespace AppBundle\Form\Pa;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
        $builder->add('firstname', 'text')
                 ->add('lastname', 'text')
                ->add('dateOfBirth', 'date', ['widget' => 'text',
                        'input' => 'datetime',
                        'format' => 'dd-MM-yyyy',
                        'invalid_message' => 'Enter a valid date',
                ])
                ->add('email', 'email', [
                    'constraints' => [
                        new Email(['message' => 'SETME']),
                    ],
                ])
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

        // strip tags to prevent XSS as the name is often displayed around mixed with translation with the twig "raw" filter
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data['firstname'] = strip_tags($data['firstname']);
            $data['lastname'] = strip_tags($data['lastname']);
            $event->setData($data);
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'pa-client-edit',
        ]);
    }

    public function getName()
    {
        return 'pa_client_edit';
    }
}
