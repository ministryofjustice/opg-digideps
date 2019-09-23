<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeputyExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('explanation', FormTypes\TextareaType::class, [
                'required' => true,
            ])
            ->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                //'error_bubbling' => true,  // keep (and show) the error (Default behaviour). if true, error is los
                'invalid_message' => 'expenses.singleExpense.notNumeric',
            ]);

        $reportType = $options['report']->getType();

        if (!empty($options['report']->getBankAccountOptions()) && $options['report']->canLinkToBankAccounts())
        {
            $builder->add('bankAccountId', FormTypes\ChoiceType::class, [
                'choices' => $options['report']->getBankAccountOptions(),
                'placeholder' => 'Please select'
            ]);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
            'validation_groups' => ['deputy-expense'],
            'translation_domain' => 'report-deputy-expenses',
        ])->setRequired(['user', 'report']);
    }

    public function getBlockPrefix()
    {
        return 'expenses_single';
    }
}
