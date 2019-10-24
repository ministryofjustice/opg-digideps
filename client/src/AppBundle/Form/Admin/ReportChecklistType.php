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
    const SAVE_ACTION = 'save';
    const SUBMIT_AND_DOWNLOAD_ACTION = 'submitAndDownload';

    /**
     * @var Report
     */
    private $report;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $finalDecisionTransPrefix = 'checklistPage.form.finalDecision.options.';
        $this->report = $options['report'];

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

        // HW & PFA
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('reportingPeriodAccurate', FormTypes\ChoiceType::class, $yesNoOptions)
            ->add('contactDetailsUptoDate', FormTypes\CheckboxType::class);

        // DDPB-2293 question not relevant for PA
        if (!$this->report->hasSection('paDeputyExpenses')) {
            $builder->add('deputyFullNameAccurateInCasrec', FormTypes\CheckboxType::class);
        }

        $builder
            ->add('decisionsSatisfactory', FormTypes\ChoiceType::class, $yesNoOptions)
            ->add('consultationsSatisfactory', FormTypes\ChoiceType::class, $yesNoOptions)
            ->add('careArrangements', FormTypes\ChoiceType::class, $yesNoOptions);

        if($this->report->hasSection('bankAccounts')) {
            // Client Assets and Debt
            $builder
                ->add('assetsDeclaredAndManaged', FormTypes\ChoiceType::class, $yesNoNaOptions)
                ->add('debtsManaged', FormTypes\ChoiceType::class, $yesNoNaOptions)
                ->add('openClosingBalancesMatch', FormTypes\ChoiceType::class, $yesNoNaOptions)
                ->add('accountsBalance', FormTypes\ChoiceType::class, $yesNoNaOptions)
                ->add('moneyMovementsAcceptable', FormTypes\ChoiceType::class, $yesNoOptions);

            // If PA report, add PA deputy Expenses question
            if ($this->report->hasSection('paDeputyExpenses')) {
                $builder
                    ->add('satisfiedWithPaExpenses', FormTypes\ChoiceType::class, $yesNoNaOptions)
                    ->add('deputyChargeAllowedByCourt', FormTypes\ChoiceType::class, $yesNoOptions);
            } else {
                // Otherwise add Bonds question
                $builder
                    ->add('bondAdequate', FormTypes\ChoiceType::class, $yesNoNaOptions)
                    ->add('bondOrderMatchCasrec', FormTypes\ChoiceType::class, $yesNoNaOptions);
            }
        }

        if ($this->report->hasSection('profDeputyCosts')) {
            $builder
                ->add('paymentsMatchCostCertificate', FormTypes\ChoiceType::class, $yesNoOptions)
                ->add('profCostsReasonableAndProportionate', FormTypes\ChoiceType::class, $yesNoOptions)
                ->add('hasDeputyOverchargedFromPreviousEstimates', FormTypes\ChoiceType::class, $yesNoNaOptions);
        }

        if ($this->report->hasSection('profDeputyCostsEstimate')) {
            $builder->add('nextBillingEstimatesSatisfactory', FormTypes\ChoiceType::class, $yesNoOptions);
        }

        if($this->report->hasSection('lifestyle')) {
            $builder->add('satisfiedWithHealthAndLifestyle', FormTypes\ChoiceType::class, $yesNoOptions);
        }

        $builder
            ->add('futureSignificantDecisions', FormTypes\ChoiceType::class, $yesNoOptions)
            ->add('hasDeputyRaisedConcerns', FormTypes\ChoiceType::class, $yesNoOptions)
            ->add('caseWorkerSatisified', FormTypes\ChoiceType::class, $yesNoOptions)
            ->add('lodgingSummary', FormTypes\TextareaType::class)
            ->add('finalDecision', FormTypes\ChoiceType::class, [
                'choices' => [
                    $finalDecisionTransPrefix . 'forReview' => 'for-review',
                    $finalDecisionTransPrefix . 'incomplete' =>  'incomplete',
                    $finalDecisionTransPrefix . 'furtherCaseworkRequired' => 'further-casework-required',
                    $finalDecisionTransPrefix . 'satisfied' => 'satisfied'
                ],
                'expanded' => true
            ])
            ->add('furtherInformationReceived', FormTypes\TextareaType::class)
            ->add('saveFurtherInformation', FormTypes\SubmitType::class)
            ->add(self::SAVE_ACTION, FormTypes\SubmitType::class)
            ->add(self::SUBMIT_AND_DOWNLOAD_ACTION, FormTypes\SubmitType::class);
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
                    $isPaReport = $this->report->hasSection('paDeputyExpenses');
                    $hasFinanceInfo = $this->report->hasSection('bankAccounts');

                    foreach ($sectionsToValidate as $section_id) {
                        $ret[] = 'submit-' . $section_id . '-checklist';
                    }

                    // DDPB-2293 question not relevant for PA
                    if (!$isPaReport) {
                        $ret[] = 'submit-deputy-fullname-accurate-casrec-checklist';
                    }

                    // bonds to show when report has financial info but not a PA one
                    if ($hasFinanceInfo && !$isPaReport) {
                        $ret[] = 'submit-bonds-checklist';
                    }


                }
                return $ret;
            },
        ])
        ->setRequired(['report'])
        ;
    }
}
