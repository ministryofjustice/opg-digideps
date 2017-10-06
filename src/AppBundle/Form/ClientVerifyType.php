<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientVerifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('lastname'  , 'text')
                ->add('caseNumber', 'text')
                ->add('save'      , 'submit');
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
        return 'clientVerify';
    }
}
