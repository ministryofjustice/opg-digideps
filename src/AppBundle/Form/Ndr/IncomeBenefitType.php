<?php

namespace AppBundle\Form\Ndr;

use AppBundle\Entity\Ndr\Ndr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step            = (int) $options['step'];
        $this->translator      = $options['translator'];
        $this->clientFirstName = $options['clientFirstName'];

        if ($this->step === 1) {
            $builder
                ->add('id', 'hidden')
                ->add('stateBenefits', 'collection', [
                    'type' => new StateBenefitType(),
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
                'type' => new OneOffType(),
            ]);
        }


        $builder->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ndr-income-benefits',
            'cascade_validation' => true,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data Ndr */

                $validationGroups = [
                    1 => ['ndr-state-benefits'],
                    2 => ['receive-state-pension'],
                    3 => ($data->getReceiveOtherIncome() == 'yes')
                        ? ['receive-other-income', 'receive-other-income-details']
                        : ['receive-other-income'],
                    4 => ($data->getExpectCompensationDamages() == 'yes')
                        ? ['expect-compensation-damage', 'expect-compensation-damage-details']
                        : ['expect-compensation-damage'],
                    5=>['ndr-one-off']
                ][$this->step];

                return $validationGroups;
            },
        ])
        ->setRequired(['step', 'translator', 'clientFirstName'])
        ->setAllowedTypes('translator', TranslatorInterface::class)
        ;
    }

    public function getBlockPrefix()
    {
        return 'income_benefits';
    }
}
