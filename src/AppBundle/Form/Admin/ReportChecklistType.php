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
    const SAVE_ACTION = 'submitAndDownfload';
    const SUBMIT_AND_DOWNLOAD_ACTION = 'submitAndDownload';

    private $report;
    private $hideMoneySections;
    private $nonPFATypes = array("104", "104-6", "104-5");

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $finalDecisionTransPrefix = 'checklistPage.form.finalDecision.options.';
        $this->report = $options['report'];
        $this->hideMoneySections = in_array($this->report->type, $nonPFATypes);
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
            ])

            // Client Assets and Debt
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
            ])

            // Next reporting period
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


        // If PA report, add PA deputy Expenses question
        if ($this->report->isPaReport()) {
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

        // Health and Lifestyle question
        $builder->add('satisfiedWithHealthAndLifestyle', FormTypes\ChoiceType::class, [
            'choices' => ['Yes' => 'yes', 'No' => 'no'],
            'expanded' => true
        ]);

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

                    if ($this->report->isLayReport()) {
                        $ret[] = 'submit-lay-checklist';
                    } elseif ($this->report->isPaReport()) {
                        $ret[] = 'submit-pa-checklist';
                    } elseif ($this->report->isProfReport()) {
                        $ret[] = 'submit-prof-checklist';
                    }
                }
                return $ret;
            },
        ])
        ->setRequired(['report'])
        ;
    }
}
