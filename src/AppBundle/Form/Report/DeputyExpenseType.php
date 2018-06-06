<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeputyExpenseType extends AbstractType
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
                'invalid_message' => 'expenses.singleExpense.notNumeric',
            ]);

        $reportType = $options['report']->getType();

        if (!empty($options['report']->getBankAccountOptions()) && (in_array($reportType, ['102', '102-4']))) {
            $builder->add('bankAccountId', 'choice', [
                'choices' => $options['report']->getBankAccountOptions(),
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
        ])->setRequired(['user', 'report']);
    }

    public function getName()
    {
        return 'expenses_single';
    }
}
