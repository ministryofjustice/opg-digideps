<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname', 'text')
                 ->add('lastname', 'text')
                 ->add('caseNumber', 'text')
                 ->add('courtDate', 'date', ['widget' => 'text',
                                              'input' => 'datetime',
                                              'format' => 'yyyy-MM-dd',
                                              'invalid_message' => 'client.courtDate.message',
                                            ])
                ->add('address', 'text')
                ->add('address2', 'text')
                ->add('postcode', 'text')
                ->add('county', 'text')
                ->add('country', 'country', [
                      'preferred_choices' => ['GB'],
                      'empty_value' => 'country.defaultOption',
                ])
                ->add('phone', 'text')
                ->add('users', 'collection', ['type' => 'integer',
                                               'options' => ['required' => false,
                                                              'attr' => ['style' => 'display: none'],
                                                              'label' => false, ], 'label' => false, ])
                ->add('reports', 'collection', ['type' => 'integer',
                                               'options' => ['required' => false,
                                                              'attr' => ['style' => 'display: none'],
                                                              'label' => false, ], 'label' => false, ])
                ->add('id', 'hidden')
                ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'registration',
        ]);
    }

    public function getName()
    {
        return 'client';
    }
}
