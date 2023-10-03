<?php

namespace App\Form\Ndr;

use App\Entity\Ndr\Ndr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        $this->step = (int) $options['step'];
        $this->translator = $options['translator'];
        $this->clientFirstName = $options['clientFirstName'];

        if (1 === $this->step) {
            $builder
                ->add('id', FormTypes\HiddenType::class)
                ->add('stateBenefits', FormTypes\CollectionType::class, [
                    'entry_type' => StateBenefitType::class,
                    'entry_options' => ['constraints' => new Valid()],
                    'constraints' => new Valid(),
                ]);
        }

        if (2 === $this->step) {
            $builder->add('receiveStatePension', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ]);
        }

        if (3 === $this->step) {
            $builder
                ->add('receiveOtherIncome', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no'],
                    'expanded' => true,
                ])
                ->add('receiveOtherIncomeDetails', FormTypes\TextareaType::class);
        }

        if (4 === $this->step) {
            $builder->add('expectCompensationDamages', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ])
                ->add('expectCompensationDamagesDetails', FormTypes\TextareaType::class);
        }

        if (5 === $this->step) {
            $builder->add('oneOff', FormTypes\CollectionType::class, [
                'entry_type' => OneOffType::class,
                'entry_options' => ['constraints' => new Valid()],
                'constraints' => new Valid(),
            ]);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ndr-income-benefits',
            'constraints' => new Valid(),
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data Ndr */

                $validationGroups = [
                    1 => ['ndr-state-benefits'],
                    2 => ['receive-state-pension'],
                    3 => ('yes' == $data->getReceiveOtherIncome())
                        ? ['receive-other-income', 'receive-other-income-details']
                        : ['receive-other-income'],
                    4 => ('yes' == $data->getExpectCompensationDamages())
                        ? ['expect-compensation-damage', 'expect-compensation-damage-details']
                        : ['expect-compensation-damage'],
                    5 => ['ndr-one-off'],
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
