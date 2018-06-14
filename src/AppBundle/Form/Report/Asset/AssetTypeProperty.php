<?php

namespace AppBundle\Form\Report\Asset;

use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetTypeProperty extends AbstractType
{
    private $step;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        if ($this->step === 1) {
            $builder
                ->add('address', FormTypes\TextType::class)
                ->add('address2', FormTypes\TextType::class)
                ->add('postcode', FormTypes\TextType::class)
                ->add('county', FormTypes\TextType::class);
        }

        if ($this->step === 2) {
            $builder
                ->add('occupants', FormTypes\TextareaType::class);
        }

        if ($this->step === 3) {
            $builder->add('owned', FormTypes\ChoiceType::class, [
                'choices' => ['fully' => 'Fully-owned', 'partly' => 'Part-owned'],
                'expanded' => true,
            ])
                ->add('ownedPercentage', FormTypes\NumberType::class, [
                    'grouping' => false,
                    'precision' => 0,
                    'max_length' => 2,
                    'pattern' => '[0-9]',
                    'invalid_message' => 'asset.property.ownedPercentage.type',
                ]);
        }
        if ($this->step === 4) {
            $builder->add('hasMortgage', FormTypes\ChoiceType::class, [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ])
                ->add('mortgageOutstandingAmount', FormTypes\NumberType::class, [
                    'grouping' => true,
                    'precision' => 2,
                    'invalid_message' => 'asset.property.mortgageOutstandingAmount.type',
                ]);
        }

        if ($this->step === 5) {
            $builder->add('value', FormTypes\NumberType::class, [
                'grouping' => true,
                'precision' => 2,
                'invalid_message' => 'asset.property.value.type',
            ]);
        }

        if ($this->step === 6) {
            $builder
                ->add('isSubjectToEquityRelease', FormTypes\ChoiceType::class, [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ]);
        }

        if ($this->step === 7) {
            $builder
                ->add('hasCharges', FormTypes\ChoiceType::class, [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ]);
        }

        if ($this->step === 8) {
            $builder
                ->add('isRentedOut', FormTypes\ChoiceType::class, [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ])
                ->add('rentAgreementEndDate', FormTypes\DateType::class, [
                    'widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'dd-MM-yyyy',
                    'invalid_message' => 'Enter a valid date',
                ])
                ->add('rentIncomeMonth', FormTypes\NumberType::class, [
                    'grouping' => true,
                    'precision' => 2,
                    'invalid_message' => 'asset.property.rentIncomeMonth.type',
                ]);

            $builder
                ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    $data = $event->getData();

                    // rentAgreementEndDate; set day=01 if month and year are set
                    if (!empty($data['rentAgreementEndDate']['month']) && !empty($data['rentAgreementEndDate']['year'])) {
                        $data['rentAgreementEndDate']['day'] = '01';
                        $event->setData($data);
                    }
                });
        }

        $builder
            ->add('title', FormTypes\HiddenType::class)
            ->add('id', FormTypes\HiddenType::class)
            ->add('save', FormTypes\SubmitType::class);
    }

    protected function getValidationGroups()
    {
        return function (FormInterface $form) {

            /** @var $asset \AppBundle\Entity\Report\AssetProperty */
            $asset = $form->getData();

            return [
                1 => ['property-address'],
                2 => ['property-occupants'],
                3 => ($asset->getOwned() == 'partly') ? ['property-owned', 'property-owned-partly'] : ['property-owned'],
                4 => ($asset->getHasMortgage() == 'yes') ? ['property-mortgage', 'property-mortgage-outstanding-amount'] : ['property-mortgage'],
                5 => ['property-value'],
                6 => ['property-subject-equity-release'],
                7 => ['property-has-charges'],
                8 => ($asset->getIsRentedOut() == 'yes')
                    ? ['property-rented-out', 'property-rent-agree-date', 'property-rent-income-month']
                    : ['property-rented-out'],

            ][$this->step];
        };
    }

    public function getBlockPrefix()
    {
        return 'asset';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-assets',
            'validation_groups' => $this->getValidationGroups(),
        ])
        ->setRequired(['step']);
    }
}
