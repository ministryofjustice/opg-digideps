<?php

declare(strict_types=1);

namespace App\Form\Report;

use App\Form\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientBenefitsCheckType extends AbstractType
{
    private int $step = 1;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        if (1 === $this->step) {
            $builder->add('whenLastCheckedEntitlement', ChoiceType::class, [
                'choices' => [
                    'form.whenLastChecked.choices.haveChecked' => 'haveChecked',
                    'form.whenLastChecked.choices.currentlyChecking' => 'currentlyChecking',
                    'form.whenLastChecked.choices.neverChecked' => 'neverChecked',
                ],
                'expanded' => true,
            ]);
            $builder->add('dateLastCheckedEntitlement', DateType::class, [
                'widget' => 'text',
                'input' => 'datetime',
                'invalid_message' => 'Enter a valid date',
            ]);
            $builder->add('neverCheckedExplanation', TextareaType::class);
        }

        $builder->add('save', SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (!empty($data['dateLastCheckedEntitlement']['month']) && !empty($data['dateLastCheckedEntitlement']['year'])) {
                $data['dateLastCheckedEntitlement']['day'] = '01';
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'report-client-benefits-check',
                'validation_groups' => [
                    1 => ['client-benefits-check'],
                    2 => [],
                ][$this->step],
            ]
        )
            ->setRequired(['step']);
    }

    public function getBlockPrefix()
    {
        return 'report-client-benefits-check';
    }
}
