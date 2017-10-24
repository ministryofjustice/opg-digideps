<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientType extends AbstractType
{
    private $client_validated = false;

    /**
     * ClientType constructor.
     * Setting client_validated = true renders the firstname, lastname and case_number as read only fields
     * 
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (isset($options['client_validated'])) {
            $this->setClientValidated((bool) $options['client_validated']);
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->isClientValidated()) {
            $builder->add('firstname', 'text', ['attr'=> ['readonly' => 'readonly']])
                ->add('lastname', 'text',  ['attr'=> ['readonly' => 'readonly']])
                ->add('caseNumber', 'text',  ['attr'=> ['readonly' => 'readonly']]);
        } else {
            $builder->add('firstname', 'text')
                ->add('lastname', 'text')
                ->add('caseNumber', 'text');
        }
        $builder->add('courtDate', 'date', [
            'widget' => 'text',
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
            'translation_domain' => 'registration',
            'validation_groups' => 'lay-deputy-client',
        ]);
    }

    public function getName()
    {
        return 'client';
    }

    /**
     * @return bool
     */
    public function isClientValidated()
    {
        return $this->client_validated;
    }

    /**
     * @param bool $client_validated
     */
    public function setClientValidated($client_validated)
    {
        $this->client_validated = $client_validated;
        return $this;
    }
}
