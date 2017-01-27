<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MentalAssessment extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mentalAssessmentDate', 'date', ['widget' => 'text',
            'input' => 'datetime',
            'format' => 'dd-MM-yyyy',
            'invalid_message' => 'Enter a valid date',
        ])
            ->add('save', 'submit')
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-decisions',
            'validation_groups' => ['mental-assessment-date']
        ]);
    }

    public function getName()
    {
        return 'mental_assessment';
    }
}
