<?php

declare(strict_types=1);

namespace App\Form\Report;

use App\Entity\IncomeReceivedOnClientsBehalfInterface;
use App\Entity\Report\ClientBenefitsCheck;
use App\Form\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientBenefitsCheckType extends AbstractType
{
    private int $step = 1;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        if (1 === $this->step) {
            $builder->add('whenLastCheckedEntitlement', ChoiceType::class, [
                'choices' => [
                    'form.whenLastChecked.choices.haveChecked' => ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED,
                    'form.whenLastChecked.choices.currentlyChecking' => ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING,
                    'form.whenLastChecked.choices.neverChecked' => ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED,
                ],
                'expanded' => true,
            ]);

            $builder->add('dateLastCheckedEntitlement', DateType::class, [
                'widget' => 'text',
                'input' => 'datetime',
                'invalid_message' => 'Enter a valid date',
            ]);

            $builder->add('neverCheckedExplanation', TextareaType::class);
        }

        if (2 === $this->step) {
            $builder->add('doOthersReceiveIncomeOnClientsBehalf', ChoiceType::class, [
                'choices' => [
                    'form.incomeOnClientsBehalf.choices.yes' => ClientBenefitsCheck::OTHER_INCOME_YES,
                    'form.incomeOnClientsBehalf.choices.no' => ClientBenefitsCheck::OTHER_INCOME_NO,
                    'form.incomeOnClientsBehalf.choices.dontKnow' => ClientBenefitsCheck::OTHER_INCOME_DONT_KNOW,
                ],
                'expanded' => true,
            ]);

            $builder->add('dontKnowIncomeExplanation', TextareaType::class);
        }

        if (3 === $this->step) {
            $builder->add('typesOfIncomeReceivedOnClientsBehalf', CollectionType::class, [
                'entry_type' => IncomeReceivedOnClientsBehalfType::class,
                'entry_options' => ['label' => false, 'empty_data' => null],
                'delete_empty' => function (IncomeReceivedOnClientsBehalfInterface $income) use ($options) {
                    return null === $income->getAmount() && null === $income->getIncomeType() && false === $income->getAmountDontKnow() && $options['allow_delete_empty'];
                },
                'allow_delete' => true,
            ]);

            $builder->add('addAnother', SubmitType::class);
        }

        $builder->add('save', SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (!empty($data['dateLastCheckedEntitlement']['month']) && !empty($data['dateLastCheckedEntitlement']['year'])) {
                $data['dateLastCheckedEntitlement']['day'] = '01';
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'report-client-benefits-check',
                'validation_groups' => [
                    1 => ['client-benefits-check'],
                    2 => ['client-benefits-check'],
                    3 => ['client-benefits-check'],
                ][$this->step],
            ]
        )
            ->setRequired(['step', 'allow_delete_empty']);
    }

    public function getBlockPrefix()
    {
        return 'report-client-benefits-check';
    }
}
