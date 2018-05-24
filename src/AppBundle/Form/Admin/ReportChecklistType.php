<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class ReportChecklistType extends AbstractType
{
    const SAVE_ACTION = 'submitAndDownload';
    const SUBMIT_AND_DOWNLOAD_ACTION = 'submitAndDownload';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $finalDecisionTransPrefix = 'checklistPage.form.finalDecision.options.';
            $builder
            ->add('id', 'hidden')
            ->add('reportingPeriodAccurate', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])
            ->add('contactDetailsUptoDate', 'checkbox', [])
            ->add('deputyFullNameAccurateinCasrec', 'checkbox', [])

            // Decisions
            ->add('decisionsSatisfactory', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])

            // People Consulted
            ->add('consultationsSatisfactory', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])

            // Visits and Care
            ->add('careArrangements', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])

            // Client Assets and Debt
            ->add('assetsDeclaredAndManaged', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('debtsManaged', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Money In
            ->add('openClosingBalancesMatch', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('accountsBalance', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('moneyMovementsAcceptable', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Bonds
            ->add('bondAdequate', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('bondOrderMatchCasrec', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Next reporting period
            ->add('futureSignificantFinancialDecisions', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])
            ->add('hasDeputyRaisedConcerns', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Deputy declaration
            ->add('caseWorkerSatisified', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true
            ])

            // Lodging summary
            ->add('lodgingSummary', 'textarea', [])

            // Final decision
            ->add('finalDecision', 'choice', [
                'choices' => [
                    'for-review' => $finalDecisionTransPrefix . 'forReview',
                    'incomplete' => $finalDecisionTransPrefix . 'incomplete',
                    'further-casework-required' => $finalDecisionTransPrefix . 'furtherCaseworkRequired',
                    'satisfied' => $finalDecisionTransPrefix . 'satisfied'
                ],
                'expanded' => true
            ])
            // Further information received
            ->add('furtherInformationReceived', 'textarea', [])
        ->add('saveFurtherInformation', 'submit')
        ->add('save', 'submit')
        ->add('submitAndDownload', 'submit');
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
