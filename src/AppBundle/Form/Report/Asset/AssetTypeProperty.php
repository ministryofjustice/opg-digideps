<?php

namespace AppBundle\Form\Report\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class AssetTypeProperty extends AbstractType
{
    private $step;

    /**
     * @param $step
     */
    public function __construct($step)
    {
        $this->step = (int)$step;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->step === 1) {
            $builder
                ->add('address', 'text')
                ->add('address2', 'text')
                ->add('postcode', 'text')
                ->add('county', 'text');
        }

        if ($this->step === 2) {
            $builder
                ->add('occupants', 'textarea');
        }

        if ($this->step === 3) {
            $builder->add('owned', 'choice', array(
                'choices' => ['fully' => 'Fully owned', 'partly' => 'Part-owned'],
                'expanded' => true,
            ))
                ->add('ownedPercentage', 'number', [
                    'grouping' => false,
                    'precision' => 0,
                    'max_length' => 2,
                    'pattern' => '[0-9]',
                    'invalid_message' => 'asset.property.ownedPercentage.type',
                ]);
        }
        if ($this->step === 4) {
            $builder->add('hasMortgage', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ])
                ->add('mortgageOutstandingAmount', 'number', [
                    'grouping' => true,
                    'precision' => 2,
                    'invalid_message' => 'asset.property.mortgageOutstandingAmount.type',
                ]);
        }

        if ($this->step === 5) {
            $builder->add('value', 'number', [
                'grouping' => true,
                'precision' => 2,
                'invalid_message' => 'asset.property.value.type',
            ]);
        }

        if ($this->step === 6) {
            $builder
                ->add('isSubjectToEquityRelease', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ]);
        }

        if ($this->step === 7) {
            $builder
                ->add('hasCharges', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ]);
        }

        if ($this->step === 8) {
            $builder
                ->add('isRentedOut', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ])
                ->add('rentAgreementEndDate', 'date', [
                    'widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'dd-MM-yyyy',
                    'invalid_message' => 'Enter a valid date',
                ])
                ->add('rentIncomeMonth', 'number', [
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
            ->add('title', 'hidden')
            ->add('id', 'hidden')
            ->add('save', 'submit');
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


    public function getName()
    {
        return 'asset';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-assets',
            'validation_groups' => $this->getValidationGroups(),
        ]);
    }

}
