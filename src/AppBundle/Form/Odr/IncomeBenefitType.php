<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Odr\Odr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class IncomeBenefitType extends AbstractType
{
    /**
     * @var int
     */
    private $step;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $clientFirstName;

    /**
     * @param $step
     */
    public function __construct($step, TranslatorInterface $translator, $clientFirstName)
    {
        $this->step = (int)$step;
        $this->translator = $translator;
        $this->clientFirstName = $clientFirstName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->step === 1) {
            $builder
                ->add('id', 'hidden')
                ->add('stateBenefits', 'collection', [
                    'type' => new IncomeBenefitSingleType(),
                ]);
        }

        if ($this->step === 2) {
            $builder->add('receiveStatePension', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder
                ->add('receiveOtherIncome', 'choice', [
                    'choices' => ['yes' => 'Yes', 'no' => 'No'],
                    'expanded' => true,
                ])
                ->add('receiveOtherIncomeDetails', 'textarea');
        }

        if ($this->step === 4) {
            $builder->add('expectCompensationDamages', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ])
                ->add('expectCompensationDamagesDetails', 'textarea');
        }

        if ($this->step === 5) {
            $builder->add('oneOff', 'collection', [
                'type' => new IncomeBenefitSingleType(),
            ]);
        }


        $builder->add('save', 'submit');


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            //

            $event->setData($data);
        });
    }

    private function translate($key)
    {
        return $this->translator->trans($key, ['%client%' => $this->clientFirstName], 'report-visits-care');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-income-benefits',
            'cascade_validation' => true,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data Odr */

                $validationGroups = [
                    1 => ['odr-state-benefits'],
                    2 => ['receive-state-pension'],
                    3 => ($data->getReceiveOtherIncome() == 'yes')
                        ? ['receive-other-income', 'receive-other-income-details']
                        : ['receive-other-income'],
                    4 => ($data->getExpectCompensationDamages() == 'yes')
                        ? ['expect-compensation-damage', 'expect-compensation-damage-details']
                        : ['expect-compensation-damage'],
                    5=>['odr-one-off']
                ][$this->step];

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'income_benefits';
    }
}
