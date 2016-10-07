<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('exist', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [new NotBlank(['message' => 'contact.noContactsChoice.notBlank', 'groups' => ['exist']])],
            ))
            ->add('reasonForNoContacts', 'textarea')
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-contacts',
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = ['exist'];
                if ($form['exist']->getData() === 'no') {
                    $validationGroups = ['reasonForNoContacts'];
                }

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'contact_exist';
    }
}
