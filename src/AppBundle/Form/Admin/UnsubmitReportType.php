<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Constraints;

class UnsubmitReportType extends AbstractType
{
    const DUE_DATE_OPTION_CUSTOM = 'custom';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dueDateChoiceTransPrefix = 'reportManage.form.dueDateChoice.choices.';
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('unsubmittedSection', FormTypes\CollectionType::class, [
                'type' => new UnsubmittedSectionType(),
            ])
            ->add('startDate', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'report.startDate.invalidMessage', ])

            ->add('endDate', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'report.endDate.invalidMessage',
            ])

            ->add('dueDateChoice', FormTypes\ChoiceType::class, [
                'choices'     => [
                    'keep'  => $dueDateChoiceTransPrefix . 'keep',
                    3       => $dueDateChoiceTransPrefix . '3weeks',
                    4       => $dueDateChoiceTransPrefix . '4weeks',
                    5       => $dueDateChoiceTransPrefix . '5weeks',
                    self::DUE_DATE_OPTION_CUSTOM => $dueDateChoiceTransPrefix . 'custom',
                ],
                'expanded'    => true,
                'multiple'    => false,
                'mapped'      => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.dueDateChoice.notBlank', 'groups' => ['change_due_date']])
                ],
            ])
            ->add('dueDateCustom', FormTypes\DateType::class, [
                'widget'      => 'text',
                'input'       => 'datetime',
                'format'      => 'yyyy-MM-dd',
                 'invalid_message' => 'report.dueDate.invalidMessage',
                'mapped'      => false,
                'required'    => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'report.dueDate.notBlank', 'groups' => ['due_date_new']]),
                    new Constraints\Date(['message' => 'report.dueDate.invalidMessage', 'groups' => ['due_date_new']]),
                ],
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-clients',
            'name'               => 'report',
            'validation_groups'  => function (FormInterface $form) {
                $ret = ['unsubmitted_sections', 'change_due_date'];

                if ($form['dueDateChoice']->getData() == self::DUE_DATE_OPTION_CUSTOM) {
                    $ret[] = 'due_date_new';
                }

                return $ret;
            },
        ]);
    }
}
