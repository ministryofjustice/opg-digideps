<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MentalAssessment extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mentalAssessmentDate', FormTypes\DateType::class, ['widget' => 'text',
            'input' => 'datetime',
            'format' => 'dd-MM-yyyy',
            'invalid_message' => 'Enter a valid date',
        ])
            ->add('save', FormTypes\SubmitType::class)
        ;

        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (!empty($data['mentalAssessmentDate']['month']) && !empty($data['mentalAssessmentDate']['year'])) {
                    $data['mentalAssessmentDate']['day'] = '01';
                    $event->setData($data);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-decisions',
            'validation_groups' => ['mental-assessment-date']
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mental_assessment';
    }
}
