<?php

namespace AppBundle\Form\Odr\Expense;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExpensesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('paidForAnything', 'choice', array(
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ))
                ->add('expenses', 'collection', [
                    'type' => new ExpenseSingleType(),
                    'allow_add' => true, // let the form collection know that it will receive an unknown number of tags
                    'allow_delete' => true,
                    'delete_empty' => false,
                    'prototype' => true,
                ])
                ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-expenses',
            'validation_groups' => ['odr-expenses'],
        ]);
    }

    public function getName()
    {
        return 'odr_expenses';
    }
}
