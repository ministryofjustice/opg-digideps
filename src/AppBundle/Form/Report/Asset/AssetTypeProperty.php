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

            /** @var $data \AppBundle\Entity\Report\AssetProperty */
            $data = $form->getData();

            $validationGroups = [];

            if ($this->step == 1) {
                $validationGroups = ['property-address'];
            }

            if ($this->step == 2) {
                $validationGroups = ['property-occupants'];
            }

            if ($this->step == 3) {
                $validationGroups = ['property-owned'];
                if ($data->getOwned() == 'partly') {
                    $validationGroups[] = 'property-owned-partly';
                }
            }

            if ($this->step == 4) {
                $validationGroups = ['property-mortgage'];
                if ($data->getHasMortgage() == 'yes') {
                    $validationGroups[] = 'property-mortgage-outstanding-amount';
                }
            }

            if ($this->step == 5) {
                $validationGroups[] = 'property-value';
            }

            if ($this->step == 6) {
                $validationGroups[] = 'property-subject-equity-release';
            }

            if ($this->step == 7) {
                $validationGroups[] = 'property-has-charges';
            }

            if ($this->step == 8) {
                $validationGroups[] = 'property-rented-out';
                if ($data->getIsRentedOut() == 'yes') {
                    $validationGroups[] = 'property-rent-agree-date';
                    $validationGroups[] = 'property-rent-income-month';
                }
            }

            return $validationGroups;
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
