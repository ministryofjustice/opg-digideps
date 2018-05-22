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
            $builder
            ->add('id', 'hidden')
            ->add('reportingPeriodAccurate', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer Yes or No', 'groups' => ['report-checklist']])],
            ])
            ->add('contactDetailsUptoDate', 'checkbox', [
                'label' => 'Are all contact details correct and up to date? (and updated on CASREC in full, where applicable)',
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer Yes or No', 'groups' => ['report-checklist']])],
            ])
            ->add('deputyFullNameAccurateinCasrec', 'checkbox', [
                'label' => 'Are the deputies full name(s) on CASREC accurate? ',
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer Yes or No', 'groups' => ['report-checklist']])],
            ])

            // Decisions
            ->add('decisionsSatisfactory', 'choice', [
                'label' => 'Decisions: Have satisfactory responses been provided by the deputy?',
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer Yes or No', 'groups' => ['report-checklist']])],
            ])

            // People Consulted
            ->add('consultationsSatisfactory', 'choice', [
                'label' => 'People consulted: Have satisfactory responses been provided by the deputy?',
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer Yes or No', 'groups' => ['report-checklist']])],
            ])

            // Visits and Care
            ->add('careArrangements', 'choice', [
                'label' => 'Visits and care consultations: Have satisfactory responses been provided by the deputy?',
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer Yes or No', 'groups' => ['report-checklist']])],
            ])

            // Client Assets and Debt
            ->add('assetsDeclaredAndManaged', 'choice', [
                'label' => 'Are you satisfied the deputy has accurately declared all of the client’s assets AND are they being appropriately managed?',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])
            ->add('debtsManaged', 'choice', [
                'label' => 'If the client has any debt, are you satisfied that the debt is appropriate and/or is it being managed by the deputy in an appropriate manner?',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])


            // Money In
            ->add('openClosingBalancesMatch', 'choice', [
                'label' => 'Account balance: Does the opening balance(s) match last year’s closing balance?',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])
            ->add('accountsBalance', 'choice', [
                'label' => 'Account balance: Does the account summary balance (and match bank statements where provided)  (up to £250 leeway for lay deputies)?',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])
            ->add('moneyMovementsAcceptable', 'choice', [
                'label' => 'Are you satisfied that the ‘money in’ and ‘money out’ entries (including transfers), along with any comments, are: acceptable, appear reasonable and inline with the terms of the court order?',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])

            // Bonds
            ->add('bondAdequate', 'choice', [
                'label' => 'Bonds:  Is the bond adequate protection for the client’s assets? If ‘NO’, refer to the Security Bonds Discrepancy Resolution Job Card.',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])
            ->add('bondOrderMatchCasrec', 'choice', [
                'label' => 'Bonds: And/or does the bond amount on CASREC match the order? If ‘NO’, refer to the Security Bonds Discrepancy Resolution Job Card.',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])

            // Next reporting period

            ->add('futureSignificantFinancialDecisions', 'choice', [
                'label' => 'Are there significant financial decision(s) in the next reporting period?',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])
            ->add('hasDeputyRaisedConcerns', 'choice', [
                'label' => 'Does the deputy raise any concerns about their deputyship?',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])

            // Deputy declaration
            ->add('caseWorkerSatisified', 'choice', [
                'label' => 'Are you satisfied with the deputy’s declaration?',
                'choices' => ['yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable'],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])

            // Lodging summary
            ->add('lodgingSummary', 'textarea', [
                'label' => 'Lodging Summary (This section must be completed)'
            ])

            // Final decision
            ->add('finalDecision', 'choice', [
                'label' => 'Final Decision?',
                'choices' => [
                    'for-review' => 'I am referring the case for a staff review or gifting review',
                    'incomplete' => 'The report is incomplete',
                    'further-casework-required' => 'I have lodged and acknowledged the report but issues require further case work',
                    'satisfied' => 'I am satisfied with the deputy’s report, no further action is required and I have sent an acknowledgment to the deputy(s)'
                ],
                'expanded' => true,
                'constraints' => [new Constraints\NotBlank(['message' => 'Please answer this question', 'groups' => ['report-checklist']])],
            ])
            // Further information received
            ->add('furtherInformationReceived', 'textarea', [
                'label' => 'Further Information received: (Document all your decisions made and why).'
            ])
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
