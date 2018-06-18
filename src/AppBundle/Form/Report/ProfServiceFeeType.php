<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfServiceFee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfServiceFeeType extends AbstractType
{
    /**
     * @var int
     */
    private $step;

    /**
     * @var array
     */
    protected $serviceTypeIds;

    public function __construct(array $serviceTypeIds)
    {
        $this->serviceTypeIds = $serviceTypeIds;
    }

    private function getServiceFeeTypes()
    {
        $ret = [];

        foreach ($this->serviceTypeIds as $serviceTypeId => $hasMoreInfo) {
            $ret[$serviceTypeId] = 'addTypePage.form.serviceType.' . $serviceTypeId;
        }
        return array_unique($ret);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        $builder->add('id', FormTypes\HiddenType::class);
        $builder->add('feeTypeId', FormTypes\HiddenType::class);

        if ($this->step == 1) {
            $builder->add('serviceTypeId', FormTypes\ChoiceType::class, [
                'choices' =>  array_flip($this->getServiceFeeTypes()),
                'expanded' => true,
            ]);
        }

        if ($this->step == 2) {
            $builder->add('serviceTypeId', FormTypes\HiddenType::class);
            $builder->add('assessedOrFixed', FormTypes\ChoiceType::class, [
                    'choices' => array_flip([ProfServiceFee::TYPE_FIXED_FEE => 'Fixed costs', ProfServiceFee::TYPE_ASSESSED_FEE => 'Assessed costs']),
                    'expanded' => true,
                ])
                ->add('amountCharged', FormTypes\NumberType::class, [
                    'scale' => 2,
                    'placeholder' => null,
                    'grouping' => true,
                    'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                    'invalid_message' => 'profServiceFee.amountCharged.type'
                ])
                ->add('paymentReceived', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no'],
                    'expanded' => true,

                ])
                ->add('amountReceived', FormTypes\NumberType::class, [
                    'scale' => 2,
                    'grouping' => true,
                    'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                    'invalid_message' => 'profServiceFee.amountReceived.type'
                ])
                ->add('paymentReceivedDate', FormTypes\DateType::class, ['widget' => 'text',
                    'placeholder' => null,
                    'input' => 'datetime',
                    'format' => 'yyyy-MM-dd',
                    'invalid_message' => 'profServiceFee.paymentReceivedDate.invalidMessage',]);

            $builder->add('saveAndAddAnother', FormTypes\SubmitType::class);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'amountCharged' => null,
            'data_class' => ProfServiceFee::class,
            'validation_groups' => $this->getValidationGroups(),
            'translation_domain' => 'report-prof-current-fees'
        ])
        ->setRequired(['step']);
    }

    protected function getValidationGroups()
    {
        return function (FormInterface $form) {
            /** @var $asset \AppBundle\Entity\Report\ProfServiceFee */
            $profServiceFee = $form->getData();
            switch ($this->step) {
                case '1':
                    $validationGroups = ['prof-service-fee-type'];
                    break;
                case '2':
                    $validationGroups = ['prof-service-fee-details-type'];
                    if ($profServiceFee->getPaymentReceived() == 'yes') {
                        $validationGroups = ['prof-service-fee-details-type', 'prof-service-fee-details-type-payment-received'];
                    }
                    break;
                default:
                    throw new \Exception('Invalid step: validation groups not found');
            }

            return $validationGroups;
        };
    }

    public function getBlockPrefix()
    {
        return 'prof_service_fee_type';
    }
}
