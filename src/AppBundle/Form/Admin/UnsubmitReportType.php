<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints as Constraints;

class UnsubmitReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('unsubmittedSection', 'collection', [
                'type' => new UnsubmittedSectionType(),
//                'cascade_validation' => true,
                'error_bubbling' => false,
            ])
            ->add('dueDateChoice', 'choice', [
                'choices' => [
                    0 => 'reportChangeDueDate.form.dueDateChoice.choices.keep',
                    3 => 'reportChangeDueDate.form.dueDateChoice.choices.3weeks',
                    4 => 'reportChangeDueDate.form.dueDateChoice.choices.4weeks',
                    5 => 'reportChangeDueDate.form.dueDateChoice.choices.5weeks',
                    'other' => 'reportChangeDueDate.form.dueDateChoice.choices.other',
                ],
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
                'constraints' => [new Constraints\NotBlank(['message' => 'report.dueDateChoice.notBlank', 'groups'=>['change_due_date']])]
            ])
            ->add('dueDate', 'date', ['widget'          => 'text',
                                      'input'           => 'datetime',
                                      'format'          => 'yyyy-MM-dd',
                                      'invalid_message' => 'report.endDate.invalidMessage',
                                      'data' => null
            ])
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-clients',
            'name'               => 'report',
            'validation_groups'  => function (FormInterface $form) {
                $ret = ['unsubmitted_sections', 'change_due_date'];

                // validate due date if the choice value is "other"
                $weeksFromNow = $form['dueDateChoice']->getData();// access unmapped field
                if ($weeksFromNow == 'other') {
                    $ret[] = 'report_due_date';
                }

                return $ret;
            },
        ]);
    }
}
