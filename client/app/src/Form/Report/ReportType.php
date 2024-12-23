<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('startDate', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'report.startDate.invalidMessage', ])
            ->add('endDate', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'report.endDate.invalidMessage',
            ])
            ->add('save', FormTypes\SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (!preg_match('/^\d{4}$/', $data['startDate']['year']) || !preg_match('/^\d{4}$/', $data['endDate']['year'])) {
                $form = $event->getForm();
                $form->addError(new FormError('Please enter a valid four-digit year.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['name' => 'report', 'validation_groups' => ['start-end-dates']]);
    }
}
