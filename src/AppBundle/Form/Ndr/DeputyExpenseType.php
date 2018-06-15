<?php

namespace AppBundle\Form\Ndr;

use AppBundle\Entity\Ndr\Expense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeputyExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('explanation', FormTypes\TextType::class, [
                'required' => true,
            ])
            ->add('amount', FormTypes\NumberType::class, [
                'precision' => 2,
                'grouping' => true,
                //'error_bubbling' => true,  // keep (and show) the error (Default behaviour). if true, error is los
                'invalid_message' => 'ndr.expenses.singleExpense.notNumeric',
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
            'validation_groups' => ['ndr-deputy-expense'],
            'translation_domain' => 'ndr-deputy-expenses',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'expenses_single';
    }
}
