<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientVerifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('lastname'  , 'text')
                ->add('caseNumber', 'text')
                ->add('save'      , 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'registration',
            'validation_groups' => 'lay-deputy-client',
        ]);
    }

    public function getName()
    {
        return 'clientVerify';
    }
}
