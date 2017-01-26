<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeputyExpenseExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paidForAnything', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
//                'constraints' => [new NotBlank(['message' => 'expenses.paidForAnything.notBlank', 'groups' => ['exist']])],
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'deputy-expenses',
            'validation_groups' => ['expenses-paid-anything'],
        ]);
    }

    public function getName()
    {
        return 'expense_exist';
    }
}
