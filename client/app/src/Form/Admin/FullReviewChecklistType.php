<?php

namespace App\Form\Admin;

use App\Model\FullReviewChecklist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FullReviewChecklistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $yesNoOptions = [
            'choices' => ['yes' => 'yes', 'no' => 'no'],
            'expanded' => true,
            'choice_translation_domain' => 'common',
        ];

        $yesNoNaOptions = [
            'choices' => ['yes' => 'yes', 'no' => 'no', 'notApplicable' => 'na'],
            'expanded' => true,
            'choice_translation_domain' => 'common',
        ];

        $builder
            ->add('decisionExplanation', FormTypes\TextareaType::class)
            ->add('fullBankStatementsExist', FormTypes\ChoiceType::class, $yesNoNaOptions)
            ->add('anyLodgingConcerns', FormTypes\ChoiceType::class, $yesNoNaOptions)
            ->add('spendingAcceptable', FormTypes\ChoiceType::class, $yesNoNaOptions)
            ->add('expensesReasonable', FormTypes\ChoiceType::class, $yesNoNaOptions)
            ->add('giftingReasonable', FormTypes\ChoiceType::class, $yesNoNaOptions)
            ->add('debtManageable', FormTypes\ChoiceType::class, $yesNoNaOptions)
            ->add('anySpendingConcerns', FormTypes\ChoiceType::class, $yesNoNaOptions)
            ->add('needReferral', FormTypes\ChoiceType::class, $yesNoOptions)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FullReviewChecklist::class,
            'translation_domain' => 'admin-checklist',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'full-review-checklist';
    }
}
