<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfServiceFee;
use Symfony\Component\Form\AbstractType;
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

        $builder->add('id', 'hidden');
        $builder->add('feeTypeId', 'hidden');

        if ($this->step == 1) {
            $builder->add('serviceTypeId', 'choice', [
                'choices' =>  $this->getServiceFeeTypes(),
                'expanded' => true,
            ]);
        }

        if ($this->step == 2) {
            $builder->add('serviceTypeId', 'hidden');
            $builder->add('assessedOrFixed', 'choice', [
                    'choices' => [ProfServiceFee::TYPE_FIXED_FEE => 'Fixed costs', ProfServiceFee::TYPE_ASSESSED_FEE => 'Assessed costs'],
                    'expanded' => true,
                ])
                ->add('amountCharged', 'number', [
                    'precision' => 2,
                    'empty_data' => null,
                    'grouping' => true,
                    'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                    'invalid_message' => 'profServiceFee.amountCharged.type'
                ])
                ->add('paymentReceived', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,

                ])
                ->add('amountReceived', 'number', [
                    'precision' => 2,
                    'grouping' => true,
                    'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                    'invalid_message' => 'profServiceFee.amountReceived.type'
                ])
                ->add('paymentReceivedDate', 'date', ['widget' => 'text',
                    'empty_data' => null,
                    'input' => 'datetime',
                    'format' => 'yyyy-MM-dd',
                    'invalid_message' => 'profServiceFee.paymentReceivedDate.invalidMessage',]);

            $builder->add('saveAndAddAnother', 'submit');
        }

        $builder->add('save', 'submit');
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
