<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatPeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('period', FormTypes\ChoiceType::class, [
                'choices' => [
                    'last-30'=> 'last-30',
                    'this-year'=> 'this-year',
                    'all-time'=> 'all-time',
                    'custom'=> 'custom',
                ],
                'choice_label' => function ($choice) {
                    return 'form.period.options.' . $choice;
                },
                'expanded' => true,
                'multiple' => false,
                'data' => 'last-30'
            ])
            ->add('startDate', FormTypes\DateType::class, [
                'widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Enter a valid date',
                'data' => new \DateTime('-30 days')
            ])
            ->add('endDate', FormTypes\DateType::class, [
                'widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Enter a valid date',
                'data' => new \DateTime()
            ])
            ->add('update', FormTypes\SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if ($data['period'] === 'last-30') {
                $startDate = new \DateTime('-30 days');
                $data['startDate'] = ['day' => date('d', $startDate), 'month' => date('m', $startDate), 'year' => date('Y', $startDate)];
                $data['endDate'] = ['day' => date('d'), 'month' => date('m'), 'year' => date('Y')];
            } elseif ($data['period'] === 'this-year') {
                $data['startDate'] = ['day' => 1, 'month' => 1, 'year' => date('Y')];
                $data['endDate'] = ['day' => date('d'), 'month' => date('m'), 'year' => date('Y')];
            } elseif ($data['period'] === 'all-time') {
                $data['startDate'] = ['day' => 1, 'month' => 1, 'year' => 2000];
                $data['endDate'] = ['day' => date('d'), 'month' => date('m'), 'year' => date('Y')];
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-metrics',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin';
    }
}
