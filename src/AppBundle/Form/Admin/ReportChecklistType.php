<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportChecklistType extends AbstractType
{
    const SAVE_ACTION = 'submitAndDownload';
    const SUBMIT_AND_DOWNLOAD_ACTION = 'submitAndDownload';

    /**
     * @var Report
     */
    private $report;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $finalDecisionTransPrefix = 'checklistPage.form.finalDecision.options.';
        $this->report = $options['report'];


        // HW & PFA
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('reportingPeriodAccurate', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ])
            ->add('contactDetailsUptoDate', FormTypes\CheckboxType::class, [])
            ->add('deputyFullNameAccurateInCasrec', FormTypes\CheckboxType::class, [])

            // Decisions
            ->add('decisionsSatisfactory', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ])

            // People Consulted
            ->add('consultationsSatisfactory', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ])

            // Visits and Care
            ->add('careArrangements', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ]);

        // PFA
        if($this->report->hasSection('bankAccounts')) {
            // Client Assets and Debt
            $builder
                ->add('assetsDeclaredAndManaged', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ])
                ->add('debtsManaged', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ])

                // Money In
                ->add('openClosingBalancesMatch', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ])
                ->add('accountsBalance', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ])
                ->add('moneyMovementsAcceptable', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ]);

            // If PA report, add PA deputy Expenses question
            if ($this->report->hasSection('paDeputyExpenses')) {
                $builder->add('satisfiedWithPaExpenses', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ]);
            } else {
                // Otherwise add Bonds question
                $builder->add('bondAdequate', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ]);
                $builder->add('bondOrderMatchCasrec', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ]);
            }
        }

        // HW
        if($this->report->hasSection('lifestyle')) {
            // Health and Lifestyle question
            $builder->add('satisfiedWithHealthAndLifestyle', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ]);
        }

        // HW and PFA

        // Next reporting period
        $builder
            ->add('futureSignificantFinancialDecisions', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                'expanded' => true
            ])
            ->add('hasDeputyRaisedConcerns', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                'expanded' => true
            ])

            // Deputy declaration
            ->add('caseWorkerSatisified', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                'expanded' => true
            ])

            // Lodging summary
            ->add('lodgingSummary', FormTypes\TextareaType::class, [])

            // Final decision
            ->add('finalDecision', FormTypes\ChoiceType::class, [
                'choices' => [
                    $finalDecisionTransPrefix . 'forReview' => 'for-review',
                    $finalDecisionTransPrefix . 'incomplete' =>  'incomplete',
                    $finalDecisionTransPrefix . 'furtherCaseworkRequired' => 'further-casework-required',
                    $finalDecisionTransPrefix . 'satisfied' => 'satisfied'
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
            'data-class' => Checklist::class,
            'name'               => 'checklist',
            'validation_groups'  => function (FormInterface $form) {
                $ret = [];
                if (self::SUBMIT_AND_DOWNLOAD_ACTION == $form->getClickedButton()->getName()) {
                     $ret[] = 'submit-common-checklist';

                    $sectionsToValidate = $this->report->getAvailableSections();

                    foreach ($sectionsToValidate as $section_id) {
                        $ret[] = 'submit-' . $section_id . '-checklist';
                    }
                }
                return $ret;
            },
        ])
        ->setRequired(['report'])
        ;
    }
}
