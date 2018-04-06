<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Expense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeputyExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $banks = [];
        foreach ($options['banks'] as $bank) {
            /* @var $bank BankAccount */
            $banks[$bank->getId()] = (!empty($bank->getBank()) ? $bank->getBank() . ' - '  : '') . $bank->getAccountTypeText() . ' (****' . $bank->getAccountNumber() . ')';
        }

        $builder
            ->add('explanation', 'text', [
                'required' => true,
            ])
            ->add('amount', 'number', [
                'precision' => 2,
                'grouping' => true,
                //'error_bubbling' => true,  // keep (and show) the error (Default behaviour). if true, error is los
                'invalid_message' => 'expenses.singleExpense.notNumeric',
            ]);

        if (!empty($banks)) {
            $builder->add('bankAccount', 'choice', [
                'choices' => $banks,
                'empty_value' => 'Please select'
            ]);
        }

        $builder->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
            'validation_groups' => ['deputy-expense'],
            'translation_domain' => 'report-deputy-expenses',
        ])
        ->setRequired(['banks'])
        ->setAllowedTypes('banks', 'array');
    }

    public function getName()
    {
        return 'expenses_single';
    }
}
