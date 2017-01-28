<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\Report\MoneyTransactionShort;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoneyShortTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', 'text', [
                'required' => true,
            ])
            ->add('amount', 'number', [
                'precision' => 2,
                'grouping'  => true,
//                'invalid_message' => 'expenses.singleExpense.notNumeric',
            ])
            ->add('date', 'date', ['widget'          => 'text',
                                            'input'           => 'datetime',
                                            'format'          => 'dd-MM-yyyy',
                                            'invalid_message' => 'Enter a valid date',
            ])
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MoneyTransactionShort::class,
            'validation_groups'  => ['money-transaction-short'],
            'translation_domain' => 'report-money-short',
        ]);
    }

    public function getName()
    {
        return 'money_short_transaction';
    }
}
