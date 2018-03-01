<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfServiceFee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

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

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $translatorDomain;

    public function __construct(array $serviceTypeIds, TranslatorInterface $translator, $translatorDomain)
    {
        $this->serviceTypeIds = $serviceTypeIds;
        $this->translator = $translator;
        $this->translatorDomain = $translatorDomain;
    }

    private function getServiceFeeTypes()
    {
        $ret = [];

        foreach ($this->serviceTypeIds as $serviceTypeId => $hasMoreInfo) {
            $ret[$serviceTypeId] = $this->translator->trans('addTypePage.form.serviceType.' . $serviceTypeId);
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
            $builder->add('assessedOrFixed', 'choice', [
                    'choices' => ['fixed' => 'Fixed costs', 'assessed' => 'Assessed costs'],
                    'expanded' => true,
                ])
                ->add('amountCharged', 'number', [
                    'precision' => 2,
                    'grouping' => true,
                    'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                    'invalid_message' => 'serviceFee.form.amountCharged.type'
                ])
                ->add('paymentReceived', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,

                ])
                ->add('amountReceived', 'number', [
                    'precision' => 2,
                    'grouping' => true,
                    'error_bubbling' => false, // keep (and show) the error (Default behaviour). if true, error is lost
                    'invalid_message' => 'serviceFee.form.amountReceived.type'
                ])
                ->add('paymentReceivedDate', 'date', ['widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'yyyy-MM-dd',
                    'invalid_message' => 'serviceFee.paymentReceived.invalidMessage',]);
        }

        $builder->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Report\ProfServiceFee',
            'validation_groups' => function (FormInterface $form) {
                /* @var $data \AppBundle\Entity\Report\ProfServiceFee */
                $data = $form->getData();
                $validationGroups = ['prof_service_fee'];

//                if ($data->getAmount() && $data->getHasMoreDetails()) {
//                    $validationGroups[] = 'fees-more-details';
//                }

                return $validationGroups;
            },
            'translation_domain' => 'report-prof-current-fees',
        ])
        ->setRequired(['step']);

    }

    public function getName()
    {
        return 'prof_service_fee_type';
    }
}
