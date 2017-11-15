<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Odr\Expense;
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
                'invalid_message' => 'odr.expenses.singleExpense.notNumeric',
            ])
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
            'validation_groups' => ['odr-deputy-expense'],
            'translation_domain' => 'odr-deputy-expenses',
        ]);
    }

    public function getName()
    {
        return 'expenses_single';
    }
}
