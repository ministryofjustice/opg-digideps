<?php

namespace AppBundle\Form\Odr\Expense;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

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
                    'type' => new ExpenseSingleType()
                ])
                ->add('planningToClaimExpenses', 'choice', array(
                        'choices' => ['yes' => 'Yes', 'no' => 'No'],
                        'expanded' => true,
                      ))
                ->add('planningToClaimExpensesDetails', 'textarea')
                ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-expenses',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData();
                $validationGroups = ['odr-expenses'];

                if ($data->getPlanningToClaimExpenses() == 'yes') {
                    $validationGroups[] = 'odr-expenses-planning-claim-yes';
                }

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'odr_expenses';
    }
}
