<?php

namespace AppBundle\Form\Org;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

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
        $builder
            ->add('confirmArchive', 'checkbox', [ 'constraints' => new NotBlank(['message' => '....']), 'mapped' => false])
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
        return 'org_client_archive';
    }
}
