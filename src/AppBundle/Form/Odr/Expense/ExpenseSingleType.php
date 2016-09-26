<?php

namespace AppBundle\Form\Odr\Expense;

use AppBundle\Entity\Odr\Expense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExpenseSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('explanation', 'text', [
                     'required' => true,
                 ])
                ->add('amount', 'number', [
                    'precision' => 2,
                    'grouping' => true,
                    //'error_bubbling' => true,  // keep (and show) the error (Default behaviour). if true, error is los
                    'invalid_message' => 'odr.expenses.singleExpense.notNumeric',
                ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
             'data_class' => Expense::class,
             'validation_groups' => ['odr-expenses'],
             'translation_domain' => 'odr-expenses',
        ]);
    }

    public function getName()
    {
        return 'odr_expenses_single';
    }
}
