<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('startDate', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'report.startDate.invalidMessage',
                'constraints' => [
                    new Range([
                        'min' => (new \DateTime('now'))->modify('-7 years'),
                        'max' => (new \DateTime('now'))->modify('+3 years'),
                        'notInRangeMessage' => 'Please enter a valid start date.',
                        'groups' => 'start-end-dates',
                    ]),
                ],
            ])
            ->add('endDate', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'report.endDate.invalidMessage',
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['name' => 'report', 'validation_groups' => ['start-end-dates']]);
    }
}
