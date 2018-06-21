<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportChecklistType extends AbstractType
{
    const SAVE_ACTION = 'submitAndDownload';
    const SUBMIT_AND_DOWNLOAD_ACTION = 'submitAndDownload';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $finalDecisionTransPrefix = 'checklistPage.form.finalDecision.options.';
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('reportingPeriodAccurate', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])
            ->add('reportingPeriodAcceptable', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])
            ->add('contactDetailsUptoDate', FormTypes\CheckboxType::class, [])
            ->add('deputyFullNameAccurateInCasrec', FormTypes\CheckboxType::class, [])

            // Decisions
            ->add('decisionsSatisfactory', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])

            // People Consulted
            ->add('consultationsSatisfactory', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])

            // Visits and Care
            ->add('careArrangements', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])

            // Client Assets and Debt
            ->add('assetsDeclaredAndManaged', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('debtsManaged', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Money In
            ->add('openClosingBalancesMatch', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('accountsBalance', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('moneyMovementsAcceptable', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Bonds
            ->add('bondAdequate', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('bondOrderMatchCasrec', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Next reporting period
            ->add('futureSignificantFinancialDecisions', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('hasDeputyRaisedConcerns', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Deputy declaration
            ->add('caseWorkerSatisified', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Lodging summary
            ->add('lodgingSummary', FormTypes\TextareaType::class, [])

            // Final decision
            ->add('finalDecision', FormTypes\ChoiceType::class, [
                'choices' => [
                    'for-review' => $finalDecisionTransPrefix . 'forReview',
                    'incomplete' => $finalDecisionTransPrefix . 'incomplete',
                    'further-casework-required' => $finalDecisionTransPrefix . 'furtherCaseworkRequired',
                    'satisfied' => $finalDecisionTransPrefix . 'satisfied'
                ],
                'expanded' => true
            ])
            // Further information received
            ->add('furtherInformationReceived', FormTypes\TextareaType::class, [])
        ->add('saveFurtherInformation', FormTypes\SubmitType::class)
        ->add('save', FormTypes\SubmitType::class)
        ->add('submitAndDownload', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-checklist',
            'name'               => 'checklist',
            'validation_groups'  => function (FormInterface $form) {
                $ret = [];
                if (self::SUBMIT_AND_DOWNLOAD_ACTION == $form->getClickedButton()->getName()) {
                    $ret[] = 'submit-checklist';
                }

                return $ret;
            },
        ]);
    }
}
