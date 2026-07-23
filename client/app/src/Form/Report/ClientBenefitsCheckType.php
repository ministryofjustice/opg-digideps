<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Report;

use OPG\Digideps\Frontend\Entity\MoneyReceivedOnClientsBehalfInterface;
use OPG\Digideps\Frontend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Frontend\Form\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientBenefitsCheckType extends AbstractType
{
    private int $step = 1;

    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->step = (int) $options['step'];
        $baseTransParams = ['%client%' => $options['label_translation_parameters']['clientFirstname']];
        $domain = 'report-client-benefits-check';

        if ($this->step === 1) {
            $builder->add('whenLastCheckedEntitlement', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('form.whenLastChecked.choices.haveChecked', $baseTransParams, $domain) => ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED,
                    $this->translator->trans('form.whenLastChecked.choices.currentlyChecking', $baseTransParams, $domain) => ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING,
                    $this->translator->trans('form.whenLastChecked.choices.neverChecked', $baseTransParams, $domain) => ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED,
                ],
                'expanded' => true,
            ]);

            $builder->add('dateLastCheckedEntitlement', DateType::class, [
                'widget' => 'text',
                'input' => 'datetime',
                'invalid_message' => $this->translator->trans(
                    'form.whenLastChecked.errors.invalidDate',
                    [],
                    'report-client-benefits-check'
                ),
            ]);

            $builder->add('neverCheckedExplanation', TextareaType::class);
        }

        if ($this->step === 2) {
            $builder->add('doOthersReceiveMoneyOnClientsBehalf', ChoiceType::class, [
                'choices' => [
                    'form.moneyOnClientsBehalf.choices.yes' => ClientBenefitsCheck::OTHER_MONEY_YES,
                    'form.moneyOnClientsBehalf.choices.no' => ClientBenefitsCheck::OTHER_MONEY_NO,
                    'form.moneyOnClientsBehalf.choices.dontKnow' => ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW,
                ],
                'expanded' => true,
            ]);

            $builder->add('dontKnowMoneyExplanation', TextareaType::class);
        }

        if ($this->step === 3) {
            $builder->add('typesOfMoneyReceivedOnClientsBehalf', CollectionType::class, [
                'entry_type' => MoneyReceivedOnClientsBehalfType::class,
                'entry_options' => ['label' => false, 'empty_data' => null],
                'delete_empty' => function (MoneyReceivedOnClientsBehalfInterface $money) use ($options) {
                    return $money->getAmount() === null && $money->getMoneyType() === null && $money->getAmountDontKnow() === false && $options['allow_delete_empty'];
                },
                'allow_delete' => true,
            ]);
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'report-client-benefits-check',
                'label_translation_parameters' => [
                    '%client%' => 'ACME Inc.',
                ],
                'validation_groups' => [
                    1 => ['client-benefits-check'],
                    2 => ['client-benefits-check'],
                    3 => ['client-benefits-check'],
                ][$this->step],
            ]
        )
            ->setRequired(['step', 'allow_delete_empty']);
    }

    public function getBlockPrefix(): string
    {
        return 'report-client-benefits-check';
    }
}
