<?php

namespace AppBundle\Form\Pa;

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
class ClientArchiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('confirmArchive',
        'choice',
        [
            'choices'  => ['yes' => 'Yes', 'no'  => 'No'],
            'expanded' => true,
            'mapped'   => false
        ])
        ->add('id', 'hidden')
        ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'pa-client-archive',
            'validation_groups' => 'pa-client',
        ]);
    }

    public function getName()
    {
        return 'pa_client_archive';
    }
}
