<?php

namespace App\Form\Report\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
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

        if (1 === $this->step) {
            $builder
                ->add('address', FormTypes\TextType::class)
                ->add('address2', FormTypes\TextType::class)
                ->add('postcode', FormTypes\TextType::class)
                ->add('county', FormTypes\TextType::class);
        }

        if (2 === $this->step) {
            $builder
                ->add('occupants', FormTypes\TextareaType::class);
        }

        if (3 === $this->step) {
            $builder->add('owned', FormTypes\ChoiceType::class, [
                'choices' => array_flip(['fully' => 'Fully-owned', 'partly' => 'Part-owned']),
                'expanded' => true,
            ])
                ->add('ownedPercentage', FormTypes\NumberType::class, [
                    'grouping' => false,
                    'scale' => 0,
                    'attr' => ['maxlength' => 2],
                    'invalid_message' => 'asset.property.ownedPercentage.type',
                ]);
        }
        if (4 === $this->step) {
            $builder->add('hasMortgage', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ])
                ->add('mortgageOutstandingAmount', FormTypes\NumberType::class, [
                    'grouping' => true,
                    'scale' => 2,
                    'invalid_message' => 'asset.property.mortgageOutstandingAmount.type',
                ]);
        }

        if (5 === $this->step) {
            $builder->add('value', FormTypes\NumberType::class, [
                'grouping' => true,
                'scale' => 2,
                'invalid_message' => 'asset.property.value.type',
            ]);
        }

        if (6 === $this->step) {
            $builder
                ->add('isSubjectToEquityRelease', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no'],
                    'expanded' => true,
                ]);
        }

        if (7 === $this->step) {
            $builder
                ->add('hasCharges', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no'],
                    'expanded' => true,
                ]);
        }

        if (8 === $this->step) {
            $builder
                ->add('isRentedOut', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no'],
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
                    'scale' => 2,
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
            /** @var $asset \App\Entity\Report\AssetProperty */
            $asset = $form->getData();

            return [
                1 => ['property-address'],
                2 => ['property-occupants'],
                3 => ('partly' == $asset->getOwned()) ? ['property-owned', 'property-owned-partly'] : ['property-owned'],
                4 => ('yes' == $asset->getHasMortgage()) ? ['property-mortgage', 'property-mortgage-outstanding-amount'] : ['property-mortgage'],
                5 => ['property-value'],
                6 => ['property-subject-equity-release'],
                7 => ['property-has-charges'],
                8 => ('yes' == $asset->getIsRentedOut())
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
