<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contactName', 'text', ['label' => 'Contact name'])
                ->add('relationship', 'text', ['label' => 'Relationship to the client'])
                ->add('explanation', 'textarea', ['label' => 'Reason for contact'])
                ->add('address', 'text')
                ->add('address2', 'text')
                ->add('county', 'text')
                ->add('postcode', 'text')
                ->add('id', 'hidden')
                ->add('country', 'country', [
                      'preferred_choices' => ['GB'],
                      'empty_value' => 'country.defaultOption',
                ])
                ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-contacts',
        ]);
    }

    public function getName()
    {
        return 'contact';
    }
}
